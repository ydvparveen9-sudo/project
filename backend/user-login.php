<?php
/*
FILE OVERVIEW:
- backend\user-login.php
- Website access ke liye common login page (student/admin dono support).
*/

require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    if ((string)($_SESSION['user']['role'] ?? '') === 'admin') {
        header('Location: admin-dashboard.php');
        exit;
    }

    header('Location: ../frontend/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        if (login_user($email, $password, null)) {
            $role = (string)($_SESSION['user']['role'] ?? 'student');
            if ($role === 'admin') {
                header('Location: admin-dashboard.php');
                exit;
            }

            header('Location: ../frontend/index.php');
            exit;
        }

        $error = 'Invalid login credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #f6f8fa 0%, #e9f5f1 100%);
            display: flex;
            align-items: center;
        }

        .login-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .btn-brand {
            background-color: #0f8a74;
            border: none;
            color: #fff;
        }

        .btn-brand:hover {
            background-color: #0d7865;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card login-card">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="mb-1">Website Login</h3>
                    <p class="text-muted small mb-4">Login ke baad hi website pages accessible honge.</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" action="user-login.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-brand w-100">Login</button>
                    </form>

                    <a href="admin-login.php" class="btn btn-link text-decoration-none px-0 mt-3">Admin login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
