<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = "";
$fullname = "";
$email = "";
$username = "";

$usersFile = __DIR__ . '/users.json';

function loadUsers($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

function saveUsers($file, $users) {
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim(htmlspecialchars($_POST['fullname'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($fullname) && empty($email) && empty($username) && empty($password) && empty($confirm_password)) {
        $errors[] = "All fields are required.";
    } else {
        if (empty($fullname)) {
            $errors[] = "Full name is required.";
        }
        if (empty($email)) {
            $errors[] = "Email address is required.";
        }
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        if (empty($confirm_password)) {
            $errors[] = "Password confirmation is required.";
        }
    }

    if (!empty($fullname) && (strlen($fullname) < 2 || strlen($fullname) > 50)) {
        $errors[] = "Full name must be between 2 and 50 characters.";
    }
    if (!empty($username) && (strlen($username) < 3 || strlen($username) > 20)) {
        $errors[] = "Username must be between 3 and 20 characters.";
    }
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }


    if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $users = loadUsers($usersFile);
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $errors[] = "Username already taken.";
                break;
            }
            if ($user['email'] === $email) {
                $errors[] = "Email already registered.";
                break;
            }
        }
    }

    if (empty($errors)) {
        $users = loadUsers($usersFile);
        $newUser = [
            'id' => uniqid('user_'),
            'fullname' => $fullname,
            'email' => $email,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $users[] = $newUser;
        saveUsers($usersFile, $users);
        $success = "Account created successfully! You can now log in.";
        $fullname = "";
        $email = "";
        $username = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – Tournament Manager</title>
    <meta name="description" content="Create an account to access the Sports Registration and Tournament Management System.">
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
            <h1>Create Account</h1>
            <p class="auth-subtitle">Join the Tournament Manager platform</p>
        </div>

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

        <!-- Success Message -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <!-- Signup Form (POST Method) -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" value="<?php echo $fullname; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo $email; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo $username; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn-auth" id="signup-btn">
                <span>Create Account</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
</div>

</body>
</html>
