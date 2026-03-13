<?php
// includes/helpers.php

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function create_notification(mysqli $mysqli, int $userId, string $type, string $title, string $body = '', string $linkUrl = ''): bool {
  $stmt = $mysqli->prepare("
    INSERT INTO dashboard_notifications
      (user_id, type, title, body, link_url, is_read, created_at)
    VALUES
      (?, ?, ?, ?, ?, 0, NOW())
  ");
  if (!$stmt) return false;

  $stmt->bind_param("issss", $userId, $type, $title, $body, $linkUrl);
  $ok = $stmt->execute();
  $stmt->close();

  return $ok;
}

function redirect(string $url): void {
  header("Location: $url");
  exit;
}

function is_post(): bool {
  return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

// if you’re hosting under /cardgame or /something, this works:
function base_path(): string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');

  // normalize
  if ($dir === '/' || $dir === '.') $dir = '';

  // If we're inside /admin (or /admin/whatever), strip it so base is project root
  // Examples:
  //  /cardgame/admin        -> /cardgame
  //  /admin                 -> ''   (project at web root)
  //  /cardgame/admin/tools  -> /cardgame
  if (preg_match('#^(.*?)/admin(?:/.*)?$#', $dir, $m)) {
    $dir = $m[1];
  }

  return $dir;
}

// Session flash helpers
function flash_set(string $key, string $msg): void {
  $_SESSION['_flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
  $val = $_SESSION['_flash'][$key] ?? null;
  unset($_SESSION['_flash'][$key]);
  return $val;
}

function mark_notification_read(mysqli $mysqli, int $notificationId, int $userId): bool {
  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1, read_at = NOW()
    WHERE id = ? AND user_id = ?
    LIMIT 1
  ");
  if (!$stmt) return false;

  $stmt->bind_param("ii", $notificationId, $userId);
  $ok = $stmt->execute();
  $stmt->close();

  return $ok;
}

function mark_all_notifications_read(mysqli $mysqli, int $userId): bool {
  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1, read_at = NOW()
    WHERE user_id = ? AND is_read = 0
  ");
  if (!$stmt) return false;

  $stmt->bind_param("i", $userId);
  $ok = $stmt->execute();
  $stmt->close();

  return $ok;
}