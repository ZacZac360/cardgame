<?php
// api/payments/paymongo-topup-create.php

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
if (!$u) {
  flash_set('err', 'Please sign in first.');
  redirect($bp . '/index.php');
}

if ((int)($u['is_guest'] ?? 0) === 1) {
  flash_set('err', 'Guest accounts cannot buy Zeny.');
  redirect($bp . '/guest_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  flash_set('err', 'Invalid request.');
  redirect($bp . '/shop.php?tab=credits');
}

if (!defined('PAYMONGO_SECRET_KEY') || !PAYMONGO_SECRET_KEY) {
  flash_set('err', 'PayMongo test key is missing.');
  redirect($bp . '/shop.php?tab=credits');
}

$packCode = trim((string)($_POST['pack_code'] ?? ''));

$creditPacks = [
  'starter_50' => [
    'code'          => 'starter_50',
    'name'          => 'Starter Cache',
    'price_php'     => 50.00,
    'credits'       => 250,
    'bonus_credits' => 0,
  ],
  'duel_100' => [
    'code'          => 'duel_100',
    'name'          => 'Duel Stack',
    'price_php'     => 100.00,
    'credits'       => 500,
    'bonus_credits' => 50,
  ],
  'arena_200' => [
    'code'          => 'arena_200',
    'name'          => 'Arena Vault',
    'price_php'     => 200.00,
    'credits'       => 1000,
    'bonus_credits' => 150,
  ],
];

if (!isset($creditPacks[$packCode])) {
  flash_set('err', 'Invalid top-up pack.');
  redirect($bp . '/shop.php?tab=credits');
}

$pack = $creditPacks[$packCode];
$userId = (int)$u['id'];

$amountPhp    = (float)$pack['price_php'];
$baseCredits  = (int)$pack['credits'];
$bonusCredits = (int)$pack['bonus_credits'];
$totalCredits = $baseCredits + $bonusCredits;
$amountCentavos = (int)round($amountPhp * 100);

$referenceNumber = 'LOGIA-TOPUP-' . $userId . '-' . time();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $scheme . '://' . $_SERVER['HTTP_HOST'];

$successUrl = $host . $bp . '/api/payments/paymongo-topup-success.php';
$cancelUrl  = $host . $bp . '/shop.php?tab=credits&cancel=1';

$displayName = trim((string)($u['display_name'] ?? ''));
if ($displayName === '') {
  $displayName = (string)($u['username'] ?? 'Logia Player');
}

$email = trim((string)($u['email'] ?? ''));

$stmt = $mysqli->prepare("
  INSERT INTO credit_topups
    (user_id, pack_code, pack_name, amount_php, credits_amount, bonus_credits, total_credits, reference_number, status)
  VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param(
  "issdiiis",
  $userId,
  $pack['code'],
  $pack['name'],
  $amountPhp,
  $baseCredits,
  $bonusCredits,
  $totalCredits,
  $referenceNumber
);
$stmt->execute();
$topupId = (int)$stmt->insert_id;
$stmt->close();

$_SESSION['pending_topup_id'] = $topupId;

$payload = [
  'data' => [
    'attributes' => [
      'billing' => [
        'name'  => $displayName,
        'email' => $email !== '' ? $email : null,
        'phone' => null,
      ],
      'cancel_url' => $cancelUrl,
      'success_url' => $successUrl,
      'description' => 'Logia Zeny Top-Up',
      'payment_method_types' => [
        'card',
        'gcash',
      ],
      'line_items' => [
        [
          'currency'    => 'PHP',
          'amount'      => $amountCentavos,
          'name'        => $pack['name'],
          'quantity'    => 1,
          'description' => number_format($totalCredits) . ' Zeny Top-Up',
        ],
      ],
      'merchant'           => 'Logia',
      'reference_number'   => $referenceNumber,
      'send_email_receipt' => false,
      'show_description'   => true,
      'show_line_items'    => true,
      'metadata' => [
        'topup_id'      => (string)$topupId,
        'user_id'       => (string)$userId,
        'pack_code'     => $pack['code'],
        'credits_total' => (string)$totalCredits,
      ],
    ],
  ],
];

$ch = curl_init(PAYMONGO_API_BASE . '/checkout_sessions');

curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
  ],
  CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$responseBody = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($responseBody === false || $curlErr) {
  $stmt = $mysqli->prepare("UPDATE credit_topups SET status = 'failed' WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $topupId);
  $stmt->execute();
  $stmt->close();

  flash_set('err', 'PayMongo did not respond. Check your test key and cURL setup.');
  redirect($bp . '/shop.php?tab=credits');
}

$data = json_decode($responseBody, true);

$checkoutId  = (string)($data['data']['id'] ?? '');
$checkoutUrl = (string)($data['data']['attributes']['checkout_url'] ?? '');

if ($httpCode >= 200 && $httpCode < 300 && $checkoutId !== '' && $checkoutUrl !== '') {
  $stmt = $mysqli->prepare("
    UPDATE credit_topups
    SET paymongo_checkout_id = ?
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("si", $checkoutId, $topupId);
  $stmt->execute();
  $stmt->close();

  header('Location: ' . $checkoutUrl);
  exit;
}

$stmt = $mysqli->prepare("UPDATE credit_topups SET status = 'failed' WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $topupId);
$stmt->execute();
$stmt->close();

$errorMessage = 'Failed to create PayMongo checkout session.';
if (!empty($data['errors'][0]['detail'])) {
  $errorMessage = (string)$data['errors'][0]['detail'];
}

flash_set('err', $errorMessage);
redirect($bp . '/shop.php?tab=credits');