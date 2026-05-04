<?php
// guest_quick_match.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/game_helpers.php";

$bp = base_path();

require_login();

$u = current_user();
if (!$u) {
  header("Location: {$bp}/index.php");
  exit;
}

$isGuest = ((int)($u['is_guest'] ?? 0) === 1);

if (!$isGuest) {
  header("Location: {$bp}/play.php");
  exit;
}

try {
  /*
    Guest Find Match:
    - Creates a casual public room
    - Uses AI fill
    - Starts immediately
    - Sends guest straight to live room
  */

  $room = game_create_room(
    $mysqli,
    $u,
    'casual',
    'public',
    'Guest Quick Match',
    4,
    null
  );

  if (!$room) {
    throw new RuntimeException("Could not create guest match.");
  }

  $roomId = (int)$room['id'];

  $rules = game_rules_for_preset('classic', 'casual');
  $rules['allow_ai_fill'] = true;
  $rules['starting_hand_size'] = 5;

  $rulesJson = game_jencode($rules);

  $stmt = $mysqli->prepare("
    UPDATE game_rooms
    SET rules_json = ?
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("si", $rulesJson, $roomId);
  $stmt->execute();
  $stmt->close();

  $room = game_get_room_by_id($mysqli, $roomId);
  if (!$room) {
    throw new RuntimeException("Guest match room disappeared.");
  }

  game_start_room($mysqli, $room);

  $roomCode = urlencode((string)$room['room_code']);
  header("Location: {$bp}/room.php?code={$roomCode}");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_error'] = "Could not start guest match: " . $e->getMessage();
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}