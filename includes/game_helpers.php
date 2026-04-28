<?php
declare(strict_types=1);

if (defined('LOGIA_GAME_HELPERS')) {
  return;
}
define('LOGIA_GAME_HELPERS', true);

define('LOGIA_AI_TURN_DELAY_MS', 900);

require_once __DIR__ . '/ranked_helpers.php';

define('LOGIA_ELEMENTS', ['Fire', 'Water', 'Lightning', 'Earth', 'Wind', 'Wood']);

define('LOGIA_STRONG_AGAINST', [
  'Fire'      => 'Wood',
  'Water'     => 'Fire',
  'Lightning' => 'Water',
  'Earth'     => 'Lightning',
  'Wind'      => 'Earth',
  'Wood'      => 'Wind',
]);

  /* =========================================================
     BASIC JSON / REQUEST HELPERS
  ========================================================= */

  function game_json_out(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  function game_request_json(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
  }

  function game_jdecode(?string $json, $default) {
    if ($json === null || $json === '') return $default;
    $v = json_decode($json, true);
    return is_array($v) ? $v : $default;
  }

  function game_jencode($value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }

  function game_now_mysql(): string {
    return date('Y-m-d H:i:s');
  }

  /* =========================================================
     USER / DISPLAY HELPERS
  ========================================================= */

  function game_current_user_or_fail(): array {
    if (!function_exists('current_user')) {
      game_json_out(['ok' => false, 'msg' => 'Auth helper is missing.'], 500);
    }

    $u = current_user();
    if (!$u || empty($u['id'])) {
      game_json_out(['ok' => false, 'msg' => 'Please sign in first.'], 401);
    }

    return $u;
  }

  function game_user_display_name(array $u): string {
    $display = trim((string)($u['display_name'] ?? ''));
    if ($display !== '') return $display;

    $username = trim((string)($u['username'] ?? ''));
    if ($username !== '') return $username;

    return 'Player';
  }

  /* =========================================================
     ROOM CODE / IDS
  ========================================================= */

  function game_make_id(): string {
    return bin2hex(random_bytes(6));
  }

  function game_random_room_code(int $length = 8): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($chars) - 1;
    $out = '';

    for ($i = 0; $i < $length; $i++) {
      $out .= $chars[random_int(0, $max)];
    }

    return $out;
  }

  function game_generate_unique_room_code(mysqli $mysqli, int $length = 8): string {
    for ($tries = 0; $tries < 30; $tries++) {
      $code = game_random_room_code($length);

      $stmt = $mysqli->prepare("
        SELECT id
        FROM game_rooms
        WHERE room_code = ?
        LIMIT 1
      ");
      $stmt->bind_param('s', $code);
      $stmt->execute();
      $exists = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$exists) return $code;
    }

    throw new RuntimeException('Failed to generate unique room code.');
  }

  /* =========================================================
     ROOM QUERIES
  ========================================================= */

  function game_get_room_by_id(mysqli $mysqli, int $roomId): ?array {
    $stmt = $mysqli->prepare("
      SELECT *
      FROM game_rooms
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
  }

  function game_get_room_by_code(mysqli $mysqli, string $roomCode): ?array {
    $stmt = $mysqli->prepare("
      SELECT *
      FROM game_rooms
      WHERE room_code = ?
      LIMIT 1
    ");
    $stmt->bind_param('s', $roomCode);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
  }

  function game_get_room_players(mysqli $mysqli, int $roomId): array {
    $stmt = $mysqli->prepare("
      SELECT *
      FROM game_room_players
      WHERE room_id = ?
      ORDER BY seat_no ASC
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
  }

  function game_get_room_player_by_user(mysqli $mysqli, int $roomId, int $userId): ?array {
    $stmt = $mysqli->prepare("
      SELECT *
      FROM game_room_players
      WHERE room_id = ? AND user_id = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $roomId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
  }

  function game_touch_room_player(mysqli $mysqli, int $roomPlayerId): void {
    $stmt = $mysqli->prepare("
      UPDATE game_room_players
      SET last_seen_at = NOW()
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $roomPlayerId);
    $stmt->execute();
    $stmt->close();
  }

  function game_is_room_host(array $room, int $userId): bool {
    return (int)($room['host_user_id'] ?? 0) === $userId;
  }

  function game_default_room_rules(?string $roomType = 'custom'): array {
    $roomType = (string)($roomType ?? 'custom');

    $rules = [
      'allow_ai_fill' => true,
      'starting_hand_size' => 5,
      'allow_stack_plus2' => false,
      'allow_stack_plus4' => false,
      'draw_until_playable' => false,
      'must_pass_on_draw_penalty' => true,
      'preset_key' => 'classic',
    ];

    if ($roomType === 'solo') {
      $rules['allow_ai_fill'] = true;
    }

    if ($roomType === 'ranked') {
      $rules['allow_ai_fill'] = false;
      $rules['starting_hand_size'] = 5;
      $rules['allow_stack_plus2'] = true;
      $rules['allow_stack_plus4'] = true;
      $rules['draw_until_playable'] = false;
      $rules['must_pass_on_draw_penalty'] = false;
      $rules['preset_key'] = 'ranked';
      $rules['ranked_locked'] = true;
    }

    return $rules;
  }

  function game_normalize_room_rules(array $rules, ?string $roomType = 'custom'): array {
    $defaults = game_default_room_rules($roomType);
    $merged = array_merge($defaults, $rules);

    $merged['allow_ai_fill'] = !empty($merged['allow_ai_fill']);
    $merged['allow_stack_plus2'] = !empty($merged['allow_stack_plus2']);
    $merged['allow_stack_plus4'] = !empty($merged['allow_stack_plus4']);
    $merged['draw_until_playable'] = !empty($merged['draw_until_playable']);
    $merged['must_pass_on_draw_penalty'] = !empty($merged['must_pass_on_draw_penalty']);
    $merged['preset_key'] = trim((string)($merged['preset_key'] ?? $defaults['preset_key'] ?? 'classic'));

    $startingHandSize = (int)($merged['starting_hand_size'] ?? $defaults['starting_hand_size']);
    if ($startingHandSize < 3) $startingHandSize = 3;
    if ($startingHandSize > 10) $startingHandSize = 10;
    $merged['starting_hand_size'] = $startingHandSize;

    return $merged;
  }

  function game_rules_for_preset(string $presetKey, ?string $roomType = 'custom'): array {
    $presetKey = trim((string)$presetKey);
    $base = game_default_room_rules($roomType);

    switch ($presetKey) {
      case 'classic':
        return game_normalize_room_rules([
          'preset_key' => 'classic',
          'allow_stack_plus2' => false,
          'allow_stack_plus4' => false,
          'draw_until_playable' => false,
          'must_pass_on_draw_penalty' => true,
        ], $roomType);

      case 'pressure':
        return game_normalize_room_rules([
          'preset_key' => 'pressure',
          'allow_stack_plus2' => false,
          'allow_stack_plus4' => false,
          'draw_until_playable' => true,
          'must_pass_on_draw_penalty' => false,
        ], $roomType);

      case 'chain_clash':
        return game_normalize_room_rules([
          'preset_key' => 'chain_clash',
          'allow_stack_plus2' => true,
          'allow_stack_plus4' => false,
          'draw_until_playable' => false,
          'must_pass_on_draw_penalty' => false,
        ], $roomType);

      case 'custom':
        return game_normalize_room_rules([
          'preset_key' => 'custom',
        ], $roomType);

      default:
        return game_normalize_room_rules($base, $roomType);
    }
  }

  function game_room_rules(array $room): array {
    $decoded = game_jdecode($room['rules_json'] ?? null, []);
    return game_normalize_room_rules($decoded, (string)($room['room_type'] ?? 'custom'));
  }

  function game_room_seat_order(mysqli $mysqli, int $roomId): array {
    $players = game_get_room_players($mysqli, $roomId);
    return array_values(array_map(fn($p) => (int)$p['seat_no'], $players));
  }

  function game_next_open_seat(mysqli $mysqli, int $roomId, int $maxPlayers): ?int {
    $taken = [];

    $stmt = $mysqli->prepare("
      SELECT seat_no
      FROM game_room_players
      WHERE room_id = ?
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
      $taken[(int)$row['seat_no']] = true;
    }
    $stmt->close();

    for ($seat = 1; $seat <= $maxPlayers; $seat++) {
      if (!isset($taken[$seat])) return $seat;
    }

    return null;
  }

  function game_get_player_name_by_seat(mysqli $mysqli, int $roomId, int $seatNo): string {
    $stmt = $mysqli->prepare("
      SELECT player_name
      FROM game_room_players
      WHERE room_id = ? AND seat_no = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $roomId, $seatNo);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (string)($row['player_name'] ?? ('Seat ' . $seatNo));
  }

  function game_get_user_progress_snapshot(mysqli $mysqli, int $userId): ?array {
    $stmt = $mysqli->prepare("
      SELECT id, level, exp, exp_to_next, matches_played, matches_won, is_guest
      FROM users
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if (!$user) {
      return null;
    }

    $level = max(1, (int)($user['level'] ?? 1));
    $exp = max(0, (int)($user['exp'] ?? 0));
    $expToNext = max(1, (int)($user['exp_to_next'] ?? game_level_exp_required($level)));
    $progressPct = (int)max(0, min(100, round(($exp / $expToNext) * 100)));

    return [
      'user_id' => (int)$user['id'],
      'is_guest' => ((int)($user['is_guest'] ?? 0) === 1),
      'level' => $level,
      'exp' => $exp,
      'exp_to_next' => $expToNext,
      'progress_pct' => $progressPct,
      'matches_played' => (int)($user['matches_played'] ?? 0),
      'matches_won' => (int)($user['matches_won'] ?? 0),
    ];
  }

  function game_level_exp_required(int $level): int {
    $level = max(1, $level);
    return 500 + (($level - 1) * 250);
  }

  function game_ranked_exp_multiplier(string $roomType): float {
    return $roomType === 'ranked' ? 1.5 : 1.0;
  }

  function game_base_exp_for_place(int $place): int {
    return match ($place) {
      1 => 500,
      2 => 400,
      3 => 300,
      4 => 200,
      default => 100,
    };
  }

  function game_exp_for_place(int $place, string $roomType, float $extraMultiplier = 1.0): int {
    $base = game_base_exp_for_place($place);
    return (int)round($base * game_ranked_exp_multiplier($roomType) * max(1.0, $extraMultiplier));
  }

  function game_compute_final_standings(mysqli $mysqli, array $room): array {
    $roomId = (int)$room['id'];
    $players = game_get_room_players($mysqli, $roomId);
    $winnerSeat = (int)($room['winner_seat'] ?? 0);
    $roomType = (string)($room['room_type'] ?? 'custom');

    $rows = [];
    foreach ($players as $player) {
      $seatNo = (int)$player['seat_no'];
      $hand = game_get_hand($mysqli, $roomId, $seatNo);

      $rows[] = [
        'seat_no' => $seatNo,
        'user_id' => isset($player['user_id']) ? (int)$player['user_id'] : null,
        'player_name' => (string)$player['player_name'],
        'player_type' => (string)$player['player_type'],
        'card_count' => count($hand),
      ];
    }

    usort($rows, function (array $a, array $b) use ($winnerSeat): int {
      $aWinner = ((int)$a['seat_no'] === $winnerSeat);
      $bWinner = ((int)$b['seat_no'] === $winnerSeat);

      if ($aWinner && !$bWinner) return -1;
      if (!$aWinner && $bWinner) return 1;

      if ((int)$a['card_count'] !== (int)$b['card_count']) {
        return (int)$a['card_count'] <=> (int)$b['card_count'];
      }

      return (int)$a['seat_no'] <=> (int)$b['seat_no'];
    });

    foreach ($rows as $idx => &$row) {
      $row['place'] = $idx + 1;
      $extraMultiplier = 1.0;

      if ($roomType === 'ranked' && $row['player_type'] === 'human' && !empty($row['user_id'])) {
        $profile = ranked_ensure_profile($mysqli, (int)$row['user_id']);
        $tier = ranked_tier_for_trophy((int)($profile['trophy'] ?? 1000));
        $extraMultiplier = ranked_exp_multiplier($tier, (int)($profile['win_streak'] ?? 0));
      }

      $row['xp_awarded'] = $row['player_type'] === 'human'
        ? game_exp_for_place($row['place'], $roomType, $extraMultiplier)
        : 0;

      if ($roomType === 'ranked') {
        $row['ranked_exp_multiplier'] = $extraMultiplier;
      }
    }
    unset($row);

    return $rows;
  }

  function game_apply_progress_to_user(mysqli $mysqli, int $userId, int $xpGained, bool $didWin): array {
    $stmt = $mysqli->prepare("
      SELECT id, is_guest, level, exp, exp_to_next, matches_played, matches_won
      FROM users
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
      return [
        'applied' => false,
        'xp_gained' => 0,
        'level_before' => 1,
        'level_after' => 1,
        'exp_after' => 0,
        'exp_to_next_after' => 500,
      ];
    }

    if ((int)($user['is_guest'] ?? 0) === 1) {
      return [
        'applied' => false,
        'xp_gained' => 0,
        'level_before' => (int)($user['level'] ?? 1),
        'level_after' => (int)($user['level'] ?? 1),
        'exp_after' => (int)($user['exp'] ?? 0),
        'exp_to_next_after' => game_level_exp_required((int)($user['level'] ?? 1)),
      ];
    }

    $levelBefore = max(1, (int)($user['level'] ?? 1));
    $level = $levelBefore;
    $exp = max(0, (int)($user['exp'] ?? 0));
    $expToNext = game_level_exp_required($level);

    $exp += max(0, $xpGained);

    while ($exp >= $expToNext) {
      $exp -= $expToNext;
      $level++;
      $expToNext = game_level_exp_required($level);
    }

    $matchesPlayed = (int)($user['matches_played'] ?? 0) + 1;
    $matchesWon = (int)($user['matches_won'] ?? 0) + ($didWin ? 1 : 0);

    $stmt = $mysqli->prepare("
      UPDATE users
      SET level = ?, exp = ?, exp_to_next = ?, matches_played = ?, matches_won = ?
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('iiiiii', $level, $exp, $expToNext, $matchesPlayed, $matchesWon, $userId);
    $stmt->execute();
    $stmt->close();

    return [
      'applied' => true,
      'xp_gained' => max(0, $xpGained),
      'level_before' => $levelBefore,
      'level_after' => $level,
      'exp_after' => $exp,
      'exp_to_next_after' => $expToNext,
    ];
  }

  function game_add_match_result_notification(
    mysqli $mysqli,
    int $userId,
    int $place,
    int $xpGained,
    string $roomCode,
    string $roomType,
    bool $leveledUp = false
  ): void {
    $title = $leveledUp ? 'Level Up!' : 'Match Result';
    $body = 'Placed #' . $place . ' in a ' . $roomType . ' match and earned ' . $xpGained . ' EXP.';
    $linkUrl = '/room.php?code=' . urlencode($roomCode);

    $stmt = $mysqli->prepare("
      INSERT INTO dashboard_notifications (
        user_id, type, title, body, link_url, is_read
      )
      VALUES (?, 'match_result', ?, ?, ?, 0)
    ");
    $stmt->bind_param('isss', $userId, $title, $body, $linkUrl);
    $stmt->execute();
    $stmt->close();
  }

  function game_award_room_results(mysqli $mysqli, array $room): array {
    $roomType = (string)($room['room_type'] ?? 'custom');
    $roomCode = (string)($room['room_code'] ?? '');
    $standings = game_compute_final_standings($mysqli, $room);

    ranked_apply_room_results($mysqli, $room, $standings);

    foreach ($standings as &$entry) {
      $userId = $entry['user_id'] ?? null;
      $didWin = ((int)$entry['place'] === 1);

      if ($entry['player_type'] !== 'human' || !$userId) {
        $entry['progress'] = null;
        continue;
      }

      $progress = game_apply_progress_to_user(
        $mysqli,
        (int)$userId,
        (int)$entry['xp_awarded'],
        $didWin
      );

      $entry['progress'] = $progress;

      if (!empty($progress['applied'])) {
        game_add_match_result_notification(
          $mysqli,
          (int)$userId,
          (int)$entry['place'],
          (int)$entry['xp_awarded'],
          $roomCode,
          $roomType,
          (int)$progress['level_after'] > (int)$progress['level_before']
        );
      }
    }
    unset($entry);

    return $standings;
  }

  /* =========================================================
     HANDS
  ========================================================= */

  function game_get_hand(mysqli $mysqli, int $roomId, int $seatNo): array {
    $stmt = $mysqli->prepare("
      SELECT hand_json
      FROM game_player_hands
      WHERE room_id = ? AND seat_no = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $roomId, $seatNo);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return game_jdecode($row['hand_json'] ?? null, []);
  }

  function game_set_hand(mysqli $mysqli, int $roomId, int $seatNo, array $hand): void {
    $json = game_jencode(array_values($hand));

    $stmt = $mysqli->prepare("
      INSERT INTO game_player_hands (room_id, seat_no, hand_json)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE hand_json = VALUES(hand_json)
    ");
    $stmt->bind_param('iis', $roomId, $seatNo, $json);
    $stmt->execute();
    $stmt->close();
  }

  function game_clear_hands(mysqli $mysqli, int $roomId): void {
    $stmt = $mysqli->prepare("DELETE FROM game_player_hands WHERE room_id = ?");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $stmt->close();
  }

  /* =========================================================
     LOGS
  ========================================================= */

  function game_add_log(mysqli $mysqli, int $roomId, string $text): void {
    $stmt = $mysqli->prepare("
      INSERT INTO game_logs (room_id, log_text)
      VALUES (?, ?)
    ");
    $stmt->bind_param('is', $roomId, $text);
    $stmt->execute();
    $stmt->close();
  }

  function game_get_logs(mysqli $mysqli, int $roomId, int $limit = 20): array {
    $stmt = $mysqli->prepare("
      SELECT log_text
      FROM game_logs
      WHERE room_id = ?
      ORDER BY id DESC
      LIMIT ?
    ");
    $stmt->bind_param('ii', $roomId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_map(fn($r) => (string)$r['log_text'], $rows);
  }

  /* =========================================================
     CARD CREATION
  ========================================================= */

  function game_create_normal_card(string $element, int $value): array {
    return [
      'id' => game_make_id(),
      'kind' => 'normal',
      'element' => $element,
      'value' => $value,
      'name' => $element . ' ' . $value,
    ];
  }

  function game_create_plus2(string $element): array {
    return [
      'id' => game_make_id(),
      'kind' => 'plus2',
      'element' => $element,
      'value' => null,
      'name' => '+2 ' . $element,
    ];
  }

  function game_create_plus4(): array {
    return [
      'id' => game_make_id(),
      'kind' => 'plus4',
      'element' => 'Wild',
      'value' => null,
      'name' => '+4 Wild',
    ];
  }

  function game_shuffle_cards(array $cards): array {
    shuffle($cards);
    return array_values($cards);
  }

  function game_build_deck(): array {
    $deck = [];

    foreach (LOGIA_ELEMENTS as $element) {
      for ($value = 1; $value <= 10; $value++) {
        $deck[] = game_create_normal_card($element, $value);
      }
    }

    foreach (LOGIA_ELEMENTS as $element) {
      $deck[] = game_create_normal_card($element, 5);
      $deck[] = game_create_normal_card($element, 8);
    }

    foreach (LOGIA_ELEMENTS as $element) {
      $deck[] = game_create_plus2($element);
    }

    $deck[] = game_create_plus4();
    $deck[] = game_create_plus4();
    $deck[] = game_create_plus4();
    $deck[] = game_create_plus4();

    return game_shuffle_cards($deck);
  }

  /* =========================================================
     CARD IMAGE SUPPORT
  =========================================================
     If a file exists, return its public path.
     If not, return null.
     Frontend should fall back to styled/gradient card rendering.
  ========================================================= */

  function game_card_image_relpath(array $card): ?string {
    $kind = (string)($card['kind'] ?? '');
    $element = (string)($card['element'] ?? '');
    $value = $card['value'] ?? null;

    if ($kind === 'normal') {
      return 'assets/cards/' . strtolower($element) . '/' . strtolower($element) . '_' . (int)$value . '.png';
    }

    if ($kind === 'plus2') {
      return 'assets/cards/' . strtolower($element) . '/' . strtolower($element) . '_plus2.png';
    }

    if ($kind === 'plus4') {
      return 'assets/cards/wild/wild_plus4.png';
    }

    return null;
  }

  function game_card_image_url(array $card, string $bp = ''): ?string {
    $rel = game_card_image_relpath($card);
    if (!$rel) return null;

    $root = dirname(__DIR__);
    $abs = $root . '/' . ltrim($rel, '/');

    if (!is_file($abs)) {
      return null;
    }

    $bp = rtrim($bp, '/');
    return $bp . '/' . ltrim($rel, '/');
  }

  function game_enrich_card_for_output(array $card, string $bp = ''): array {
    $card['image_url'] = game_card_image_url($card, $bp);
    $card['has_image'] = !empty($card['image_url']);
    return $card;
  }

  function game_enrich_cards_for_output(array $cards, string $bp = ''): array {
    return array_map(fn($c) => game_enrich_card_for_output($c, $bp), $cards);
  }

  /* =========================================================
     CARD RULES
  ========================================================= */

  function game_compare_elements(?string $challenger, ?string $defender): string {
    if (!$challenger || !$defender) return 'neutral';
    if ($challenger === 'Wild' || $defender === 'Wild') return 'neutral';

    if ((LOGIA_STRONG_AGAINST[$challenger] ?? null) === $defender) return 'strong';
    if ((LOGIA_STRONG_AGAINST[$defender] ?? null) === $challenger) return 'weak';
    return 'neutral';
  }

  function game_card_text(?array $card): string {
    if (!$card) return 'None';

    if (($card['kind'] ?? '') === 'normal') {
      return (string)$card['element'] . ' ' . (string)$card['value'];
    }

    if (($card['kind'] ?? '') === 'plus2') {
      return '+2 ' . (string)$card['element'];
    }

    if (($card['kind'] ?? '') === 'plus4') {
      if (!empty($card['chosenElement'])) {
        return '+4 Wild → ' . (string)$card['chosenElement'];
      }
      return '+4 Wild';
    }

    return 'Unknown';
  }

  function game_get_effective_element(?array $card): ?string {
    if (!$card) return null;

    if (($card['kind'] ?? '') === 'plus4') {
      return $card['chosenElement'] ?? null;
    }

    return $card['element'] ?? null;
  }

  function game_can_play_card(array $card, ?array $activeCard, int $pendingDraw, ?array $rules = null): bool {
    if (!$activeCard) return true;

    $rules = game_normalize_room_rules($rules ?? [], 'custom');
    $activeKind = (string)($activeCard['kind'] ?? '');
    $cardKind = (string)($card['kind'] ?? '');

    if ($pendingDraw > 0) {
      if (!empty($rules['must_pass_on_draw_penalty'])) {
        return false;
      }

      if ($activeKind === 'plus2' && $cardKind === 'plus2') {
        return !empty($rules['allow_stack_plus2']);
      }

      if ($activeKind === 'plus4' && $cardKind === 'plus4') {
        return !empty($rules['allow_stack_plus4']);
      }

      if (($cardKind === 'plus2' || $cardKind === 'plus4') && $activeKind !== $cardKind) {
        return false;
      }
    }

    if ($cardKind === 'plus4') {
      return true;
    }

    $targetElement = game_get_effective_element($activeCard);

    if ($cardKind === 'plus2' || $cardKind === 'normal') {
      $cardElement = (string)($card['element'] ?? '');

      if ($cardElement !== '' && $cardElement === $targetElement) {
        return true;
      }

      return game_compare_elements($cardElement, $targetElement) === 'strong';
    }

    return false;
  }

  function game_get_playable_cards(array $hand, ?array $activeCard, int $pendingDraw, ?array $rules = null): array {
    return array_values(array_filter(
      $hand,
      fn($card) => game_can_play_card($card, $activeCard, $pendingDraw, $rules)
    ));
  }

  function game_has_any_playable_card(array $hand, ?array $activeCard, int $pendingDraw, ?array $rules = null): bool {
    foreach ($hand as $card) {
      if (game_can_play_card($card, $activeCard, $pendingDraw, $rules)) {
        return true;
      }
    }
    return false;
  }

  function game_find_card_in_hand(array $hand, string $cardId): ?array {
    foreach ($hand as $card) {
      if (($card['id'] ?? '') === $cardId) {
        return $card;
      }
    }
    return null;
  }

  function game_remove_card_from_hand(array &$hand, string $cardId): ?array {
    foreach ($hand as $i => $card) {
      if (($card['id'] ?? '') === $cardId) {
        $removed = $card;
        array_splice($hand, $i, 1);
        return $removed;
      }
    }
    return null;
  }

  /* =========================================================
     TURN / PILE HELPERS
  ========================================================= */

  function game_next_turn_seat(mysqli $mysqli, int $roomId, int $currentSeat): int {
    $seats = game_room_seat_order($mysqli, $roomId);
    if (!$seats) return $currentSeat;

    $idx = array_search($currentSeat, $seats, true);
    if ($idx === false) return $seats[0];

    $nextIdx = ($idx + 1) % count($seats);
    return $seats[$nextIdx];
  }

  function game_ensure_draw_pile(mysqli $mysqli, array &$room): void {
    $drawPile = game_jdecode($room['draw_pile_json'] ?? null, []);
    if (count($drawPile) > 0) return;

    $discard = game_jdecode($room['discard_pile_json'] ?? null, []);
    $activeCard = game_jdecode($room['active_card_json'] ?? null, null);

    if (!$discard) return;

    $activeId = $activeCard['id'] ?? null;
    $recyclable = [];

    foreach ($discard as $card) {
      if (($card['id'] ?? '') !== $activeId) {
        $recyclable[] = $card;
      }
    }

    if (!$recyclable) return;

    $drawPile = game_shuffle_cards($recyclable);
    $room['draw_pile_json'] = game_jencode($drawPile);
    $room['discard_pile_json'] = game_jencode($activeCard ? [$activeCard] : []);
  }

  function game_draw_cards_for_seat(mysqli $mysqli, array &$room, int $seatNo, int $count): int {
    $roomId = (int)$room['id'];
    $hand = game_get_hand($mysqli, $roomId, $seatNo);
    $drawPile = game_jdecode($room['draw_pile_json'] ?? null, []);

    $drawn = 0;

    for ($i = 0; $i < $count; $i++) {
      if (!$drawPile) {
        game_ensure_draw_pile($mysqli, $room);
        $drawPile = game_jdecode($room['draw_pile_json'] ?? null, []);
      }

      if (!$drawPile) break;

      $card = array_pop($drawPile);
      if (!$card) break;

      $hand[] = $card;
      $drawn++;
      $room['draw_pile_json'] = game_jencode($drawPile);
    }

    game_set_hand($mysqli, $roomId, $seatNo, $hand);
    return $drawn;
  }

  /* =========================================================
     SAVE ROOM STATE
  ========================================================= */

    function game_save_room_state(mysqli $mysqli, array $room): void {
    $stmt = $mysqli->prepare("
        UPDATE game_rooms
        SET
        room_name = ?,
        room_type = ?,
        visibility = ?,
        password_hash = ?,
        status = ?,
        max_players = ?,
        rules_json = ?,
        created_by_user_id = ?,
        host_user_id = ?,
        current_turn_seat = ?,
        lead_seat = ?,
        last_played_seat = ?,
        winner_seat = ?,
        active_card_json = ?,
        active_element = ?,
        pending_draw = ?,
        pass_count = ?,
        draw_pile_json = ?,
        discard_pile_json = ?,
        started_at = ?,
        finished_at = ?
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new RuntimeException('Prepare failed in game_save_room_state: ' . $mysqli->error);
    }

    $roomName        = $room['room_name'] ?? null;
    $roomType        = (string)($room['room_type'] ?? 'custom');
    $visibility      = (string)($room['visibility'] ?? 'private');
    $passwordHash    = $room['password_hash'] ?? null;
    $status          = (string)($room['status'] ?? 'waiting');
    $maxPlayers      = (int)($room['max_players'] ?? 4);

    $rules = game_normalize_room_rules(
        game_jdecode($room['rules_json'] ?? null, []),
        $roomType
    );
    $rulesJson = game_jencode($rules);

    $createdByUserId = isset($room['created_by_user_id']) && $room['created_by_user_id'] !== null
        ? (int)$room['created_by_user_id']
        : null;

    $hostUserId = isset($room['host_user_id']) && $room['host_user_id'] !== null
        ? (int)$room['host_user_id']
        : null;

    $currentTurnSeat = isset($room['current_turn_seat']) && $room['current_turn_seat'] !== null
        ? (int)$room['current_turn_seat']
        : null;

    $leadSeat = isset($room['lead_seat']) && $room['lead_seat'] !== null
        ? (int)$room['lead_seat']
        : null;

    $lastPlayedSeat = isset($room['last_played_seat']) && $room['last_played_seat'] !== null
        ? (int)$room['last_played_seat']
        : null;

    $winnerSeat = isset($room['winner_seat']) && $room['winner_seat'] !== null
        ? (int)$room['winner_seat']
        : null;

    $activeCardJson  = $room['active_card_json'] ?? null;
    $activeElement   = $room['active_element'] ?? null;
    $pendingDraw     = (int)($room['pending_draw'] ?? 0);
    $passCount       = (int)($room['pass_count'] ?? 0);
    $drawPileJson    = $room['draw_pile_json'] ?? null;
    $discardPileJson = $room['discard_pile_json'] ?? null;
    $startedAt       = $room['started_at'] ?? null;
    $finishedAt      = $room['finished_at'] ?? null;
    $roomId          = (int)$room['id'];

    $stmt->bind_param(
        'sssssisiiiiiissiissssi',
        $roomName,
        $roomType,
        $visibility,
        $passwordHash,
        $status,
        $maxPlayers,
        $rulesJson,
        $createdByUserId,
        $hostUserId,
        $currentTurnSeat,
        $leadSeat,
        $lastPlayedSeat,
        $winnerSeat,
        $activeCardJson,
        $activeElement,
        $pendingDraw,
        $passCount,
        $drawPileJson,
        $discardPileJson,
        $startedAt,
        $finishedAt,
        $roomId
    );

    $stmt->execute();
    $stmt->close();
    }

  function game_restart_training_1_try_phase(mysqli $mysqli, array &$room): void {
    $roomId = (int)$room['id'];
    $setup = game_build_solo_scripted_setup('training_1');

    $players = game_get_room_players($mysqli, $roomId);

    foreach ($players as $player) {
      $seatNo = (int)$player['seat_no'];

      if ($seatNo === 1) {
        $hand = $setup['player_hand'] ?? [];
      } else {
        $hand = $setup['ai_hands'][$seatNo] ?? [];
      }

      game_set_hand($mysqli, $roomId, $seatNo, $hand);
    }

    $drawPile = array_values($setup['draw_pile'] ?? game_build_deck());
    $usedIds = [];

    $activeCard = $setup['active_card'] ?? null;
    if ($activeCard && !empty($activeCard['id'])) {
      $usedIds[$activeCard['id']] = true;
    }

    foreach (($setup['player_hand'] ?? []) as $card) {
      if (!empty($card['id'])) {
        $usedIds[$card['id']] = true;
      }
    }

    foreach (($setup['ai_hands'] ?? []) as $seatCards) {
      foreach ($seatCards as $card) {
        if (!empty($card['id'])) {
          $usedIds[$card['id']] = true;
        }
      }
    }

    $drawPile = array_values(array_filter($drawPile, function ($card) use ($usedIds) {
      $id = (string)($card['id'] ?? '');
      return $id === '' || !isset($usedIds[$id]);
    }));

    $rules = game_room_rules($room);
    $rules['training_1_phase'] = 'try';
    $rules['training_1_round'] = 2;
    $rules['tutorial_objective'] = 'Try it yourself.';
    $rules['tutorial_explanation'] = 'The hand has been reset. This time, no card is forced. Use the element rule yourself: match the table element, or play the element that beats it.';
    $rules['tutorial_tip'] = 'Look at the table card first, then choose the correct playable card.';
    $rules['tutorial_expected_element'] = '';
    $rules['tutorial_expected_kind'] = '';

    $room['rules_json'] = game_jencode($rules);
    $room['status'] = 'playing';
    $room['current_turn_seat'] = 1;
    $room['lead_seat'] = 1;
    $room['last_played_seat'] = null;
    $room['winner_seat'] = null;
    $room['active_card_json'] = $activeCard ? game_jencode($activeCard) : null;
    $room['active_element'] = $activeCard ? game_get_effective_element($activeCard) : null;
    $room['pending_draw'] = 0;
    $room['pass_count'] = 0;
    $room['draw_pile_json'] = game_jencode($drawPile);
    $room['discard_pile_json'] = $activeCard ? game_jencode([$activeCard]) : game_jencode([]);
    $room['finished_at'] = null;

    game_add_log($mysqli, $roomId, 'Guided round complete. Training 1 has reset for try-it-yourself mode.');
    game_add_log($mysqli, $roomId, 'Try it yourself: match the table element or play the element that beats it.');
  }

  /* =========================================================
     CORE ACTIONS
  ========================================================= */
  function game_restart_training_try_phase(mysqli $mysqli, array &$room, string $trainingKey): void {
    $roomId = (int)$room['id'];
    $setup = game_build_solo_scripted_setup($trainingKey);

    $players = game_get_room_players($mysqli, $roomId);

    foreach ($players as $player) {
      $seatNo = (int)$player['seat_no'];

      if ($seatNo === 1) {
        $hand = $setup['player_hand'] ?? [];
      } else {
        $hand = $setup['ai_hands'][$seatNo] ?? [];
      }

      game_set_hand($mysqli, $roomId, $seatNo, $hand);
    }

    $activeCard = $setup['active_card'] ?? null;
    $drawPile = array_values($setup['draw_pile'] ?? game_build_deck());

    $rules = game_room_rules($room);
    $rules[$trainingKey . '_phase'] = 'try';
    $rules[$trainingKey . '_round'] = 2;
    $rules['tutorial_objective'] = 'Try it yourself.';
    $rules['tutorial_explanation'] = 'The hand has been reset. This time, no card is forced. Use the lesson yourself and win the round.';
    $rules['tutorial_tip'] = 'No guide this time. Choose the correct playable card.';
    $rules['tutorial_expected_element'] = '';
    $rules['tutorial_expected_kind'] = '';

    $room['rules_json'] = game_jencode($rules);
    $room['status'] = 'playing';
    $room['current_turn_seat'] = 1;
    $room['lead_seat'] = 1;
    $room['last_played_seat'] = null;
    $room['winner_seat'] = null;
    $room['active_card_json'] = $activeCard ? game_jencode($activeCard) : null;
    $room['active_element'] = $activeCard ? game_get_effective_element($activeCard) : null;
    $room['pending_draw'] = 0;
    $room['pass_count'] = 0;
    $room['draw_pile_json'] = game_jencode($drawPile);
    $room['discard_pile_json'] = $activeCard ? game_jencode([$activeCard]) : game_jencode([]);
    $room['finished_at'] = null;

    game_add_log($mysqli, $roomId, 'Guided round complete. ' . $trainingKey . ' has reset for try-it-yourself mode.');
  }



  function game_apply_play_action(
    mysqli $mysqli,
    array &$room,
    int $seatNo,
    string $cardId,
    ?string $chosenElement = null
  ): array {
    if (($room['status'] ?? '') !== 'playing') {
      return ['ok' => false, 'msg' => 'Game is not active.'];
    }

    if ((int)($room['winner_seat'] ?? 0) > 0) {
      return ['ok' => false, 'msg' => 'Game already finished.'];
    }

    if ((int)($room['current_turn_seat'] ?? 0) !== $seatNo) {
      return ['ok' => false, 'msg' => 'Not your turn.'];
    }

    $roomId = (int)$room['id'];
    $hand = game_get_hand($mysqli, $roomId, $seatNo);
    $card = game_find_card_in_hand($hand, $cardId);

    if (!$card) {
      return ['ok' => false, 'msg' => 'Card not found in hand.'];
    }

    $activeCard = game_jdecode($room['active_card_json'] ?? null, null);
    $pendingDraw = (int)($room['pending_draw'] ?? 0);
    $rules = game_room_rules($room);

    if (!game_can_play_card($card, $activeCard, $pendingDraw, $rules)) {
      return ['ok' => false, 'msg' => 'That card cannot be played right now.'];
    }

    if (($card['kind'] ?? '') === 'plus4') {
      if (!$chosenElement || !in_array($chosenElement, LOGIA_ELEMENTS, true)) {
        return ['ok' => false, 'msg' => 'Choose an element for +4.'];
      }
    }

    $played = game_remove_card_from_hand($hand, $cardId);
    if (!$played) {
      return ['ok' => false, 'msg' => 'Failed to remove card from hand.'];
    }

    if (($played['kind'] ?? '') === 'plus4') {
      $played['chosenElement'] = $chosenElement;
    }

    $discard = game_jdecode($room['discard_pile_json'] ?? null, []);
    $discard[] = $played;

    $room['active_card_json'] = game_jencode($played);
    $room['discard_pile_json'] = game_jencode($discard);
    $room['last_played_seat'] = $seatNo;
    $room['lead_seat'] = $seatNo;
    $room['pass_count'] = 0;

    if (($played['kind'] ?? '') === 'plus2') {
      $room['pending_draw'] = max(0, (int)$room['pending_draw']) + 2;
      $room['active_element'] = (string)$played['element'];
    } elseif (($played['kind'] ?? '') === 'plus4') {
      $room['pending_draw'] = max(0, (int)$room['pending_draw']) + 4;
      $room['active_element'] = (string)$played['chosenElement'];
    } else {
      $room['pending_draw'] = 0;
      $room['active_element'] = (string)$played['element'];
    }

    game_set_hand($mysqli, $roomId, $seatNo, $hand);

    $playerName = game_get_player_name_by_seat($mysqli, $roomId, $seatNo);
    game_add_log($mysqli, $roomId, $playerName . ' played ' . game_card_text($played) . '.');

    if (count($hand) === 0) {
      $rules = game_room_rules($room);
      $soloLevelKey = (string)($rules['solo_level_key'] ?? '');

      if (
        (string)($room['room_type'] ?? '') === 'solo' &&
        in_array($soloLevelKey, ['training_1', 'training_2', 'training_3'], true) &&
        (int)($rules[$soloLevelKey . '_round'] ?? 1) < 2
      ) {
        game_restart_training_try_phase($mysqli, $room, $soloLevelKey);
        game_save_room_state($mysqli, $room);

        return ['ok' => true];
      }

      $room['winner_seat'] = $seatNo;
      $soloLevelKey = (string)($rules['solo_level_key'] ?? '');
      $training1Round = (int)($rules['training_1_round'] ?? 1);

      if (
        (string)($room['room_type'] ?? '') === 'solo' &&
        $soloLevelKey === 'training_1' &&
        $training1Round < 2
      ) {
        game_restart_training_1_try_phase($mysqli, $room);
        game_save_room_state($mysqli, $room);

        return ['ok' => true];
      }

      $room['winner_seat'] = $seatNo;
      $room['status'] = 'finished';
      $room['finished_at'] = game_now_mysql();

      game_add_log($mysqli, $roomId, $playerName . ' wins the game.');

      $standings = game_award_room_results($mysqli, $room);

      foreach ($standings as $result) {
        if (($result['player_type'] ?? '') !== 'human') {
          continue;
        }

        game_add_log(
          $mysqli,
          $roomId,
          (string)$result['player_name'] . ' finished #' . (int)$result['place'] .
          ' and earned ' . (int)$result['xp_awarded'] . ' EXP.'
        );
      }

      game_save_room_state($mysqli, $room);
      return ['ok' => true];
    }

    $room['current_turn_seat'] = game_next_turn_seat($mysqli, $roomId, $seatNo);
    game_save_room_state($mysqli, $room);

    return ['ok' => true];
  }

  function game_apply_pass_action(mysqli $mysqli, array &$room, int $seatNo): array {
    if (($room['status'] ?? '') !== 'playing') {
      return ['ok' => false, 'msg' => 'Game is not active.'];
    }

    if ((int)($room['winner_seat'] ?? 0) > 0) {
      return ['ok' => false, 'msg' => 'Game already finished.'];
    }

    if ((int)($room['current_turn_seat'] ?? 0) !== $seatNo) {
      return ['ok' => false, 'msg' => 'Not your turn.'];
    }

    $roomId = (int)$room['id'];
    $playerName = game_get_player_name_by_seat($mysqli, $roomId, $seatNo);
    $pendingDraw = (int)($room['pending_draw'] ?? 0);
    $rules = game_room_rules($room);

    if ($pendingDraw > 0) {
      $drawn = game_draw_cards_for_seat($mysqli, $room, $seatNo, $pendingDraw);
      $room['pending_draw'] = 0;
      $room['pass_count'] = ((int)$room['pass_count']) + 1;

      game_add_log($mysqli, $roomId, $playerName . ' passed and drew ' . $drawn . ' card(s).');

      $seatCount = count(game_room_seat_order($mysqli, $roomId));
      $leadSeat = (int)($room['lead_seat'] ?? 0);

      if ($leadSeat > 0 && $room['pass_count'] >= max(1, $seatCount - 1)) {
        $room['pass_count'] = 0;
        $room['current_turn_seat'] = $leadSeat;
        game_add_log(
          $mysqli,
          $roomId,
          game_get_player_name_by_seat($mysqli, $roomId, $leadSeat) . ' regains initiative.'
        );
        game_save_room_state($mysqli, $room);
        return ['ok' => true];
      }

      $room['current_turn_seat'] = game_next_turn_seat($mysqli, $roomId, $seatNo);
      game_save_room_state($mysqli, $room);
      return ['ok' => true];
    }

    if (!empty($rules['draw_until_playable'])) {
      $drawn = 0;
      $hand = game_get_hand($mysqli, $roomId, $seatNo);
      $activeCard = game_jdecode($room['active_card_json'] ?? null, null);

      while (true) {
        $drawCount = game_draw_cards_for_seat($mysqli, $room, $seatNo, 1);
        if ($drawCount <= 0) {
          break;
        }

        $drawn += $drawCount;
        $hand = game_get_hand($mysqli, $roomId, $seatNo);

        if (game_has_any_playable_card($hand, $activeCard, 0, $rules)) {
          game_add_log($mysqli, $roomId, $playerName . ' drew ' . $drawn . ' card(s) until a playable card appeared.');
          game_save_room_state($mysqli, $room);
          return ['ok' => true, 'msg' => 'Drew until playable. Your turn continues.'];
        }

        if ($drawn >= 20) {
          break;
        }
      }

      game_add_log($mysqli, $roomId, $playerName . ' drew ' . $drawn . ' card(s) and still had no playable card.');
    } else {
      $drawn = game_draw_cards_for_seat($mysqli, $room, $seatNo, 1);
      game_add_log($mysqli, $roomId, $playerName . ' passed and drew ' . $drawn . ' card.');
    }

    $room['pass_count'] = ((int)$room['pass_count']) + 1;

    $seatCount = count(game_room_seat_order($mysqli, $roomId));
    $leadSeat = (int)($room['lead_seat'] ?? 0);

    if ($leadSeat > 0 && $room['pass_count'] >= max(1, $seatCount - 1)) {
      $room['pass_count'] = 0;
      $room['current_turn_seat'] = $leadSeat;
      game_add_log(
        $mysqli,
        $roomId,
        game_get_player_name_by_seat($mysqli, $roomId, $leadSeat) . ' regains initiative.'
      );
      game_save_room_state($mysqli, $room);
      return ['ok' => true];
    }

    $room['current_turn_seat'] = game_next_turn_seat($mysqli, $roomId, $seatNo);
    game_save_room_state($mysqli, $room);

    return ['ok' => true];
  }

  /* =========================================================
     AI
  ========================================================= */

  function game_ai_card_score(array $card, ?array $activeCard, int $pendingDraw): int {
    $kind = (string)($card['kind'] ?? '');

    if ($pendingDraw > 0) {
      if ($kind === 'plus2' || $kind === 'plus4') return 0;
      return 999;
    }

    if ($kind === 'normal') return (int)($card['value'] ?? 0);
    if ($kind === 'plus2') return 60;
    if ($kind === 'plus4') return 100;

    return 999;
  }

  function game_ai_choose_element(array $hand, ?string $avoidElement = null): string {
    $counts = [];
    foreach (LOGIA_ELEMENTS as $e) $counts[$e] = 0;

    foreach ($hand as $card) {
      $el = $card['element'] ?? null;
      if ($el && isset($counts[$el])) {
        $counts[$el]++;
      }
    }

    if ($avoidElement && isset($counts[$avoidElement]) && count(array_unique($counts)) > 1) {
      $counts[$avoidElement] = max(0, $counts[$avoidElement] - 1);
    }

    arsort($counts);
    $best = array_key_first($counts);
    return $best ?: LOGIA_ELEMENTS[array_rand(LOGIA_ELEMENTS)];
  }

  function game_run_ai_until_human_or_end(mysqli $mysqli, array &$room): void {
    $roomId = (int)$room['id'];

    if (($room['status'] ?? '') !== 'playing') return;
    if ((int)($room['winner_seat'] ?? 0) > 0) return;

    $currentSeat = (int)($room['current_turn_seat'] ?? 0);
    if ($currentSeat <= 0) return;

    $stmt = $mysqli->prepare("
      SELECT player_type
      FROM game_room_players
      WHERE room_id = ? AND seat_no = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $roomId, $currentSeat);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (($row['player_type'] ?? '') !== 'ai') {
      return;
    }

    usleep(LOGIA_AI_TURN_DELAY_MS * 1000);

    $room = game_get_room_by_id($mysqli, $roomId);
    if (!$room) return;
    if (($room['status'] ?? '') !== 'playing') return;
    if ((int)($room['winner_seat'] ?? 0) > 0) return;

    $currentSeat = (int)($room['current_turn_seat'] ?? 0);
    if ($currentSeat <= 0) return;

    $stmt = $mysqli->prepare("
      SELECT player_type
      FROM game_room_players
      WHERE room_id = ? AND seat_no = ?
      LIMIT 1
    ");
    $stmt->bind_param('ii', $roomId, $currentSeat);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (($row['player_type'] ?? '') !== 'ai') {
      return;
    }

    $hand = game_get_hand($mysqli, $roomId, $currentSeat);
    $activeCard = game_jdecode($room['active_card_json'] ?? null, null);
    $pendingDraw = (int)($room['pending_draw'] ?? 0);
    $playable = game_get_playable_cards($hand, $activeCard, $pendingDraw);

    if (!$playable) {
      game_apply_pass_action($mysqli, $room, $currentSeat);
      $room = game_get_room_by_id($mysqli, $roomId);
      return;
    }

    usort(
      $playable,
      fn($a, $b) => game_ai_card_score($a, $activeCard, $pendingDraw)
        <=> game_ai_card_score($b, $activeCard, $pendingDraw)
    );

    $pick = $playable[0];
    $chosenElement = null;

    if (($pick['kind'] ?? '') === 'plus4') {
      $handWithoutPick = array_values(array_filter(
        $hand,
        fn($card) => ($card['id'] ?? '') !== ($pick['id'] ?? '')
      ));
      $targetElement = game_get_effective_element($activeCard);
      $chosenElement = game_ai_choose_element($handWithoutPick, $targetElement);
    }

    game_apply_play_action($mysqli, $room, $currentSeat, (string)$pick['id'], $chosenElement);
    $room = game_get_room_by_id($mysqli, $roomId);
  }

  /* =========================================================
     ROOM PAYLOAD
  ========================================================= */

  function game_room_state_payload(mysqli $mysqli, array $room, int $userId, string $bp = ''): array {
    $roomId = (int)$room['id'];
    $players = game_get_room_players($mysqli, $roomId);
    $me = game_get_room_player_by_user($mysqli, $roomId, $userId);

    $seats = [];
    for ($i = 1; $i <= (int)$room['max_players']; $i++) {
      $seats[$i] = [
        'seat_no' => $i,
        'occupied' => false,
        'player_name' => null,
        'player_type' => null,
        'is_host' => false,
        'is_me' => false,
        'card_count' => 0,
      ];
    }

    foreach ($players as $player) {
      $seatNo = (int)$player['seat_no'];
      $hand = game_get_hand($mysqli, $roomId, $seatNo);

      $seats[$seatNo] = [
        'seat_no' => $seatNo,
        'occupied' => true,
        'player_name' => (string)$player['player_name'],
        'player_type' => (string)$player['player_type'],
        'is_host' => ((int)$player['is_host'] === 1),
        'is_me' => $me ? ((int)$me['id'] === (int)$player['id']) : false,
        'card_count' => count($hand),
      ];
    }

    $myHand = [];
    $viewerProgress = null;

    if ($me) {
      $myHand = game_get_hand($mysqli, $roomId, (int)$me['seat_no']);
      $myHand = game_enrich_cards_for_output($myHand, $bp);
      $viewerProgress = game_get_user_progress_snapshot($mysqli, $userId);
    }

    $activeCard = game_jdecode($room['active_card_json'] ?? null, null);
    if ($activeCard) {
      $activeCard = game_enrich_card_for_output($activeCard, $bp);
    }

    $finalResults = [];
    $meResult = null;

    $rules = game_room_rules($room);
    $soloTutorial = null;

    if ((string)($room['room_type'] ?? '') === 'solo') {
      $soloLevelKey = (string)($rules['solo_level_key'] ?? 'training_1');

      $tutorialDefaults = [
        'training_1' => [
          'title' => 'Training 1 — Same Element',
          'speaker' => 'Guide',
          'objective' => 'Play the Wind card.',
          'explanation' => 'The active card is Wind. Same-element cards can be played even if the number is lower.',
          'tip' => 'Click your Wind card, then press Play or double-click it.',
          'type' => 'training',
          'expected_element' => 'Wind',
          'expected_kind' => 'normal',
        ],
        'training_2' => [
          'title' => 'Training 2 — Stronger Element',
          'speaker' => 'Guide',
          'objective' => 'Use Earth to beat Lightning.',
          'explanation' => 'The active card is Lightning. Earth beats Lightning in the matchup chart.',
          'tip' => 'Click the Earth card, then press Play or double-click it.',
          'type' => 'training',
          'expected_element' => 'Earth',
          'expected_kind' => 'normal',
        ],
        'training_3' => [
          'title' => 'Training 3 — Special Cards',
          'speaker' => 'Guide',
          'objective' => 'Use a special card.',
          'explanation' => '+2 pressures the next player. +4 lets you choose the next active element.',
          'tip' => 'Try playing +2 or +4. If you use +4, choose the next element.',
          'type' => 'training',
          'expected_element' => '',
          'expected_kind' => 'special',
        ],
        'campaign_1' => [
          'title' => 'Campaign — Foundations',
          'speaker' => 'Guide',
          'objective' => 'Win the match.',
          'explanation' => 'Use same elements, stronger elements, and special cards to empty your hand first.',
          'tip' => 'Play normally and try to win.',
          'type' => 'campaign',
          'expected_element' => '',
          'expected_kind' => '',
        ],
      ];

      $defaults = $tutorialDefaults[$soloLevelKey] ?? $tutorialDefaults['training_1'];

      $soloTutorial = [
        'level_key' => $soloLevelKey,
        'title' => (string)($rules['tutorial_title'] ?? $defaults['title']),
        'speaker' => (string)($rules['tutorial_speaker'] ?? $defaults['speaker']),
        'objective' => (string)($rules['tutorial_objective'] ?? $defaults['objective']),
        'explanation' => (string)($rules['tutorial_explanation'] ?? $defaults['explanation']),
        'tip' => (string)($rules['tutorial_tip'] ?? $defaults['tip']),
        'type' => (string)($rules['solo_type'] ?? $defaults['type']),
        'expected_element' => (string)($rules['tutorial_expected_element'] ?? $defaults['expected_element']),
        'expected_kind' => (string)($rules['tutorial_expected_kind'] ?? $defaults['expected_kind']),
      ];
    }

    if ((string)($room['status'] ?? '') === 'finished') {
      $finalResults = game_compute_final_standings($mysqli, $room);

      if ($me) {
        foreach ($finalResults as $result) {
          if ((int)$result['seat_no'] === (int)$me['seat_no']) {
            $meResult = $result;
            break;
          }
        }
      }
    }

    return [
      'ok' => true,
      'room' => [
        'id' => (int)$room['id'],
        'room_code' => (string)$room['room_code'],
        'room_name' => (string)($room['room_name'] ?? ''),
        'room_type' => (string)($room['room_type'] ?? 'custom'),
        'visibility' => (string)($room['visibility'] ?? 'private'),
        'status' => (string)$room['status'],
        'max_players' => (int)$room['max_players'],
        'rules' => game_room_rules($room),
        'human_count' => count(array_filter($players, fn($p) => ($p['player_type'] ?? '') === 'human')),
        'total_count' => count($players),
        'is_host' => (string)($room['room_type'] ?? '') === 'ranked' ? false : game_is_room_host($room, $userId),
        'current_turn_seat' => $room['current_turn_seat'] !== null ? (int)$room['current_turn_seat'] : null,
        'lead_seat' => $room['lead_seat'] !== null ? (int)$room['lead_seat'] : null,
        'last_played_seat' => $room['last_played_seat'] !== null ? (int)$room['last_played_seat'] : null,
        'winner_seat' => $room['winner_seat'] !== null ? (int)$room['winner_seat'] : null,
        'active_card' => $activeCard,
        'active_element' => $room['active_element'],
        'pending_draw' => (int)$room['pending_draw'],
        'pass_count' => (int)$room['pass_count'],
      ],
      'me' => $me ? [
        'seat_no' => (int)$me['seat_no'],
        'player_name' => (string)$me['player_name'],
        'is_host' => ((int)$me['is_host'] === 1),
        'hand' => $myHand,
        'has_playable_card' => game_has_any_playable_card(
          $myHand,
          $activeCard,
          (int)$room['pending_draw'],
          game_room_rules($room)
        ),
      ] : null,
      'seats' => array_values($seats),
      'logs' => game_get_logs($mysqli, $roomId, 20),
      'final_results' => $finalResults,
      'me_result' => $meResult,
      'viewer_progress' => $viewerProgress,
      'solo_tutorial' => $soloTutorial,
    ];
  }

  /* =========================================================
   SOLO LEVEL DEFINITIONS
========================================================= */

function game_solo_levels(): array {
  return [

    'training_1' => [
      'title' => 'Training 1 — Element Basics',
      'type' => 'training',
      'description' => 'Learn same-element plays and the basic element matchup rule.',
      'xp_reward' => 100,
      'scripted' => true,
    ],

    'training_2' => [
      'title' => 'Training 2 — Stronger Element',
      'type' => 'training',
      'description' => 'Use a stronger element to beat the active card.',
      'xp_reward' => 150,
      'scripted' => true,
    ],

    'training_3' => [
      'title' => 'Training 3 — Special Cards',
      'type' => 'training',
      'description' => 'Use +2 and +4 cards properly.',
      'xp_reward' => 200,
      'scripted' => true,
    ],

    'campaign_1' => [
      'title' => 'Campaign — Foundations',
      'type' => 'campaign',
      'description' => 'Apply everything in a real match.',
      'xp_reward' => 300,
      'scripted' => false,
    ],

  ];
}

function game_get_solo_level(string $key): ?array {
  $levels = game_solo_levels();
  return $levels[$key] ?? null;
}

function game_is_solo_scripted(string $key): bool {
  $lvl = game_get_solo_level($key);
  return !empty($lvl['scripted']);
}

function game_build_solo_room_rules(string $soloLevelKey): array {
  $level = game_get_solo_level($soloLevelKey);

  if (!$level) {
    $soloLevelKey = 'training_1';
    $level = game_get_solo_level($soloLevelKey);
  }

  $baseRules = game_default_room_rules('solo');

  if ($soloLevelKey === 'training_1') {
    return game_normalize_room_rules(array_merge($baseRules, [
      'solo_level_key' => 'training_1',
      'solo_scripted' => true,
      'starting_hand_size' => 4,
      'allow_stack_plus2' => false,
      'allow_stack_plus4' => false,
      'draw_until_playable' => false,
      'solo_type' => 'training',
      'tutorial_title' => 'Training 1 — Element Basics',
      'tutorial_speaker' => 'Guide',
      'tutorial_objective' => 'Start with the safe move: match the element.',
      'tutorial_explanation' => 'The table has Wind. A Wind card can be played on another Wind card, even if the number is lower.',
      'tutorial_tip' => 'Click the glowing Wind card, then press Play or double-click it.',
      'tutorial_expected_element' => 'Wind',
      'tutorial_expected_kind' => 'normal',
      'training_1_phase' => 'guided',
      'training_1_round' => 1,
    ]), 'solo');
  }

  if ($soloLevelKey === 'training_2') {
    return game_normalize_room_rules(array_merge($baseRules, [
      'solo_level_key' => 'training_2',
      'solo_scripted' => true,
      'starting_hand_size' => 4,
      'allow_stack_plus2' => true,
      'allow_stack_plus4' => false,
      'draw_until_playable' => false,
      'must_pass_on_draw_penalty' => false,
      'solo_type' => 'training',
      'tutorial_title' => 'Training 2 — Plus 2 Cards',
      'tutorial_speaker' => 'Guide',
      'tutorial_objective' => 'Use the glowing +2 card.',
      'tutorial_explanation' => '+2 cards are attack cards. When you play one, the next player must answer with another +2 or take the penalty.',
      'tutorial_tip' => 'Click the glowing +2 card, then press Play or double-click it.',
      'tutorial_expected_element' => '',
      'tutorial_expected_kind' => 'plus2',
      'training_2_phase' => 'guided',
      'training_2_round' => 1,
    ]), 'solo');
  }

  if ($soloLevelKey === 'training_3') {
    return game_normalize_room_rules(array_merge($baseRules, [
      'solo_level_key' => 'training_3',
      'solo_scripted' => true,
      'starting_hand_size' => 4,
      'allow_stack_plus2' => false,
      'allow_stack_plus4' => true,
      'draw_until_playable' => false,
      'must_pass_on_draw_penalty' => false,
      'solo_type' => 'training',
      'tutorial_title' => 'Training 3 — Wildcards',
      'tutorial_speaker' => 'Guide',
      'tutorial_objective' => 'Use the glowing +4 Wild card.',
      'tutorial_explanation' => '+4 Wild lets you change the active element. After playing it, choose the element you want next.',
      'tutorial_tip' => 'Click the glowing +4 Wild card, press Play, then choose an element.',
      'tutorial_expected_element' => '',
      'tutorial_expected_kind' => 'plus4',
      'training_3_phase' => 'guided',
      'training_3_round' => 1,
    ]), 'solo');
  }

  return game_normalize_room_rules(array_merge($baseRules, [
    'solo_level_key' => 'campaign_1',
    'solo_scripted' => false,
    'starting_hand_size' => 5,
    'solo_type' => 'campaign',
    'tutorial_title' => 'Campaign — Foundations',
    'tutorial_speaker' => 'Guide',
    'tutorial_objective' => 'Win the match.',
    'tutorial_explanation' => 'This is a normal solo encounter. Use same elements, stronger elements, and special cards to empty your hand first.',
    'tutorial_tip' => 'Play normally and try to win.',
    'tutorial_expected_element' => '',
    'tutorial_expected_kind' => '',
  ]), 'solo');
}

function game_build_solo_scripted_setup(string $soloLevelKey): array {
  if ($soloLevelKey === 'training_1') {
    return [
      'player_hand' => [
        game_create_normal_card('Wind', 6),
        game_create_normal_card('Wood', 8),
        game_create_normal_card('Water', 4),
        game_create_normal_card('Earth', 2),
      ],
      'ai_hands' => [
        2 => [
          game_create_normal_card('Earth', 7),
          game_create_normal_card('Lightning', 3),
          game_create_normal_card('Fire', 5),
          game_create_normal_card('Water', 2),
        ],
        3 => [
          game_create_normal_card('Lightning', 8),
          game_create_normal_card('Fire', 4),
          game_create_normal_card('Wood', 2),
          game_create_normal_card('Earth', 1),
        ],
        4 => [
          game_create_normal_card('Fire', 6),
          game_create_normal_card('Water', 7),
          game_create_normal_card('Wind', 3),
          game_create_normal_card('Lightning', 2),
        ],
      ],
      'active_card' => game_create_normal_card('Wind', 9),
      'draw_pile' => game_build_deck(),
      'hint' => 'Training 1 teaches the core rule: match the element, or play the element that beats it.',
    ];
  }

  if ($soloLevelKey === 'training_2') {
    return [
      'player_hand' => [
        game_create_plus2('Fire'),
        game_create_normal_card('Fire', 3),
        game_create_normal_card('Water', 5),
        game_create_normal_card('Earth', 8),
      ],
      'ai_hands' => [
        2 => [
          game_create_plus2('Fire'),
          game_create_normal_card('Wood', 4),
          game_create_normal_card('Water', 8),
          game_create_normal_card('Lightning', 1),
        ],
        3 => [
          game_create_normal_card('Wood', 7),
          game_create_normal_card('Fire', 9),
          game_create_normal_card('Water', 2),
          game_create_normal_card('Wind', 1),
        ],
        4 => [
          game_create_normal_card('Lightning', 5),
          game_create_normal_card('Earth', 1),
          game_create_normal_card('Wood', 6),
          game_create_normal_card('Fire', 2),
        ],
      ],
      'active_card' => game_create_normal_card('Fire', 9),
      'draw_pile' => game_build_deck(),
      'hint' => 'Training 2 teaches +2 cards. Play +2 Fire on Fire to pressure the next player.',
    ];
  }

  if ($soloLevelKey === 'training_3') {
    return [
      'player_hand' => [
        game_create_plus4(),
        game_create_normal_card('Water', 4),
        game_create_normal_card('Earth', 6),
        game_create_normal_card('Wind', 2),
      ],
      'ai_hands' => [
        2 => [
          game_create_normal_card('Wood', 8),
          game_create_normal_card('Fire', 5),
          game_create_normal_card('Lightning', 4),
          game_create_normal_card('Wind', 2),
        ],
        3 => [
          game_create_normal_card('Water', 7),
          game_create_normal_card('Wood', 3),
          game_create_normal_card('Earth', 5),
          game_create_normal_card('Fire', 1),
        ],
        4 => [
          game_create_normal_card('Lightning', 6),
          game_create_normal_card('Water', 2),
          game_create_normal_card('Wood', 9),
          game_create_normal_card('Earth', 1),
        ],
      ],
      'active_card' => game_create_normal_card('Wood', 7),
      'draw_pile' => game_build_deck(),
      'hint' => 'Training 3 teaches +4 Wild cards. Play +4, then choose the next element.',
    ];
  }

  return [];
}

function game_start_solo_scripted_room(mysqli $mysqli, array &$room): void {
  $roomId = (int)$room['id'];

  if (($room['status'] ?? '') !== 'waiting') {
    throw new RuntimeException('Room is not in waiting state.');
  }

  $rules = game_room_rules($room);
  $soloLevelKey = (string)($rules['solo_level_key'] ?? '');
  $setup = game_build_solo_scripted_setup($soloLevelKey);

  if (!$setup) {
    game_start_room($mysqli, $room);
    return;
  }

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      DELETE FROM game_room_players
      WHERE room_id = ? AND player_type = 'ai'
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $stmt->close();

    game_clear_hands($mysqli, $roomId);

    $stmt = $mysqli->prepare("DELETE FROM game_logs WHERE room_id = ?");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $stmt->close();

    $maxPlayers = (int)($room['max_players'] ?? 4);
    $players = game_get_room_players($mysqli, $roomId);
    $takenSeats = array_map(fn($p) => (int)$p['seat_no'], $players);

    for ($seat = 1; $seat <= $maxPlayers; $seat++) {
      if (!in_array($seat, $takenSeats, true)) {
        $name = 'AI ' . $seat;
        $isHost = 0;
        $nullUserId = null;

        $stmt = $mysqli->prepare("
          INSERT INTO game_room_players (
            room_id, user_id, seat_no, player_name, player_type, is_host
          )
          VALUES (?, ?, ?, ?, 'ai', ?)
        ");
        $stmt->bind_param('iiisi', $roomId, $nullUserId, $seat, $name, $isHost);
        $stmt->execute();
        $stmt->close();
      }
    }

    $players = game_get_room_players($mysqli, $roomId);

    foreach ($players as $player) {
      $seatNo = (int)$player['seat_no'];

      if ($seatNo === 1) {
        $hand = $setup['player_hand'] ?? [];
      } else {
        $hand = $setup['ai_hands'][$seatNo] ?? [];
      }

      game_set_hand($mysqli, $roomId, $seatNo, $hand);
    }

    $drawPile = array_values($setup['draw_pile'] ?? game_build_deck());
    $usedIds = [];

    $activeCard = $setup['active_card'] ?? null;
    if ($activeCard && !empty($activeCard['id'])) {
      $usedIds[$activeCard['id']] = true;
    }

    foreach (($setup['player_hand'] ?? []) as $card) {
      if (!empty($card['id'])) $usedIds[$card['id']] = true;
    }

    foreach (($setup['ai_hands'] ?? []) as $seatCards) {
      foreach ($seatCards as $card) {
        if (!empty($card['id'])) $usedIds[$card['id']] = true;
      }
    }

    $drawPile = array_values(array_filter($drawPile, function ($card) use ($usedIds) {
      $id = (string)($card['id'] ?? '');
      return $id === '' || !isset($usedIds[$id]);
    }));

    $firstSeat = 1;

    $room['status'] = 'playing';
    $room['current_turn_seat'] = $firstSeat;
    $room['lead_seat'] = $firstSeat;
    $room['last_played_seat'] = null;
    $room['winner_seat'] = null;
    $room['active_card_json'] = $activeCard ? game_jencode($activeCard) : null;
    $room['active_element'] = $activeCard ? game_get_effective_element($activeCard) : null;
    $room['pending_draw'] = 0;
    $room['pass_count'] = 0;
    $room['draw_pile_json'] = game_jencode($drawPile);
    $room['discard_pile_json'] = $activeCard ? game_jencode([$activeCard]) : game_jencode([]);
    $room['started_at'] = game_now_mysql();
    $room['finished_at'] = null;

    game_save_room_state($mysqli, $room);

    game_add_log($mysqli, $roomId, 'Solo lesson started.');
    if (!empty($setup['hint'])) {
      game_add_log($mysqli, $roomId, (string)$setup['hint']);
    }
    game_add_log($mysqli, $roomId, game_get_player_name_by_seat($mysqli, $roomId, $firstSeat) . ' takes the first turn.');

    $mysqli->commit();
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }
}

  /* =========================================================
     ROOM CREATION / MEMBERSHIP
  ========================================================= */

  function game_create_room(
    mysqli $mysqli,
    array $user,
    string $roomType = 'custom',
    string $visibility = 'private',
    ?string $roomName = null,
    int $maxPlayers = 4,
    ?string $plainPassword = null
  ): array {
    $userId = (int)$user['id'];
    $playerName = game_user_display_name($user);

    if (!in_array($roomType, ['custom', 'solo', 'casual', 'ranked'], true)) {
      $roomType = 'custom';
    }

    if (!in_array($visibility, ['private', 'public'], true)) {
      $visibility = 'private';
    }

    if (!in_array($maxPlayers, [2, 3, 4], true)) {
      $maxPlayers = 4;
    }

    $passwordHash = null;
    if ($plainPassword !== null && trim($plainPassword) !== '') {
      $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    $roomCode = game_generate_unique_room_code($mysqli, 8);

    $mysqli->begin_transaction();

    try {
      $stmt = $mysqli->prepare("
        INSERT INTO game_rooms (
          room_code, room_name, room_type, visibility, password_hash,
          status, max_players, created_by_user_id, host_user_id
        )
        VALUES (?, ?, ?, ?, ?, 'waiting', ?, ?, ?)
      ");
      $stmt->bind_param(
        'sssssiii',
        $roomCode,
        $roomName,
        $roomType,
        $visibility,
        $passwordHash,
        $maxPlayers,
        $userId,
        $userId
      );
      $stmt->execute();
      $roomId = (int)$stmt->insert_id;
      $stmt->close();

      $seatNo = 1;
      $isHost = 1;

      $stmt = $mysqli->prepare("
        INSERT INTO game_room_players (
          room_id, user_id, seat_no, player_name, player_type, is_host
        )
        VALUES (?, ?, ?, ?, 'human', ?)
      ");
      $stmt->bind_param('iiisi', $roomId, $userId, $seatNo, $playerName, $isHost);
      $stmt->execute();
      $stmt->close();

      game_add_log($mysqli, $roomId, $playerName . ' created the room.');
      game_audit_log(
        $mysqli,
        $userId,
        'ROOM_CREATE',
        'game_room',
        $roomId,
        [
            'room_code' => $roomCode,
            'room_name' => $roomName,
            'room_type' => $roomType,
            'visibility' => $visibility,
            'max_players' => $maxPlayers,
        ]
      );
      $mysqli->commit();

      return game_get_room_by_id($mysqli, $roomId);
    } catch (Throwable $e) {
      $mysqli->rollback();
      throw $e;
    }
  }

  function game_join_room(
    mysqli $mysqli,
    array $room,
    array $user,
    ?string $plainPassword = null
  ): array {
    $roomId = (int)$room['id'];
    $userId = (int)$user['id'];
    $playerName = game_user_display_name($user);

    $existing = game_get_room_player_by_user($mysqli, $roomId, $userId);
    if ($existing) {
      game_touch_room_player($mysqli, (int)$existing['id']);
      return $existing;
    }

    if ((string)$room['status'] !== 'waiting') {
      throw new RuntimeException('Game already started. Wait for next room.');
    }

    if (!empty($room['password_hash'])) {
      if ($plainPassword === null || !password_verify($plainPassword, (string)$room['password_hash'])) {
        throw new RuntimeException('Incorrect room password.');
      }
    }

    $seat = game_next_open_seat($mysqli, $roomId, (int)$room['max_players']);
    if ($seat === null) {
      throw new RuntimeException('Room is full.');
    }

    $stmt = $mysqli->prepare("
      INSERT INTO game_room_players (
        room_id, user_id, seat_no, player_name, player_type, is_host
      )
      VALUES (?, ?, ?, ?, 'human', 0)
    ");
    $stmt->bind_param('iiis', $roomId, $userId, $seat, $playerName);
    $stmt->execute();
    $playerId = (int)$stmt->insert_id;
    $stmt->close();

    game_add_log($mysqli, $roomId, $playerName . ' joined the room.');
    
    $stmt = $mysqli->prepare("
      SELECT *
      FROM game_room_players
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $playerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ?: [];
  }

  function game_start_room(mysqli $mysqli, array &$room): void {
    $roomId = (int)$room['id'];

    if (($room['status'] ?? '') !== 'waiting') {
      throw new RuntimeException('Room is not in waiting state.');
    }

    $players = game_get_room_players($mysqli, $roomId);
    $humanPlayers = array_values(array_filter(
      $players,
      fn($p) => ($p['player_type'] ?? '') === 'human'
    ));

    if (count($humanPlayers) < 1) {
      throw new RuntimeException('At least one human must join first.');
    }

    $maxPlayers = (int)$room['max_players'];
    $rules = game_room_rules($room);
    $startingHandSize = (int)($rules['starting_hand_size'] ?? 5);

    $mysqli->begin_transaction();

    try {
      $stmt = $mysqli->prepare("
        DELETE FROM game_room_players
        WHERE room_id = ? AND player_type = 'ai'
      ");
      $stmt->bind_param('i', $roomId);
      $stmt->execute();
      $stmt->close();

      game_clear_hands($mysqli, $roomId);

      $stmt = $mysqli->prepare("DELETE FROM game_logs WHERE room_id = ?");
      $stmt->bind_param('i', $roomId);
      $stmt->execute();
      $stmt->close();

      $players = game_get_room_players($mysqli, $roomId);
      $takenSeats = array_map(fn($p) => (int)$p['seat_no'], $players);

      if (!empty($rules['allow_ai_fill'])) {
        for ($seat = 1; $seat <= $maxPlayers; $seat++) {
          if (!in_array($seat, $takenSeats, true)) {
            $name = 'AI ' . $seat;
            $isHost = 0;
            $nullUserId = null;

            $stmt = $mysqli->prepare("
              INSERT INTO game_room_players (
                room_id, user_id, seat_no, player_name, player_type, is_host
              )
              VALUES (?, ?, ?, ?, 'ai', ?)
            ");
            $stmt->bind_param('iiisi', $roomId, $nullUserId, $seat, $name, $isHost);
            $stmt->execute();
            $stmt->close();
          }
        }
      }

      $players = game_get_room_players($mysqli, $roomId);
      $deck = game_build_deck();

      foreach ($players as $player) {
        $seatNo = (int)$player['seat_no'];
        $hand = [];

        for ($i = 0; $i < $startingHandSize; $i++) {
          $card = array_pop($deck);
          if ($card) $hand[] = $card;
        }

        game_set_hand($mysqli, $roomId, $seatNo, $hand);
      }

      $seatOrder = game_room_seat_order($mysqli, $roomId);
      $firstSeat = $seatOrder[0] ?? 1;

      $room['status'] = 'playing';
      $room['current_turn_seat'] = $firstSeat;
      $room['lead_seat'] = $firstSeat;
      $room['last_played_seat'] = null;
      $room['winner_seat'] = null;
      $room['active_card_json'] = null;
      $room['active_element'] = null;
      $room['pending_draw'] = 0;
      $room['pass_count'] = 0;
      $room['draw_pile_json'] = game_jencode(array_values($deck));
      $room['discard_pile_json'] = game_jencode([]);
      $room['started_at'] = game_now_mysql();
      $room['finished_at'] = null;

      game_save_room_state($mysqli, $room);

      game_add_log($mysqli, $roomId, 'Game started with ' . $maxPlayers . ' total seat(s).');
      game_add_log($mysqli, $roomId, game_get_player_name_by_seat($mysqli, $roomId, $firstSeat) . ' takes the first turn.');

      $mysqli->commit();
    } catch (Throwable $e) {
      $mysqli->rollback();
      throw $e;
    }
  }

  function game_client_ip_binary(): ?string {
  $candidates = [
    $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
    $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
    $_SERVER['REMOTE_ADDR'] ?? null,
  ];

  foreach ($candidates as $candidate) {
    if (!$candidate) continue;

    $ip = trim(explode(',', $candidate)[0]);
    if ($ip === '') continue;

    $packed = @inet_pton($ip);
    if ($packed !== false) {
      return $packed;
    }
  }

  return null;
}

function game_audit_log(
  mysqli $mysqli,
  ?int $actorUserId,
  string $action,
  string $targetType,
  ?int $targetId = null,
  ?array $metadata = null
): void {
  $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
  $ipBinary = game_client_ip_binary();

  $stmt = $mysqli->prepare("
    INSERT INTO audit_logs (
      actor_user_id,
      action,
      target_type,
      target_id,
      metadata_json,
      ip_address
    )
    VALUES (?, ?, ?, ?, ?, ?)
  ");

  if (!$stmt) {
    throw new RuntimeException('Prepare failed in game_audit_log: ' . $mysqli->error);
  }

  $stmt->bind_param(
    'ississ',
    $actorUserId,
    $action,
    $targetType,
    $targetId,
    $metadataJson,
    $ipBinary
  );

  $stmt->execute();
  $stmt->close();
}