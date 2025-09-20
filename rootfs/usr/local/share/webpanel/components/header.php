<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Tor Admin Panel'; ?></title>
    <link rel="stylesheet" href="style.css">
    <?php if (isset($auto_refresh)): ?>
    <meta http-equiv="refresh" content="<?php echo $auto_refresh; ?>">
    <?php endif; ?>
</head>
<body>
    <div class="main-wrapper">
        <div class="container">
            <div class="header">
                <h1>🧅 <?php echo $page_title ?? 'Tor Admin Panel'; ?></h1>
                <div class="header-actions">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout=1" class="logout-btn">Logout</a>
                </div>
            </div>