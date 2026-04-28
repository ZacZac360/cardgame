<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/game_helpers.php';

$user = game_current_user_or_fail();

try {
  $status = ranked_cancel_queue($mysqli, (int)$user['id']);

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