<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/game_helpers.php';

require_login();

$u = game_current_user_or_fail();
$data = game_request_json();

$roomCode = strtoupper(trim((string)($data['room_code'] ?? '')));
$maxPlayers = (int)($data['max_players'] ?? 0);
$rulesInput = is_array($data['rules'] ?? null) ? $data['rules'] : [];

if ($roomCode === '') {
  game_json_out(['ok' => false, 'msg' => 'Room code is required.'], 400);
}

if (!in_array($maxPlayers, [2, 3, 4], true)) {
  game_json_out(['ok' => false, 'msg' => 'Invalid player count.'], 400);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found.'], 404);
}

if (!game_is_room_host($room, (int)$u['id'])) {
  game_json_out(['ok' => false, 'msg' => 'Only the host can change room settings.'], 403);
}

if ((string)$room['status'] !== 'waiting') {
  game_json_out(['ok' => false, 'msg' => 'Cannot change mode after game start.'], 409);
}

$stmt = $mysqli->prepare("
  SELECT COUNT(*) AS c
  FROM game_room_players
  WHERE room_id = ? AND player_type = 'human'
");
$roomId = (int)$room['id'];
$stmt->bind_param('i', $roomId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$humanCount = (int)($row['c'] ?? 0);
if ($humanCount > $maxPlayers) {
  game_json_out(['ok' => false, 'msg' => 'Too many human players already joined for that mode.'], 409);
}

$normalizedRules = game_normalize_room_rules($rulesInput, (string)$room['room_type']);
$rulesJson = game_jencode($normalizedRules);

$stmt = $mysqli->prepare("
  UPDATE game_rooms
  SET max_players = ?, rules_json = ?
  WHERE id = ?
  LIMIT 1
");
$stmt->bind_param('isi', $maxPlayers, $rulesJson, $roomId);
$stmt->execute();
$stmt->close();

if (function_exists('game_audit_log')) {
  game_audit_log(
    $mysqli,
    (int)$u['id'],
    'ROOM_UPDATE_RULES',
    'game_room',
    $roomId,
    [
      'room_code' => $roomCode,
      'max_players' => $maxPlayers,
      'rules' => $normalizedRules,
    ]
  );
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found after update.'], 404);
}

$bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
$payload = game_room_state_payload($mysqli, $room, (int)$u['id'], $bp);
$payload['msg'] = 'Room updated.';

game_json_out($payload);