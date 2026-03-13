<?php

function profile_load_state(mysqli $mysqli, array $u): array {
  $is_guest = ((int)($u['is_guest'] ?? 0) === 1);

  $username  = (string)($u['username'] ?? 'Player');
  $email     = (string)($u['email'] ?? '');
  $roleLabel = $is_guest ? 'Guest' : 'Player';
  $displayName = (string)($u['display_name'] ?? $username);

  $playerId = 'CG-' . str_pad((string)((int)($u['id'] ?? 0)), 6, '0', STR_PAD_LEFT);
  $joinedAt = !empty($u['created_at']) ? date("M d, Y", strtotime((string)$u['created_at'])) : '—';
  $bio      = trim((string)($u['bio'] ?? ''));

  $appearanceMode = (string)($u['appearance_mode'] ?? 'default');
  if (!in_array($appearanceMode, ['default', 'dark', 'light'], true)) {
    $appearanceMode = 'default';
  }

  $avatar   = trim((string)($u['avatar_path'] ?? ''));
  $avatarInitial = strtoupper(substr($username, 0, 1));

  $emailVerified = !empty($u['email_verified_at']);
  $approved      = (($u['approval_status'] ?? '') === 'approved');

  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $u['id']);
  $stmt->execute();
  $twofaEnabled = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0) === 1;
  $stmt->close();

  $level         = (int)($u['level'] ?? 1);
  $exp           = (int)($u['exp'] ?? 0);
  $expToNext     = (int)($u['exp_to_next'] ?? 100);
  $credits       = (int)($u['credits'] ?? 0);
  $matchesPlayed = (int)($u['matches_played'] ?? 0);
  $matchesWon    = (int)($u['matches_won'] ?? 0);
  $winRate       = $matchesPlayed > 0 ? round(($matchesWon / $matchesPlayed) * 100) : 0;

  $profileChecks = [
    !empty($avatar),
    $bio !== '',
    $emailVerified,
    $twofaEnabled,
  ];

  $profileCompletion = (int)round(
    (array_sum(array_map(fn($v) => $v ? 1 : 0, $profileChecks)) / count($profileChecks)) * 100
  );

  return [
    'is_guest'          => $is_guest,
    'username'          => $username,
    'email'             => $email,
    'roleLabel'         => $roleLabel,
    'displayName'       => $displayName,
    'playerId'          => $playerId,
    'joinedAt'          => $joinedAt,
    'bio'               => $bio,
    'appearanceMode'    => $appearanceMode,
    'avatar'            => $avatar,
    'avatarInitial'     => $avatarInitial,
    'emailVerified'     => $emailVerified,
    'twofaEnabled'      => $twofaEnabled,
    'approved'          => $approved,
    'level'             => $level,
    'exp'               => $exp,
    'expToNext'         => $expToNext,
    'credits'           => $credits,
    'matchesPlayed'     => $matchesPlayed,
    'matchesWon'        => $matchesWon,
    'winRate'           => $winRate,
    'profileCompletion' => $profileCompletion,
  ];
}

function profile_handle_post(mysqli $mysqli, array $u, string $bp, string $tab): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
  }

  if ($tab === 'appearance') {
    $appearanceMode = (string)($_POST['appearance_mode'] ?? 'default');

    if (!in_array($appearanceMode, ['default', 'dark', 'light'], true)) {
      $appearanceMode = 'default';
    }

    $stmt = $mysqli->prepare("UPDATE users SET appearance_mode = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param("si", $appearanceMode, $u['id']);
    $stmt->execute();
    $stmt->close();

    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
      $_SESSION['user']['appearance_mode'] = $appearanceMode;
    }

    $_SESSION['flash_success'] = "Appearance updated.";
    header("Location: " . $bp . "/profile.php?tab=appearance");
    exit;
  }

  if ($tab === 'bio') {
    $displayName  = trim((string)($_POST['display_name'] ?? ''));
    $bioText      = trim((string)($_POST['bio'] ?? ''));
    $favoriteDeck = trim((string)($_POST['favorite_deck'] ?? ''));
    $tagline      = trim((string)($_POST['tagline'] ?? ''));

    $stmt = $mysqli->prepare("
      UPDATE users
      SET display_name = ?, bio = ?, favorite_deck = ?, tagline = ?
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("ssssi", $displayName, $bioText, $favoriteDeck, $tagline, $u['id']);
    $stmt->execute();
    $stmt->close();

    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
      $_SESSION['user']['display_name'] = $displayName;
      $_SESSION['user']['bio'] = $bioText;
      $_SESSION['user']['favorite_deck'] = $favoriteDeck;
      $_SESSION['user']['tagline'] = $tagline;
    }

    $_SESSION['flash_success'] = "Profile updated.";
    header("Location: " . $bp . "/profile.php?tab=bio");
    exit;
  }

  if ($tab === 'avatar' && isset($_FILES['avatar_file'])) {
    if (!empty($_FILES['avatar_file']['name']) && (int)$_FILES['avatar_file']['error'] === 0) {
      $uploadDir = __DIR__ . "/../uploads/avatars/";

      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      $originalName = (string)$_FILES['avatar_file']['name'];
      $tmpName      = (string)$_FILES['avatar_file']['tmp_name'];
      $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

      $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!in_array($ext, $allowedExts, true)) {
        $_SESSION['flash_error'] = "Invalid avatar file type.";
        header("Location: " . $bp . "/profile.php?tab=avatar");
        exit;
      }

      $fileName = "avatar_" . (int)$u['id'] . "_" . time() . "." . $ext;
      $fullPath = $uploadDir . $fileName;
      $dbPath   = "uploads/avatars/" . $fileName;

      if (move_uploaded_file($tmpName, $fullPath)) {
        $stmt = $mysqli->prepare("UPDATE users SET avatar_path = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("si", $dbPath, $u['id']);
        $stmt->execute();
        $stmt->close();

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
          $_SESSION['user']['avatar_path'] = $dbPath;
        }

        $_SESSION['flash_success'] = "Avatar updated.";
      } else {
        $_SESSION['flash_error'] = "Failed to upload avatar.";
      }
    } else {
      $_SESSION['flash_error'] = "Choose an image first.";
    }

    header("Location: " . $bp . "/profile.php?tab=avatar");
    exit;
  }

  if ($tab === 'account') {
    $newUsername = trim((string)($_POST['username'] ?? ''));
    $newEmail    = trim((string)($_POST['email'] ?? ''));

    $stmt = $mysqli->prepare("
      UPDATE users
      SET username = ?, email = ?
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("ssi", $newUsername, $newEmail, $u['id']);
    $stmt->execute();
    $stmt->close();

    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
      $_SESSION['user']['username'] = $newUsername;
      $_SESSION['user']['email'] = $newEmail;
    }

    $_SESSION['flash_success'] = "Account updated.";
    header("Location: " . $bp . "/profile.php?tab=account");
    exit;
  }
}