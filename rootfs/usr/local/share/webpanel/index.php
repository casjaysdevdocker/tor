<?php
require_once 'auth.php';
require_once 'functions.php';
requireAuth();

$services = [
    'tor-bridge' => 'Tor Bridge',
    'tor-relay' => 'Tor Relay', 
    'tor-server' => 'Tor Server',
    'unbound' => 'DNS Resolver',
    'privoxy' => 'HTTP Proxy',
    'nginx' => 'Web Server'
];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $service = $_POST['service'] ?? '';
    
    if (in_array($service, array_keys($services))) {
        switch ($action) {
            case 'start':
                if (startService($service)) {
                    $message = "Started $services[$service]";
                    $messageType = 'success';
                } else {
                    $message = "Failed to start $services[$service]";
                    $messageType = 'error';
                }
                break;
                
            case 'stop':
                if (stopService($service)) {
                    $message = "Stopped $services[$service]";
                    $messageType = 'success';
                } else {
                    $message = "Failed to stop $services[$service]";
                    $messageType = 'error';
                }
                break;
                
            case 'restart':
                if (restartService($service)) {
                    $message = "Restarted $services[$service]";
                    $messageType = 'success';
                } else {
                    $message = "Failed to restart $services[$service]";
                    $messageType = 'error';
                }
                break;
        }
    }
}

$stats = getSystemStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tor Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧅 Tor Admin Panel</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
            </div>
        </div>

        <div class="nav">
            <a href="index.php" class="active">Dashboard</a>
            <a href="config.php">Configuration</a>
            <a href="hidden.php">Hidden Services</a>
            <a href="logs.php">Logs</a>
            <a href="tokens.php">API Tokens</a>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['uptime']; ?></div>
                <div>System Uptime</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['memory']; ?></div>
                <div>Memory Usage</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['disk']; ?></div>
                <div>Disk Usage</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['load']; ?></div>
                <div>Load Average</div>
            </div>
        </div>

        <h2>Service Management</h2>
        <div class="service-grid">
            <?php foreach ($services as $service => $name): ?>
                <?php $isRunning = getServiceStatus($service); ?>
                <div class="service-card">
                    <h3><?php echo $name; ?></h3>
                    <div class="service-status <?php echo $isRunning ? 'status-running' : 'status-stopped'; ?>">
                        <?php echo $isRunning ? 'RUNNING' : 'STOPPED'; ?>
                    </div>
                    <p>PID: <?php echo getServicePid($service); ?></p>
                    
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="service" value="<?php echo $service; ?>">
                        <button type="submit" name="action" value="start" class="btn-small" 
                                <?php echo $isRunning ? 'disabled' : ''; ?>>Start</button>
                        <button type="submit" name="action" value="stop" class="btn-small"
                                <?php echo !$isRunning ? 'disabled' : ''; ?>>Stop</button>
                        <button type="submit" name="action" value="restart" class="btn-small">Restart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="info">
            <strong>Quick Info:</strong><br>
            • SOCKS Proxy: localhost:9050<br>
            • HTTP Proxy: localhost:8118<br>
            • DNS Resolver: localhost:9053<br>
            • Control Port: localhost:9051<br>
            • Web Interface: localhost:80
        </div>
    </div>

    <script>
        // Auto-refresh page every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>