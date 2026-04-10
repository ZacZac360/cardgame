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
if ($roomCode === '') {
  game_json_out(['ok' => false, 'msg' => 'Room code is required.'], 400);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found.'], 404);
}

if (!game_is_room_host($room, (int)$u['id'])) {
  game_json_out(['ok' => false, 'msg' => 'Only the host can start the game.'], 403);
}

try {
  game_start_room($mysqli, $room);

  $room = game_get_room_by_code($mysqli, $roomCode);
  if (!$room) {
    game_json_out(['ok' => false, 'msg' => 'Room not found after start.'], 404);
  }

  $bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
  $payload = game_room_state_payload($mysqli, $room, (int)$u['id'], $bp);
  $payload['msg'] = 'Game started.';

  game_json_out($payload);
} catch (RuntimeException $e) {
  game_json_out(['ok' => false, 'msg' => $e->getMessage()], 409);
} catch (Throwable $e) {
  game_json_out(['ok' => false, 'msg' => 'Failed to start game: ' . $e->getMessage()], 500);
}