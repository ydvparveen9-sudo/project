<?php
/*
FILE OVERVIEW:
- backend\admin-login.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Auth helper load: login/session utility functions yahin se aate hain.
require_once __DIR__ . '/auth.php';

// Guard: agar admin already logged in hai to direct dashboard pe bhejo.
if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'admin') {
    header('Location: admin-dashboard.php');
    exit;
}

// UI error state: invalid input/credentials message store karne ke liye.
$error = '';

// Store referrer page from query parameter: jab admin login page visit kare tab return URL se referrer save karo.
if (!empty($_GET['return']) && empty($_SESSION['admin_login_referrer'])) {
	$returnUrl = (string)$_GET['return'];
	// Validate URL to prevent open redirect abuse
	if (filter_var($returnUrl, FILTER_VALIDATE_URL)) {
		$_SESSION['admin_login_referrer'] = $returnUrl;
	}
}

// Fallback: agar GET parameter nhi h tab HTTP_REFERER try karo.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_SESSION['admin_login_referrer'])) {
	if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin-login.php') === false) {
		$_SESSION['admin_login_referrer'] = $_SERVER['HTTP_REFERER'];
	}
}

// POST controller: login form submit hone par credentials validate karta hai.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // Basic required validation: dono fields mandatory hain.
    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
		// Agar pehle website user logged-in tha to admin session se pehle uska snapshot save karo.
		if (isset($_SESSION['user']) && is_array($_SESSION['user']) && (string)($_SESSION['user']['role'] ?? '') !== 'admin') {
			$_SESSION['frontend_user_backup'] = $_SESSION['user'];
		}

		// Auth attempt: sirf admin role ke liye login verify.
        $authUser = login_user($email, $password, 'admin');
        if ($authUser) {
			// Success redirect: dashboard open karo.
            header('Location: admin-dashboard.php');
            exit;
        }

		if (isset($_SESSION['frontend_user_backup']) && !isset($_SESSION['user'])) {
			unset($_SESSION['frontend_user_backup']);
		}

		// Failed auth: user ko generic invalid credentials message.
        $error = 'Invalid admin credentials.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #f8f9fa 0%, #e8f7f6 100%);
            display: flex;
            align-items: center;
        }

        .login-card {
            border: 0;
            border-radius: 14px;
        }

        .custom-bg {
            background-color: #2ec;
            border: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card login-card shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h3 class="mb-1">Admin Login</h3>
                    <p class="text-muted small mb-4">Use admin credentials to open dashboard.</p>

                    <?php if ($error !== ''): ?>
						<!-- Server-side validation/auth error ko safe output me dikhaya jata hai. -->
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

					<!-- Admin login form: email + password submit karta hai backend controller ko. -->
                    <form method="post" action="admin-login.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn custom-bg text-white w-100">Login</button>
                    </form>

                    <a href="../frontend/index.php" class="btn btn-link text-decoration-none px-0 mt-3">Back to website</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>



