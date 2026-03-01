<?php
// includes/helpers.php

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
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