<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$username = "";
$remember_me = false;

$usersFile = __DIR__ . '/users.json';

function loadUsers($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    return [];
}


if (isset($_COOKIE['remember_username'])) {
    $username = htmlspecialchars($_COOKIE['remember_username']);
    $remember_me = true;
}

// GET method: Check if redirected with a status message
$status_msg = "";
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status === 'logged_out') {
        $status_msg = "You have been logged out successfully.";
    } elseif ($status === 'session_expired') {
        $status_msg = "Your session has expired. Please log in again.";
    }
}

// Handle POST request (Login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // ============================
    // VALIDATION 1: Required fields
    // ============================
    if (empty($username) && empty($password)) {
        $errors[] = "All fields are required.";
    } else {
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
    }

    // ============================
    // VALIDATION 2: Minimum length
    // ============================
    if (!empty($username) && strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    // ============================
    // VALIDATION 3: Check credentials against stored users
    // ============================
    if (empty($errors)) {
        $users = loadUsers($usersFile);
        $authenticated = false;

        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $authenticated = true;

                // ============================
                // SESSION: Set session variables
                // ============================
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['login_time'] = time();

                // ============================
                // COOKIE: Remember username if checked
                // ============================
                if ($remember_me) {
                    // Set cookie for 30 days
                    setcookie('remember_username', $username, time() + (30 * 24 * 60 * 60), '/');
                    setcookie('user_theme', 'default', time() + (30 * 24 * 60 * 60), '/');
                } else {
                    // Remove cookie if unchecked
                    setcookie('remember_username', '', time() - 3600, '/');
                }

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            }
        }

        if (!$authenticated) {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In – Tournament Manager</title>
    <meta name="description" content="Log in to the Sports Registration and Tournament Management System.">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-body">

<div class="auth-bg">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
</div>

<div class="auth-container">
    <div class="auth-card">
        <!-- Brand Header -->
        <div class="auth-header">
            <div class="brand-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                    <path d="M2 12h20"></path>
                </svg>
            </div>
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Log in to Tournament Manager</p>
        </div>

        <!-- Status Messages from GET parameter -->
        <?php if (!empty($status_msg)): ?>
            <div class="alert alert-info">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <p><?php echo htmlspecialchars($status_msg); ?></p>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cookie Notice: Show if username was remembered -->
        <?php if (!empty($_COOKIE['remember_username']) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
            <div class="alert alert-cookie">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <p>🍪 Welcome back! Your username was remembered via cookie.</p>
            </div>
        <?php endif; ?>

        <!-- Login Form (POST Method, username pre-filled via Cookie/GET) -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo $username; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox-label" for="remember_me">
                    <input type="checkbox" id="remember_me" name="remember_me" <?php echo $remember_me ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn-auth" id="login-btn">
                <span>Log In</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</div>

</body>
</html>
