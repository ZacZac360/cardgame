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
  game_json_out(['ok' => false, 'msg' => 'Only the host can destroy the room.'], 403);
}

$roomId = (int)$room['id'];

$mysqli->begin_transaction();

try {
  $stmt = $mysqli->prepare("
    DELETE FROM game_rooms
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
} catch (Throwable $e) {
  $mysqli->rollback();
  game_json_out([
    'ok' => false,
    'msg' => 'Failed to destroy room: ' . $e->getMessage(),
  ], 500);
}

$bp = function_exists('base_path') ? rtrim(base_path(), '/') : '';

game_json_out([
  'ok' => true,
  'msg' => 'Room destroyed.',
  'redirect_url' => $bp . '/play.php',
]);