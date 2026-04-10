<?php
/*
FILE OVERVIEW:
- backend\logout.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Auth helper load: logout_user function yahin se aata hai.
require_once __DIR__ . '/auth.php';

// Admin logout special case: agar website user backup available ho to usko restore karo.
$currentRole = (string)($_SESSION['user']['role'] ?? '');
$frontendBackup = $_SESSION['frontend_user_backup'] ?? null;
$referrerPage = !empty($_SESSION['admin_login_referrer']) ? $_SESSION['admin_login_referrer'] : null;

if ($currentRole === 'admin' && is_array($frontendBackup) && (string)($frontendBackup['role'] ?? '') !== 'admin') {
	$_SESSION['user'] = $frontendBackup;
	unset($_SESSION['frontend_user_backup']);
	header('Location: ../frontend/index.php');
	exit;
}

// Logout flow start: session user remove + session cleanup.
logout_user();

$_SESSION = [];

// Session cookie invalidate: browser side session token bhi expire karna.
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Final cleanup + redirect ke stored referrer page.
session_destroy();

if ($currentRole === 'admin' && !empty($referrerPage)) {
	header('Location: ' . $referrerPage);
	exit;
}

// Default redirect: homepage.
header('Location: ../frontend/index.php');
exit;



