<?php
session_start();

$_SESSION = [];
session_destroy();

if (isset($_COOKIE['remember_username'])) {
}


header("Location: login.php?status=logged_out");
exit();
?>
