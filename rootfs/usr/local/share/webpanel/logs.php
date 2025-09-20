<?php
require_once 'auth.php';
require_once 'functions.php';
requireAuth();

$log_files = [
    'tor-server' => '/data/logs/tor/server.log',
    'tor-bridge' => '/data/logs/tor/bridge.log',
    'tor-relay' => '/data/logs/tor/relay.log',
    'unbound' => '/data/logs/unbound/unbound.log',
    'privoxy' => '/data/logs/privoxy/privoxy.log',
    'nginx-access' => '/data/logs/nginx/access.log',
    'nginx-error' => '/data/logs/nginx/error.log',
    'entrypoint' => '/data/logs/entrypoint.log'
];

$current_log = $_GET['log'] ?? 'tor-server';
$lines = (int)($_GET['lines'] ?? 100);

$log_content = '';
if (isset($log_files[$current_log])) {
    $log_content = getLogTail($log_files[$current_log], $lines);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Tor Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="10">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧅 Tor Admin Panel - Logs</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
            </div>
        </div>

        <div class="nav">
            <a href="index.php">Dashboard</a>
            <a href="config.php">Configuration</a>
            <a href="hidden.php">Hidden Services</a>
            <a href="logs.php" class="active">Logs</a>
            <a href="tokens.php">API Tokens</a>
        </div>

        <div class="tabs">
            <?php foreach ($log_files as $key => $file): ?>
                <div class="tab <?php echo $key === $current_log ? 'active' : ''; ?>" 
                     onclick="location.href='logs.php?log=<?php echo $key; ?>&lines=<?php echo $lines; ?>'">
                    <?php echo ucfirst(str_replace('-', ' ', $key)); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="config-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>Viewing: <?php echo ucfirst(str_replace('-', ' ', $current_log)); ?> Log</h3>
                <div>
                    <label for="lines" style="margin-right: 10px;">Lines:</label>
                    <select id="lines" onchange="location.href='logs.php?log=<?php echo $current_log; ?>&lines=' + this.value">
                        <option value="50" <?php echo $lines === 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $lines === 100 ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo $lines === 200 ? 'selected' : ''; ?>>200</option>
                        <option value="500" <?php echo $lines === 500 ? 'selected' : ''; ?>>500</option>
                    </select>
                </div>
            </div>
            
            <p><strong>File:</strong> <?php echo $log_files[$current_log]; ?></p>
            
            <div class="log-container">
                <?php echo htmlspecialchars($log_content ?: 'Log file is empty or not found'); ?>
            </div>
            
            <div style="margin-top: 15px;">
                <button onclick="location.reload()" class="btn-small">Refresh</button>
                <button onclick="location.href='logs.php?log=<?php echo $current_log; ?>&lines=<?php echo $lines; ?>'" 
                        class="btn-small">Auto-refresh (10s)</button>
            </div>
        </div>

        <div class="info">
            <strong>Log Monitoring:</strong><br>
            • Logs auto-refresh every 10 seconds<br>
            • Tor logs show connection and circuit information<br>
            • Nginx logs show web access and errors<br>
            • Entrypoint log shows container initialization<br>
            • Check logs if services fail to start
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of log
        const logContainer = document.querySelector('.log-container');
        if (logContainer) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    </script>
</body>
</html>