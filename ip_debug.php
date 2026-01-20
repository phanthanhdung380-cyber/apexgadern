<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: text/plain');

echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n";
echo "CF-Connecting-IP: " . ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '-') . "\n";
echo "X-Forwarded-For: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '-') . "\n";
