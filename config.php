<?php
// ---- Edit these for your server ----
const DB_HOST = 'localhost';
const DB_NAME = 'lambacademy_db';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = 'http://localhost/schoolapp';   // e.g. '/school' if the site lives in a sub-folder, otherwise leave empty
// ------------------------------------
header('X-Frame-Options: SAMEORIGIN'); header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: same-origin');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
date_default_timezone_set('Africa/Lagos');
require_once __DIR__ . '/includes/functions.php';
