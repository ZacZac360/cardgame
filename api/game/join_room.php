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
$password = trim((string)($data['password'] ?? ''));

if ($roomCode === '') {
  game_json_out(['ok' => false, 'msg' => 'Room code is required.'], 400);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found.'], 404);
}

try {
  game_join_room(
    $mysqli,
    $room,
    $u,
    $password !== '' ? $password : null
  );

  $room = game_get_room_by_code($mysqli, $roomCode);
  if (!$room) {
    game_json_out(['ok' => false, 'msg' => 'Room vanished unexpectedly.'], 500);
  }

  $bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
  $payload = game_room_state_payload($mysqli, $room, (int)$u['id'], $bp);
  $payload['msg'] = 'Joined room.';
  $payload['redirect_url'] = $bp . '/room.php?code=' . urlencode((string)$room['room_code']);

  game_json_out($payload);
} catch (RuntimeException $e) {
  game_json_out(['ok' => false, 'msg' => $e->getMessage()], 409);
} catch (Throwable $e) {
  game_json_out(['ok' => false, 'msg' => 'Failed to join room: ' . $e->getMessage()], 500);
}