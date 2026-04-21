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

$roomName = trim((string)($data['room_name'] ?? ''));
$roomType = trim((string)($data['room_type'] ?? 'custom'));
$visibility = trim((string)($data['visibility'] ?? 'private'));
$maxPlayers = (int)($data['max_players'] ?? 4);
$password = trim((string)($data['password'] ?? ''));
$presetKey = trim((string)($data['preset_key'] ?? ''));

if ($roomName !== '' && mb_strlen($roomName) > 80) {
  $roomName = mb_substr($roomName, 0, 80);
}

if (!in_array($roomType, ['custom', 'solo', 'casual', 'ranked'], true)) {
  $roomType = 'custom';
}

if (!in_array($visibility, ['private', 'public'], true)) {
  $visibility = 'private';
}

if (!in_array($maxPlayers, [2, 3, 4], true)) {
  game_json_out(['ok' => false, 'msg' => 'Invalid player count.'], 400);
}

if ($roomType === 'solo') {
  $maxPlayers = 4;
  $visibility = 'private';
}

if ($presetKey === '') {
  $presetKey = match ($roomType) {
    'casual' => 'classic',
    'ranked' => 'pressure',
    'solo' => 'classic',
    default => 'custom',
  };
}

try {
  $room = game_create_room(
    $mysqli,
    $u,
    $roomType,
    $visibility,
    $roomName !== '' ? $roomName : null,
    $maxPlayers,
    $password !== '' ? $password : null
  );

  $roomId = (int)$room['id'];
  $rules = game_rules_for_preset($presetKey, $roomType);
  $rulesJson = game_jencode($rules);

  $stmt = $mysqli->prepare("
    UPDATE game_rooms
    SET rules_json = ?
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param('si', $rulesJson, $roomId);
  $stmt->execute();
  $stmt->close();

  $room = game_get_room_by_id($mysqli, $roomId);
  if (!$room) {
    game_json_out(['ok' => false, 'msg' => 'Room not found after create.'], 404);
  }

  $bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
  $payload = game_room_state_payload($mysqli, $room, (int)$u['id'], $bp);
  $payload['msg'] = 'Room created.';
  $payload['redirect_url'] = $bp . '/room.php?code=' . urlencode((string)$room['room_code']);

  game_json_out($payload);
} catch (Throwable $e) {
  game_json_out([
    'ok' => false,
    'msg' => 'Failed to create room: ' . $e->getMessage(),
  ], 500);
}