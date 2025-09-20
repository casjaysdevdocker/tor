<?php
require_once 'auth.php';
require_once 'functions.php';
requireAuth();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $port_mapping = sanitizeInput($_POST['port_mapping'] ?? '');
        
        if (empty($name) || empty($port_mapping)) {
            $message = 'Name and port mapping are required';
            $messageType = 'error';
        } elseif (!validateServiceName($name)) {
            $message = 'Invalid service name. Use only letters, numbers, underscores, and hyphens';
            $messageType = 'error';
        } elseif (!validatePortMapping($port_mapping)) {
            $message = 'Invalid port mapping format. Use: external_port internal_ip:internal_port';
            $messageType = 'error';
        } else {
            if (createHiddenService($name, $port_mapping)) {
                $message = "Hidden service '$name' created successfully. Restart tor-server to activate.";
                $messageType = 'success';
            } else {
                $message = "Failed to create hidden service '$name'";
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $name = sanitizeInput($_POST['name'] ?? '');
        
        if (deleteHiddenService($name)) {
            $message = "Hidden service '$name' deleted successfully. Restart tor-server to apply.";
            $messageType = 'success';
        } else {
            $message = "Failed to delete hidden service '$name'";
            $messageType = 'error';
        }
    } elseif ($action === 'restart-tor') {
        if (restartService('tor-server')) {
            $message = "Tor server restarted successfully";
            $messageType = 'success';
        } else {
            $message = "Failed to restart Tor server";
            $messageType = 'error';
        }
    }
}

$hidden_services = getHiddenServices();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hidden Services - Tor Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧅 Tor Admin Panel - Hidden Services</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
            </div>
        </div>

        <div class="nav">
            <a href="index.php">Dashboard</a>
            <a href="config.php">Configuration</a>
            <a href="hidden.php" class="active">Hidden Services</a>
            <a href="logs.php">Logs</a>
            <a href="tokens.php">API Tokens</a>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="config-section">
            <h2>Create New Hidden Service</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="name">Service Name:</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="e.g., myapp, blog, api" pattern="[a-zA-Z0-9_-]+">
                </div>
                <div class="form-group">
                    <label for="port_mapping">Port Mapping:</label>
                    <input type="text" id="port_mapping" name="port_mapping" required 
                           placeholder="80 127.0.0.1:8080" pattern="\d+\s+\d+\.\d+\.\d+\.\d+:\d+">
                    <small>Format: external_port internal_ip:internal_port</small>
                </div>
                <button type="submit">Create Hidden Service</button>
            </form>
        </div>

        <h2>Existing Hidden Services</h2>
        
        <?php if (empty($hidden_services)): ?>
            <div class="info">
                No hidden services configured yet. Create one above to get started.
            </div>
        <?php else: ?>
            <?php foreach ($hidden_services as $service): ?>
                <div class="hidden-service">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <div class="onion-address">
                        <strong>Onion Address:</strong><br>
                        <?php echo htmlspecialchars($service['hostname']); ?>
                    </div>
                    <p><strong>Data Directory:</strong> <?php echo htmlspecialchars($service['path']); ?></p>
                    
                    <form method="POST" style="margin-top: 10px;" 
                          onsubmit="return confirm('Are you sure you want to delete this hidden service?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($service['name']); ?>">
                        <button type="submit" class="btn-small" 
                                style="background: #d63031;">Delete Service</button>
                    </form>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px;">
                <form method="POST">
                    <input type="hidden" name="action" value="restart-tor">
                    <button type="submit" class="btn-small">Restart Tor Server</button>
                </form>
                <small>Restart Tor server to activate new services or apply deletions</small>
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>Hidden Service Management:</strong><br>
            • Hidden services allow you to host websites accessible only via Tor<br>
            • Each service gets a unique .onion address<br>
            • Port mapping routes external .onion traffic to internal services<br>
            • Services are stored in /data/tor/server/services/<br>
            • Restart Tor server after creating/deleting services
        </div>
    </div>
</body>
</html>