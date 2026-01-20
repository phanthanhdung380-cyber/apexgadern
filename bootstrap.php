<?php
// bootstrap.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$realIp = null;

// 1. Cloudflare (best)
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
    $realIp = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// 2. DigitalOcean App Platform
elseif (!empty($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
    $realIp = $_SERVER['HTTP_X_REAL_IP'];
}

// 3. Standard proxy chain
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $xff = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $xffFirst = trim($xff[0]);
    if (filter_var($xffFirst, FILTER_VALIDATE_IP)) {
        $realIp = $xffFirst;
    }
}

if ($realIp) {
    $_SERVER['REMOTE_ADDR'] = $realIp;
}
