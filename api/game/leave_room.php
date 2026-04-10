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
  game_json_out([
    'ok' => true,
    'msg' => 'Room already gone.',
    'redirect_url' => rtrim(function_exists('base_path') ? base_path() : '', '/') . '/play.php',
  ]);
}

$roomId = (int)$room['id'];
$userId = (int)$u['id'];

$me = game_get_room_player_by_user($mysqli, $roomId, $userId);
if (!$me) {
  game_json_out([
    'ok' => true,
    'msg' => 'You are no longer in that room.',
    'redirect_url' => rtrim(function_exists('base_path') ? base_path() : '', '/') . '/play.php',
  ]);
}

$isHost = game_is_room_host($room, $userId);
$playerName = (string)($me['player_name'] ?? 'Player');
$bp = rtrim(function_exists('base_path') ? base_path() : '', '/');

$mysqli->begin_transaction();

try {
  $stmt = $mysqli->prepare("
    DELETE FROM game_room_players
    WHERE id = ?
    LIMIT 1
  ");
  $playerRowId = (int)$me['id'];
  $stmt->bind_param('i', $playerRowId);
  $stmt->execute();
  $stmt->close();

  if (!$isHost) {
    game_add_log($mysqli, $roomId, $playerName . ' left the room.');
  }

  $stmt = $mysqli->prepare("
    SELECT COUNT(*) AS c
    FROM game_room_players
    WHERE room_id = ? AND player_type = 'human'
  ");
  $stmt->bind_param('i', $roomId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc() ?: ['c' => 0];
  $stmt->close();

  $humanCount = (int)$row['c'];
  $destroy = $isHost || $humanCount <= 0;

  if ($destroy) {
    $stmt = $mysqli->prepare("
      DELETE FROM game_rooms
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param('i', $roomId);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    game_json_out([
      'ok' => true,
      'msg' => $isHost ? 'You left and the room was destroyed.' : 'Room closed because no human players remained.',
      'redirect_url' => $bp . '/play.php',
    ]);
  }

  $room = game_get_room_by_id($mysqli, $roomId);
  if ($room) {
    $currentTurnSeat = isset($room['current_turn_seat']) ? (int)$room['current_turn_seat'] : 0;
    $leadSeat = isset($room['lead_seat']) ? (int)$room['lead_seat'] : 0;
    $lastPlayedSeat = isset($room['last_played_seat']) ? (int)$room['last_played_seat'] : 0;
    $winnerSeat = isset($room['winner_seat']) ? (int)$room['winner_seat'] : 0;

    $seatOrder = game_room_seat_order($mysqli, $roomId);

    $seatExists = function(int $seat) use ($seatOrder): bool {
      return $seat > 0 && in_array($seat, $seatOrder, true);
    };

    if ($currentTurnSeat > 0 && !$seatExists($currentTurnSeat) && $seatOrder) {
      $room['current_turn_seat'] = $seatOrder[0];
    }

    if ($leadSeat > 0 && !$seatExists($leadSeat)) {
      $room['lead_seat'] = $seatOrder[0] ?? null;
    }

    if ($lastPlayedSeat > 0 && !$seatExists($lastPlayedSeat)) {
      $room['last_played_seat'] = null;
    }

    if ($winnerSeat > 0 && !$seatExists($winnerSeat)) {
      $room['winner_seat'] = null;
    }

    game_save_room_state($mysqli, $room);
  }

  $mysqli->commit();

  game_json_out([
    'ok' => true,
    'msg' => 'You left the room.',
    'redirect_url' => $bp . '/play.php',
  ]);
} catch (Throwable $e) {
  $mysqli->rollback();
  game_json_out([
    'ok' => false,
    'msg' => 'Failed to leave room: ' . $e->getMessage(),
  ], 500);
}