<?php
// api/payments/paymongo-topup-success.php

session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/paymongo.php';

$bp = base_path();

if (!is_logged_in()) {
  flash_set('err', 'Please sign in first.');
  redirect($bp . '/index.php');
}

$u = current_user();
$userId  = (int)($u['id'] ?? 0);
$topupId = (int)($_SESSION['pending_topup_id'] ?? 0);

if ($userId <= 0 || $topupId <= 0) {
  flash_set('err', 'No pending top-up was found.');
  redirect($bp . '/shop.php?tab=credits');
}

$stmt = $mysqli->prepare("
  SELECT id, user_id, pack_name, total_credits, status, paymongo_checkout_id
  FROM credit_topups
  WHERE id = ? AND user_id = ?
  LIMIT 1
");
$stmt->bind_param("ii", $topupId, $userId);
$stmt->execute();
$topup = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$topup) {
  unset($_SESSION['pending_topup_id']);
  flash_set('err', 'Top-up record not found.');
  redirect($bp . '/shop.php?tab=credits');
}

if ((string)$topup['status'] === 'paid') {
  unset($_SESSION['pending_topup_id']);
  load_user_into_session($mysqli, $userId);
  flash_set('msg', 'Your Zeny was already added.');
  redirect($bp . '/shop.php?tab=credits');
}

$checkoutId = trim((string)($topup['paymongo_checkout_id'] ?? ''));
if ($checkoutId === '') {
  unset($_SESSION['pending_topup_id']);
  flash_set('err', 'Missing PayMongo checkout reference.');
  redirect($bp . '/shop.php?tab=credits');
}

$ch = curl_init(PAYMONGO_API_BASE . '/checkout_sessions/' . rawurlencode($checkoutId));

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
  ],
]);

$responseBody = curl_exec($ch);
$httpCode     = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr      = curl_error($ch);
curl_close($ch);

if ($responseBody === false || $curlErr) {
  flash_set('err', 'Could not verify PayMongo payment yet.');
  redirect($bp . '/shop.php?tab=credits');
}

$data = json_decode($responseBody, true);

if ($httpCode < 200 || $httpCode >= 300 || empty($data['data']['id'])) {
  $errorMessage = 'Could not verify the checkout session.';
  if (!empty($data['errors'][0]['detail'])) {
    $errorMessage = (string)$data['errors'][0]['detail'];
  }

  flash_set('err', $errorMessage);
  redirect($bp . '/shop.php?tab=credits');
}

$attributes = $data['data']['attributes'] ?? [];
$payments   = $attributes['payments'] ?? [];

if (empty($payments) || !is_array($payments)) {
  flash_set('err', 'Payment is not confirmed yet.');
  redirect($bp . '/shop.php?tab=credits');
}

$payment         = $payments[0];
$paymentId       = (string)($payment['id'] ?? '');
$paymentAttrs    = $payment['attributes'] ?? [];
$paymentIntentId = (string)($paymentAttrs['payment_intent_id'] ?? '');
$creditsToAdd    = (int)($topup['total_credits'] ?? 0);

$mysqli->begin_transaction();

try {

  $stmt = $mysqli->prepare("
    SELECT status
    FROM credit_topups
    WHERE id = ?
    FOR UPDATE
  ");
  $stmt->bind_param("i", $topupId);
  $stmt->execute();
  $lockedTopup = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$lockedTopup) {
    throw new RuntimeException('Top-up record disappeared.');
  }

  if ((string)$lockedTopup['status'] !== 'paid') {

    $stmt = $mysqli->prepare("
      SELECT credits
      FROM users
      WHERE id = ?
      LIMIT 1
      FOR UPDATE
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$userRow) {
      throw new RuntimeException('User record not found.');
    }

    $oldCredits = (int)($userRow['credits'] ?? 0);
    $newCredits = $oldCredits + $creditsToAdd;

    $stmt = $mysqli->prepare("
      UPDATE users
      SET credits = credits + ?
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("ii", $creditsToAdd, $userId);
    $stmt->execute();
    $stmt->close();

    /* ---------------- AUDIT LOG ---------------- */

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $meta = json_encode([
      'credits_before' => $oldCredits,
      'credits_added'  => $creditsToAdd,
      'credits_after'  => $newCredits,
      'pack'           => $topup['pack_name'],
      'payment_id'     => $paymentId
    ], JSON_UNESCAPED_SLASHES);

    $stmt = $mysqli->prepare("
      INSERT INTO audit_logs
        (actor_user_id, action, target_type, target_id, metadata_json, ip_address, created_at)
      VALUES
        (?, 'zeny_topup', 'credit_topup', ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
      "iiss",
      $userId,
      $topupId,
      $meta,
      $ip
    );

    $stmt->execute();
    $stmt->close();

    /* ------------------------------------------- */

    $stmt = $mysqli->prepare("
      UPDATE credit_topups
      SET
        status = 'paid',
        paymongo_payment_id = ?,
        paymongo_payment_intent_id = ?,
        paid_at = NOW(),
        credited_at = NOW()
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("ssi", $paymentId, $paymentIntentId, $topupId);
    $stmt->execute();
    $stmt->close();

    $title = 'Zeny Added';
    $body  = 'Your wallet was credited with ' . number_format($creditsToAdd) . ' Zeny from ' . (string)$topup['pack_name'] . '.';
    $link  = $bp . '/shop.php?tab=credits';

    $stmt = $mysqli->prepare("
      INSERT INTO dashboard_notifications
        (user_id, type, title, body, link_url, is_read, created_at)
      VALUES
        (?, 'credit_update', ?, ?, ?, 0, NOW())
    ");
    $stmt->bind_param("isss", $userId, $title, $body, $link);
    $stmt->execute();
    $stmt->close();
  }

  $mysqli->commit();
  load_user_into_session($mysqli, $userId);

} catch (Throwable $e) {

  $mysqli->rollback();
  flash_set('err', 'Top-up verification failed. Please try again or contact admin.');
  redirect($bp . '/shop.php?tab=credits');
}

unset($_SESSION['pending_topup_id']);
flash_set('msg', number_format($creditsToAdd) . ' Zeny has been added to your wallet.');
redirect($bp . '/shop.php?tab=credits');