<?php
/*
FILE OVERVIEW:
- backend\require-user.php
- Frontend pages ke liye login guard: unauthenticated users ko login page par redirect karta hai.
*/

require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: ../backend/user-login.php');
    exit;
}
