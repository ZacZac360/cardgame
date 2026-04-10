<?php
declare(strict_types=1);

if (defined('LOGIA_GAME_HELPERS')) {
  return;
}
define('LOGIA_GAME_HELPERS', true);

define('LOGIA_AI_TURN_DELAY_MS', 900);

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

  function game_can_play_card(array $card, ?array $activeCard, int $pendingDraw): bool {
    if (!$activeCard) return true;

    if (($activeCard['kind'] ?? '') === 'plus4' && $pendingDraw > 0) {
      return ($card['kind'] ?? '') === 'plus4';
    }

    if (($activeCard['kind'] ?? '') === 'plus2' && $pendingDraw > 0) {
      return ($card['kind'] ?? '') === 'plus2';
    }

    if (($card['kind'] ?? '') === 'plus4') {
      return true;
    }

    $targetElement = game_get_effective_element($activeCard);

    if (($card['kind'] ?? '') === 'plus2') {
      return game_compare_elements((string)$card['element'], $targetElement) === 'strong';
    }

    if (($card['kind'] ?? '') === 'normal') {
      return game_compare_elements((string)$card['element'], $targetElement) === 'strong';
    }

    return false;
  }

  function game_get_playable_cards(array $hand, ?array $activeCard, int $pendingDraw): array {
    return array_values(array_filter(
      $hand,
      fn($card) => game_can_play_card($card, $activeCard, $pendingDraw)
    ));
  }

  function game_has_any_playable_card(array $hand, ?array $activeCard, int $pendingDraw): bool {
    foreach ($hand as $card) {
      if (game_can_play_card($card, $activeCard, $pendingDraw)) {
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
        'sssssiiiiiiissiissssi',
        $roomName,
        $roomType,
        $visibility,
        $passwordHash,
        $status,
        $maxPlayers,
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

  /* =========================================================
     CORE ACTIONS
  ========================================================= */

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

    if (!game_can_play_card($card, $activeCard, $pendingDraw)) {
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
      $room['winner_seat'] = $seatNo;
      $room['status'] = 'finished';
      $room['finished_at'] = game_now_mysql();

      game_add_log($mysqli, $roomId, $playerName . ' wins the game.');
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

    if ($pendingDraw > 0) {
      $drawn = game_draw_cards_for_seat($mysqli, $room, $seatNo, $pendingDraw);
      $room['pending_draw'] = 0;
      game_add_log($mysqli, $roomId, $playerName . ' passed and drew ' . $drawn . ' card(s).');
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
    if ($me) {
      $myHand = game_get_hand($mysqli, $roomId, (int)$me['seat_no']);
      $myHand = game_enrich_cards_for_output($myHand, $bp);
    }

    $activeCard = game_jdecode($room['active_card_json'] ?? null, null);
    if ($activeCard) {
      $activeCard = game_enrich_card_for_output($activeCard, $bp);
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
        'human_count' => count(array_filter($players, fn($p) => ($p['player_type'] ?? '') === 'human')),
        'total_count' => count($players),
        'is_host' => game_is_room_host($room, $userId),
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
          (int)$room['pending_draw']
        ),
      ] : null,
      'seats' => array_values($seats),
      'logs' => game_get_logs($mysqli, $roomId, 20),
    ];
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
      $deck = game_build_deck();

      foreach ($players as $player) {
        $seatNo = (int)$player['seat_no'];
        $hand = [];

        for ($i = 0; $i < 7; $i++) {
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