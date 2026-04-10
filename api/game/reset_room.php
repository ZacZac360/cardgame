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
  game_json_out(['ok' => false, 'msg' => 'Only the host can reset the room.'], 403);
}

$roomId = (int)$room['id'];

$mysqli->begin_transaction();

try {
  $stmt = $mysqli->prepare("DELETE FROM game_logs WHERE room_id = ?");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("DELETE FROM game_player_hands WHERE room_id = ?");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("
    DELETE FROM game_room_players
    WHERE room_id = ? AND player_type = 'ai'
  ");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("
    UPDATE game_rooms
    SET
      status = 'waiting',
      current_turn_seat = NULL,
      lead_seat = NULL,
      last_played_seat = NULL,
      winner_seat = NULL,
      active_card_json = NULL,
      active_element = NULL,
      pending_draw = 0,
      pass_count = 0,
      draw_pile_json = NULL,
      discard_pile_json = NULL,
      started_at = NULL,
      finished_at = NULL
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $stmt->close();

  game_add_log($mysqli, $roomId, 'Room reset by host.');

  $mysqli->commit();
} catch (Throwable $e) {
  $mysqli->rollback();
  game_json_out(['ok' => false, 'msg' => 'Reset failed: ' . $e->getMessage()], 500);
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  game_json_out(['ok' => false, 'msg' => 'Room not found after reset.'], 404);
}

$bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';
$payload = game_room_state_payload($mysqli, $room, (int)$u['id'], $bp);
$payload['msg'] = 'Room reset complete.';

game_json_out($payload);