<?php
// includes/db.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$DB_HOST = "127.0.0.1";
$DB_USER = "root";
$DB_PASS = "Pokemon2003";
$DB_NAME = "cardgame";
$DB_PORT = 3306;

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
$mysqli->set_charset("utf8mb4");