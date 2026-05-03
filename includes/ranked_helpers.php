<?php
declare(strict_types=1);

if (defined('LOGIA_RANKED_HELPERS')) {
  return;
}
define('LOGIA_RANKED_HELPERS', true);

function ranked_tier_for_trophy(int $trophy): string {
  if ($trophy >= 2000) return 'Diamond';
  if ($trophy >= 1500) return 'Platinum';
  if ($trophy >= 1200) return 'Gold';
  if ($trophy >= 1000) return 'Silver';
  return 'Bronze';
}

function ranked_game_setting(string $key, $default) {
  global $mysqli;

  if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    return $default;
  }

  $stmt = $mysqli->prepare("
    SELECT setting_value
    FROM admin_game_settings
    WHERE setting_key = ?
    LIMIT 1
  ");

  if (!$stmt) {
    return $default;
  }

  $stmt->bind_param('s', $key);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    return $default;
  }

  $raw = (string)$row['setting_value'];
  $decoded = json_decode($raw, true);

  return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
}

function ranked_entry_fee_for_tier(string $tier): int {
  $fees = ranked_game_setting('ranked_entry_fees', [
    'Bronze' => 200,
    'Silver' => 500,
    'Gold' => 1000,
    'Platinum' => 2500,
    'Diamond' => 5000,
  ]);

  return (int)($fees[$tier] ?? $fees['Bronze'] ?? 200);
}

function ranked_exp_multiplier(string $tier, int $winStreak): float {
  $baseMultipliers = ranked_game_setting('ranked_exp_base_multipliers', [
    'Bronze' => 1.00,
    'Silver' => 1.06,
    'Gold' => 1.12,
    'Platinum' => 1.18,
    'Diamond' => 1.25,
  ]);

  $tierMultiplier = (float)($baseMultipliers[$tier] ?? $baseMultipliers['Bronze'] ?? 1.00);
  $bonusPerWin = (float)ranked_game_setting('ranked_streak_bonus', '0.03');
  $bonusCap = (float)ranked_game_setting('ranked_streak_bonus_cap', '0.25');

  $streakBonus = min($bonusCap, max(0, $winStreak) * $bonusPerWin);

  return round($tierMultiplier + $streakBonus, 2);
}

function ranked_league_key_to_tier(string $leagueKey): string {
  $leagueKey = strtolower(trim($leagueKey));

  return match ($leagueKey) {
    'silver' => 'Silver',
    'gold' => 'Gold',
    default => 'Bronze',
  };
}

function ranked_tier_to_league_key(string $tier): string {
  $tier = strtolower(trim($tier));

  return match ($tier) {
    'silver' => 'silver',
    'gold' => 'gold',
    default => 'bronze',
  };
}

function ranked_demo_league_keys(): array {
  return ['bronze', 'silver', 'gold'];
}

function ranked_normalize_league_key(string $leagueKey): string {
  $leagueKey = strtolower(trim($leagueKey));

  if (!in_array($leagueKey, ranked_demo_league_keys(), true)) {
    return 'bronze';
  }

  return $leagueKey;
}

function ranked_league_requirements(): array {
  $requirements = ranked_game_setting('ranked_league_requirements', [
    'Bronze' => ['wins' => 0],
    'Silver' => ['wins' => 3],
    'Gold' => ['wins' => 7],
  ]);

  return [
    'Bronze' => [
      'wins' => max(0, (int)($requirements['Bronze']['wins'] ?? 0)),
    ],
    'Silver' => [
      'wins' => max(0, (int)($requirements['Silver']['wins'] ?? 3)),
    ],
    'Gold' => [
      'wins' => max(0, (int)($requirements['Gold']['wins'] ?? 7)),
    ],
  ];
}

function ranked_entry_fee_for_league(string $leagueKey): int {
  $tier = ranked_league_key_to_tier($leagueKey);
  return ranked_entry_fee_for_tier($tier);
}

function ranked_wallet_credits(mysqli $mysqli, int $userId): int {
  $stmt = $mysqli->prepare("
    SELECT credits
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return (int)($row['credits'] ?? 0);
}

function ranked_leagues_for_user(mysqli $mysqli, int $userId): array {
  $profile = ranked_ensure_profile($mysqli, $userId);
  $wins = (int)($profile['wins'] ?? 0);
  $winStreak = (int)($profile['win_streak'] ?? 0);
  $credits = ranked_wallet_credits($mysqli, $userId);
  $requirements = ranked_league_requirements();

  $rows = [];

  foreach (ranked_demo_league_keys() as $leagueKey) {
    $tier = ranked_league_key_to_tier($leagueKey);
    $requiredWins = (int)($requirements[$tier]['wins'] ?? 0);
    $entryFee = ranked_entry_fee_for_league($leagueKey);
    $missingWins = max(0, $requiredWins - $wins);
    $missingZeny = max(0, $entryFee - $credits);
    $eligible = ($missingWins <= 0 && $missingZeny <= 0);

    $reasons = [];

    if ($missingWins > 0) {
      $reasons[] = 'Need ' . $missingWins . ' more ranked win' . ($missingWins === 1 ? '' : 's') . '.';
    }

    if ($missingZeny > 0) {
      $reasons[] = 'Need ' . $missingZeny . ' more Zeny.';
    }

    if (!$reasons) {
      $reasons[] = 'Ready to enter.';
    }

    $rows[] = [
      'key' => $leagueKey,
      'tier' => $tier,
      'entry_fee' => $entryFee,
      'required_wins' => $requiredWins,
      'wins' => $wins,
      'credits' => $credits,
      'missing_wins' => $missingWins,
      'missing_zeny' => $missingZeny,
      'eligible' => $eligible,
      'exp_multiplier' => ranked_exp_multiplier($tier, $winStreak),
      'reasons' => $reasons,
    ];
  }

  return $rows;
}

function ranked_queue_has_league_column(mysqli $mysqli): bool {
  static $hasColumn = null;

  if ($hasColumn !== null) {
    return $hasColumn;
  }

  $res = $mysqli->query("SHOW COLUMNS FROM ranked_queue LIKE 'league_key'");
  $hasColumn = ($res && $res->num_rows > 0);

  if ($res) {
    $res->close();
  }

  return $hasColumn;
}

function ranked_ensure_profile(mysqli $mysqli, int $userId): array {
  $stmt = $mysqli->prepare("
    SELECT *
    FROM ranked_profiles
    WHERE user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $profile = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($profile) {
    return $profile;
  }

  $trophy = 1000;
  $tier = ranked_tier_for_trophy($trophy);

  $stmt = $mysqli->prepare("
    INSERT INTO ranked_profiles (
      user_id, trophy, rank_tier, wins, losses, zeny, win_streak, best_win_streak
    )
    VALUES (?, ?, ?, 0, 0, 0, 0, 0)
  ");
  $stmt->bind_param('iis', $userId, $trophy, $tier);
  $stmt->execute();
  $stmt->close();

  return ranked_ensure_profile($mysqli, $userId);
}

function ranked_get_queue_entry(mysqli $mysqli, int $userId): ?array {
  $stmt = $mysqli->prepare("
    SELECT *
    FROM ranked_queue
    WHERE user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc() ?: null;
  $stmt->close();

  return $row;
}

function ranked_remove_from_queue(mysqli $mysqli, int $userId): void {
  $stmt = $mysqli->prepare("
    DELETE FROM ranked_queue
    WHERE user_id = ?
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $stmt->close();
}

function ranked_try_create_match(mysqli $mysqli, ?string $leagueKey = null): ?array {
  if ($leagueKey === null) {
    foreach (ranked_demo_league_keys() as $key) {
      $match = ranked_try_create_match($mysqli, $key);
      if ($match) {
        return $match;
      }
    }

    return null;
  }

  $leagueKey = ranked_normalize_league_key($leagueKey);
  $tier = ranked_league_key_to_tier($leagueKey);
  $hasLeagueColumn = ranked_queue_has_league_column($mysqli);

  if ($hasLeagueColumn) {
    $stmt = $mysqli->prepare("
      SELECT rq.*, u.display_name, u.username
      FROM ranked_queue rq
      JOIN users u ON u.id = rq.user_id
      WHERE COALESCE(rq.league_key, 'bronze') = ?
      ORDER BY rq.joined_at ASC
      LIMIT 4
    ");
    $stmt->bind_param('s', $leagueKey);
  } else {
    $stmt = $mysqli->prepare("
      SELECT rq.*, u.display_name, u.username
      FROM ranked_queue rq
      JOIN users u ON u.id = rq.user_id
      ORDER BY rq.joined_at ASC
      LIMIT 4
    ");
  }

  $stmt->execute();
  $queued = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (count($queued) < 4) {
    return null;
  }

  $roomCode = game_generate_unique_room_code($mysqli, 8);
  $roomName = $tier . ' Ranked Match';
  $roomType = 'ranked';
  $visibility = 'private';
  $status = 'waiting';
  $maxPlayers = 4;
  $passwordHash = null;
  $nullUserId = null;

  $rules = game_normalize_room_rules([
    'preset_key' => 'ranked',
    'allow_ai_fill' => false,
    'starting_hand_size' => 5,
    'allow_stack_plus2' => true,
    'allow_stack_plus4' => true,
    'draw_until_playable' => false,
    'must_pass_on_draw_penalty' => false,
    'ranked_locked' => true,
    'ranked_league' => $leagueKey,
    'ranked_tier' => $tier,
  ], 'ranked');

  $rulesJson = game_jencode($rules);

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      INSERT INTO game_rooms (
        room_code, room_name, room_type, visibility, password_hash,
        status, max_players, rules_json, created_by_user_id, host_user_id
      )
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
      'ssssssisii',
      $roomCode,
      $roomName,
      $roomType,
      $visibility,
      $passwordHash,
      $status,
      $maxPlayers,
      $rulesJson,
      $nullUserId,
      $nullUserId
    );
    $stmt->execute();
    $roomId = (int)$stmt->insert_id;
    $stmt->close();

    $seatNo = 1;

    foreach ($queued as $entry) {
      $userId = (int)$entry['user_id'];
      $playerName = trim((string)($entry['display_name'] ?? ''));

      if ($playerName === '') {
        $playerName = trim((string)($entry['username'] ?? 'Player'));
      }

      $isHost = 0;

      $stmt = $mysqli->prepare("
        INSERT INTO game_room_players (
          room_id, user_id, seat_no, player_name, player_type, is_host
        )
        VALUES (?, ?, ?, ?, 'human', ?)
      ");
      $stmt->bind_param('iiisi', $roomId, $userId, $seatNo, $playerName, $isHost);
      $stmt->execute();
      $stmt->close();

      $seatNo++;
    }

    $u1 = (int)$queued[0]['user_id'];
    $u2 = (int)$queued[1]['user_id'];
    $u3 = (int)$queued[2]['user_id'];
    $u4 = (int)$queued[3]['user_id'];

    $stmt = $mysqli->prepare("
      DELETE FROM ranked_queue
      WHERE user_id IN (?, ?, ?, ?)
    ");
    $stmt->bind_param('iiii', $u1, $u2, $u3, $u4);
    $stmt->execute();
    $stmt->close();

    $totalPot = array_sum(array_map(fn($row) => (int)$row['bet_zeny'], $queued));

    $stmt = $mysqli->prepare("
      INSERT INTO ranked_matches (
        room_id, created_at
      )
      VALUES (?, NOW())
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $stmt->close();

    game_add_log($mysqli, $roomId, $tier . ' ranked match created. Total pot: ' . $totalPot . ' Zeny.');

    $room = game_get_room_by_id($mysqli, $roomId);
    if (!$room) {
      throw new RuntimeException('Ranked room was not created.');
    }

    game_start_room($mysqli, $room);

    $mysqli->commit();

    return [
      'room_id' => $roomId,
      'room_code' => $roomCode,
      'total_pot' => $totalPot,
      'league' => $leagueKey,
      'tier' => $tier,
    ];
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }
}

function ranked_enter_queue(mysqli $mysqli, array $user, string $leagueKey = 'bronze'): array {
  $userId = (int)$user['id'];

  if ((int)($user['is_guest'] ?? 0) === 1) {
    throw new RuntimeException('Guests cannot join ranked mode.');
  }

  $leagueKey = ranked_normalize_league_key($leagueKey);
  $tier = ranked_league_key_to_tier($leagueKey);

  $profile = ranked_ensure_profile($mysqli, $userId);
  $trophy = (int)($profile['trophy'] ?? 1000);
  $wins = (int)($profile['wins'] ?? 0);

  $requirements = ranked_league_requirements();
  $requiredWins = (int)($requirements[$tier]['wins'] ?? 0);
  $entryFee = ranked_entry_fee_for_league($leagueKey);

  if ($wins < $requiredWins) {
    $missingWins = $requiredWins - $wins;
    throw new RuntimeException($tier . ' League requires ' . $requiredWins . ' ranked win(s). You need ' . $missingWins . ' more.');
  }

  $existingEntry = ranked_get_queue_entry($mysqli, $userId);
  if ($existingEntry) {
    $existingLeague = ranked_normalize_league_key((string)($existingEntry['league_key'] ?? $leagueKey));
    ranked_try_create_match($mysqli, $existingLeague);
    return ranked_status($mysqli, $userId);
  }

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      SELECT credits
      FROM users
      WHERE id = ?
      LIMIT 1
      FOR UPDATE
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $credits = (int)($wallet['credits'] ?? 0);

    if ($credits < $entryFee) {
      throw new RuntimeException('Not enough Zeny. ' . $tier . ' entry fee is ' . $entryFee . ' Zeny.');
    }

    $stmt = $mysqli->prepare("
      UPDATE users
      SET credits = credits - ?
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $entryFee, $userId);
    $stmt->execute();
    $stmt->close();

    if (ranked_queue_has_league_column($mysqli)) {
      $stmt = $mysqli->prepare("
        INSERT INTO ranked_queue (
          user_id, trophy, bet_zeny, league_key, joined_at
        )
        VALUES (?, ?, ?, ?, NOW())
      ");
      $stmt->bind_param('iiis', $userId, $trophy, $entryFee, $leagueKey);
    } else {
      $stmt = $mysqli->prepare("
        INSERT INTO ranked_queue (
          user_id, trophy, bet_zeny, joined_at
        )
        VALUES (?, ?, ?, NOW())
      ");
      $stmt->bind_param('iii', $userId, $trophy, $entryFee);
    }

    $stmt->execute();
    $stmt->close();

    game_audit_log(
      $mysqli,
      $userId,
      'RANKED_QUEUE_JOIN',
      'ranked_queue',
      null,
      [
        'trophy' => $trophy,
        'tier' => $tier,
        'league' => $leagueKey,
        'entry_fee' => $entryFee,
        'required_wins' => $requiredWins,
      ]
    );

    $mysqli->commit();
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }

  ranked_try_create_match($mysqli, $leagueKey);

  return ranked_status($mysqli, $userId);
}

function ranked_status(mysqli $mysqli, int $userId): array {
  $profile = ranked_ensure_profile($mysqli, $userId);
  $entry = ranked_get_queue_entry($mysqli, $userId);

  $activeRoom = null;

  $stmt = $mysqli->prepare("
    SELECT gr.room_code, gr.status
    FROM game_room_players grp
    JOIN game_rooms gr ON gr.id = grp.room_id
    WHERE grp.user_id = ?
      AND gr.room_type = 'ranked'
      AND gr.status IN ('waiting', 'playing')
    ORDER BY gr.id DESC
    LIMIT 1
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $activeRoom = $stmt->get_result()->fetch_assoc() ?: null;
  $stmt->close();

  $queueCount = 0;
  $queuePosition = null;
  $selectedLeague = null;
  $selectedTier = null;

  if ($entry) {
    $selectedLeague = ranked_normalize_league_key((string)($entry['league_key'] ?? 'bronze'));
    $selectedTier = ranked_league_key_to_tier($selectedLeague);

    if (ranked_queue_has_league_column($mysqli)) {
      $stmt = $mysqli->prepare("
        SELECT COUNT(*) AS c
        FROM ranked_queue
        WHERE COALESCE(league_key, 'bronze') = ?
      ");
      $stmt->bind_param('s', $selectedLeague);
      $stmt->execute();
      $queueCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
      $stmt->close();

      $stmt = $mysqli->prepare("
        SELECT COUNT(*) AS c
        FROM ranked_queue
        WHERE COALESCE(league_key, 'bronze') = ?
          AND joined_at <= ?
      ");
      $joinedAt = (string)$entry['joined_at'];
      $stmt->bind_param('ss', $selectedLeague, $joinedAt);
      $stmt->execute();
      $queuePosition = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 1);
      $stmt->close();
    } else {
      $stmt = $mysqli->prepare("
        SELECT COUNT(*) AS c
        FROM ranked_queue
      ");
      $stmt->execute();
      $queueCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
      $stmt->close();

      $stmt = $mysqli->prepare("
        SELECT COUNT(*) AS c
        FROM ranked_queue
        WHERE joined_at <= ?
      ");
      $joinedAt = (string)$entry['joined_at'];
      $stmt->bind_param('s', $joinedAt);
      $stmt->execute();
      $queuePosition = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 1);
      $stmt->close();
    }
  }

  $trophy = (int)($profile['trophy'] ?? 1000);
  $tier = ranked_tier_for_trophy($trophy);
  $winStreak = (int)($profile['win_streak'] ?? 0);

  return [
    'profile' => [
      'trophy' => $trophy,
      'tier' => $tier,
      'wins' => (int)($profile['wins'] ?? 0),
      'losses' => (int)($profile['losses'] ?? 0),
      'win_streak' => $winStreak,
      'best_win_streak' => (int)($profile['best_win_streak'] ?? 0),
      'entry_fee' => ranked_entry_fee_for_tier($tier),
      'exp_multiplier' => ranked_exp_multiplier($tier, $winStreak),
      'credits' => ranked_wallet_credits($mysqli, $userId),
    ],
    'leagues' => ranked_leagues_for_user($mysqli, $userId),
    'queue' => [
      'in_queue' => !!$entry,
      'queue_count' => $queueCount,
      'queue_position' => $queuePosition,
      'joined_at' => $entry['joined_at'] ?? null,
      'league' => $selectedLeague,
      'tier' => $selectedTier,
    ],
    'match' => [
      'found' => !!$activeRoom,
      'room_code' => $activeRoom['room_code'] ?? null,
      'status' => $activeRoom['status'] ?? null,
    ],
  ];
}

function ranked_cancel_queue(mysqli $mysqli, int $userId): array {
  $entry = ranked_get_queue_entry($mysqli, $userId);

  if (!$entry) {
    return ranked_status($mysqli, $userId);
  }

  $refund = (int)($entry['bet_zeny'] ?? 0);

  $mysqli->begin_transaction();

  try {
    ranked_remove_from_queue($mysqli, $userId);

    if ($refund > 0) {
      $stmt = $mysqli->prepare("
        UPDATE users
        SET credits = credits + ?
        WHERE id = ?
        LIMIT 1
      ");
      $stmt->bind_param('ii', $refund, $userId);
      $stmt->execute();
      $stmt->close();
    }

    game_audit_log(
      $mysqli,
      $userId,
      'RANKED_QUEUE_CANCEL',
      'ranked_queue',
      null,
      ['refund' => $refund]
    );

    $mysqli->commit();
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }

  return ranked_status($mysqli, $userId);
}

function ranked_apply_room_results(mysqli $mysqli, array $room, array &$standings): void {
  if ((string)($room['room_type'] ?? '') !== 'ranked') {
    return;
  }

  $roomId = (int)$room['id'];

  $stmt = $mysqli->prepare("
    SELECT COALESCE(SUM(bet_zeny), 0) AS pot
    FROM ranked_queue
    WHERE 1 = 0
  ");
  $stmt->execute();
  $stmt->close();

  $players = game_get_room_players($mysqli, $roomId);
  $humanCount = count(array_filter($players, fn($p) => ($p['player_type'] ?? '') === 'human'));

  if ($humanCount < 1) {
    return;
  }

  $baseEntrySum = 0;
  foreach ($players as $player) {
    if (($player['player_type'] ?? '') !== 'human' || empty($player['user_id'])) {
      continue;
    }

    $profile = ranked_ensure_profile($mysqli, (int)$player['user_id']);
    $tier = ranked_tier_for_trophy((int)($profile['trophy'] ?? 1000));
    $baseEntrySum += ranked_entry_fee_for_tier($tier);
  }

  $rewardRates = [
    1 => 0.70,
    2 => 0.20,
    3 => 0.10,
    4 => 0.00,
  ];

  $trophyDelta = [
    1 => 30,
    2 => 10,
    3 => -10,
    4 => -25,
  ];

  foreach ($standings as &$entry) {
    if (($entry['player_type'] ?? '') !== 'human' || empty($entry['user_id'])) {
      continue;
    }

    $userId = (int)$entry['user_id'];
    $place = (int)$entry['place'];
    $didWin = ($place === 1);

    $oldProfile = ranked_ensure_profile($mysqli, $userId);
    $oldTrophy = (int)($oldProfile['trophy'] ?? 1000);
    $oldStreak = (int)($oldProfile['win_streak'] ?? 0);

    $newTrophy = max(0, $oldTrophy + ($trophyDelta[$place] ?? -10));
    $newTier = ranked_tier_for_trophy($newTrophy);
    $newStreak = $didWin ? ($oldStreak + 1) : 0;
    $bestStreak = max((int)($oldProfile['best_win_streak'] ?? 0), $newStreak);
    $zenyReward = (int)floor($baseEntrySum * ($rewardRates[$place] ?? 0));

    $stmt = $mysqli->prepare("
      UPDATE ranked_profiles
      SET
        trophy = ?,
        rank_tier = ?,
        wins = wins + ?,
        losses = losses + ?,
        zeny = zeny + ?,
        win_streak = ?,
        best_win_streak = ?
      WHERE user_id = ?
      LIMIT 1
    ");
    $winAdd = $didWin ? 1 : 0;
    $lossAdd = $didWin ? 0 : 1;
    $stmt->bind_param(
      'isiiiiii',
      $newTrophy,
      $newTier,
      $winAdd,
      $lossAdd,
      $zenyReward,
      $newStreak,
      $bestStreak,
      $userId
    );
    $stmt->execute();
    $stmt->close();

    if ($zenyReward > 0) {
      $stmt = $mysqli->prepare("
        UPDATE users
        SET credits = credits + ?
        WHERE id = ?
        LIMIT 1
      ");
      $stmt->bind_param('ii', $zenyReward, $userId);
      $stmt->execute();
      $stmt->close();
    }

    $entry['ranked'] = [
      'old_trophy' => $oldTrophy,
      'new_trophy' => $newTrophy,
      'trophy_delta' => $newTrophy - $oldTrophy,
      'tier' => $newTier,
      'zeny_reward' => $zenyReward,
      'win_streak' => $newStreak,
      'exp_multiplier' => ranked_exp_multiplier($newTier, $newStreak),
    ];

    game_add_log(
      $mysqli,
      $roomId,
      (string)$entry['player_name'] . ' ranked result: ' .
      (($newTrophy - $oldTrophy) >= 0 ? '+' : '') . ($newTrophy - $oldTrophy) .
      ' trophies, +' . $zenyReward . ' Zeny.'
    );
  }
  unset($entry);
}