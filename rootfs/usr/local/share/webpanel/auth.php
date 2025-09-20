<?php
session_start();

$admin_user = $_ENV['TOR_ADMIN_USER'] ?? 'admin';
$admin_pass = $_ENV['TOR_ADMIN_PASS'] ?? 'torpass123';

function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

function authenticate($username, $password) {
    global $admin_user, $admin_pass;
    return $username === $admin_user && $password === $admin_pass;
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    logout();
}
?>