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

$me = game_get_room_player_by_user($mysqli, (int)$room['id'], (int)$u['id']);
if (!$me) {
  game_json_out(['ok' => false, 'msg' => 'Join the room first.'], 403);
}

$seatNo = (int)$me['seat_no'];
$res = game_apply_pass_action($mysqli, $room, $seatNo);

if (!$res['ok']) {
  game_json_out($res, 400);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found after pass.'], 404);
}

$bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
game_json_out(game_room_state_payload($mysqli, $room, (int)$u['id'], $bp));