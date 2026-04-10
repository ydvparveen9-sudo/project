<?php
/*
FILE OVERVIEW:
- backend\auth.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Session start: login state (current user) globally available rakhne ke liye.
// Session already active hai to dubara start na karo - avoid "session already active" warning.
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Users loader: users.json se user list read karke array return karta hai.
function load_users(): array
{
    $file = __DIR__ . '/../data/users.json';
    if (!file_exists($file)) {
        return [];
    }

    $data = file_get_contents($file);
    $decoded = json_decode((string)$data, true);

    return is_array($decoded) ? $decoded : [];
}

// User finder: email match karke specific user record deta hai.
function find_user(string $email): ?array
{
    $users = load_users();

    foreach ($users as $user) {
        if ((string)($user['email'] ?? '') === $email) {
            return $user;
        }
    }

    return null;
}

// Login flow: user verify + optional role check + password hash verify + session set.
function login_user(string $email, string $password, ?string $expectedRole = null): bool
{
    $user = find_user($email);

    if (!$user) {
        return false;
    }

    if ($expectedRole !== null && (string)($user['role'] ?? '') !== $expectedRole) {
        return false;
    }

    if (password_verify($password, (string)($user['password_hash'] ?? ''))) {
        $_SESSION['user'] = $user;
        return true;
    }

    return false;
}

// Current user helper: active session user safely return karta hai.
function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Logout helper: current session destroy karta hai.
function logout_user(): void
{
    session_destroy();
}



