<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/game_helpers.php';

require_login();

$u = game_current_user_or_fail();

$roomCode = strtoupper(trim((string)($_GET['room_code'] ?? '')));
if ($roomCode === '') {
  game_json_out(['ok' => false, 'msg' => 'Room code is required.'], 400);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found.'], 404);
}

$me = game_get_room_player_by_user($mysqli, (int)$room['id'], (int)$u['id']);
if ($me) {
  game_touch_room_player($mysqli, (int)$me['id']);
}

if (($room['status'] ?? '') === 'playing') {
  $currentSeat = (int)($room['current_turn_seat'] ?? 0);

  if ($currentSeat > 0) {
    $stmt = $mysqli->prepare("
      SELECT player_type
      FROM game_room_players
      WHERE room_id = ? AND seat_no = ?
      LIMIT 1
    ");
    $roomId = (int)$room['id'];
    $stmt->bind_param('ii', $roomId, $currentSeat);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (($row['player_type'] ?? '') === 'ai') {
      game_run_ai_until_human_or_end($mysqli, $room);
      $room = game_get_room_by_code($mysqli, $roomCode);
      if (!$room) {
        game_json_out(['ok' => false, 'msg' => 'Room not found after AI turn.'], 404);
      }
    }
  }
}

$bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
game_json_out(game_room_state_payload($mysqli, $room, (int)$u['id'], $bp));