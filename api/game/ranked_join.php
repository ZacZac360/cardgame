<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/game_helpers.php';

$user = game_current_user_or_fail();

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) {
  $payload = [];
}

$leagueKey = strtolower(trim((string)($payload['league'] ?? 'bronze')));

try {
  $status = ranked_enter_queue($mysqli, $user, $leagueKey);

  game_json_out([
    'ok' => true,
    'status' => $status,
  ]);
} catch (Throwable $e) {
  game_json_out([
    'ok' => false,
    'msg' => $e->getMessage(),
  ], 400);
}