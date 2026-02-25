<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?status=session_expired");
    exit();
}

$user_fullname = $_SESSION['fullname'];
$user_username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Tournament Manager</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="dash-wrapper">
    <!-- Header -->
    <header class="dash-header">
        <div class="dash-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            <span>Tournament Manager</span>
        </div>
        <div class="dash-user">
            <div class="avatar"><?php echo strtoupper(substr($user_username, 0, 1)); ?></div>
            <span><?php echo $user_fullname; ?></span>
            <a href="logout.php" class="btn-logout" id="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dash-content">
        <div class="welcome-card">
            <h1>Welcome to the Dashboard, <?php echo $user_fullname; ?>!</h1>
            <p>This is your personal tournament management hub. Currently, all management features are being prepared.</p>
            <div class="quick-actions">
                <p>Use the header to log out or wait for upcoming updates.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
