<?php
// bootstrap.php
// Purpose: Fix Cloudflare real client IP + start session before any output.

// 1) Determine real client IP.
// Cloudflare sends the real client IP in CF-Connecting-IP.
$realIp = null;

if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
    $realIp = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // X-Forwarded-For can be "client, proxy1, proxy2"
    $xffFirst = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    if (filter_var($xffFirst, FILTER_VALIDATE_IP)) {
        $realIp = $xffFirst;
    }
}

// 2) Overwrite REMOTE_ADDR so any code using it (including tracking/logging) sees the real client IP.
if ($realIp) {
    $_SERVER['REMOTE_ADDR'] = $realIp;
}

// 3) Start session ONCE, before any output.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
