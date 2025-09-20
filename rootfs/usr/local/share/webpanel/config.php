<?php
require_once 'auth.php';
require_once 'functions.php';
requireAuth();

$message = '';
$messageType = '';

$config_files = [
    'tor-server' => '/config/tor/server/server.conf',
    'tor-bridge' => '/config/tor/bridge/bridge.conf', 
    'tor-relay' => '/config/tor/relay/relay.conf',
    'unbound' => '/config/unbound/unbound.conf',
    'privoxy' => '/config/privoxy/config',
    'nginx' => '/config/nginx/nginx.conf'
];

$current_config = $_GET['config'] ?? 'tor-server';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config_name = $_POST['config'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if (isset($config_files[$config_name])) {
        $config_file = $config_files[$config_name];
        $config_dir = dirname($config_file);
        
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }
        
        if (saveConfigContent($config_file, $content)) {
            $message = "Configuration saved successfully for $config_name";
            $messageType = 'success';
            
            // Restart the service after config change
            if (in_array($config_name, ['tor-server', 'tor-bridge', 'tor-relay'])) {
                restartService($config_name);
                $message .= " and service restarted";
            }
        } else {
            $message = "Failed to save configuration for $config_name";
            $messageType = 'error';
        }
    }
}

$config_content = getConfigContent($config_files[$current_config]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - Tor Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧅 Tor Admin Panel - Configuration</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
            </div>
        </div>

        <div class="nav">
            <a href="index.php">Dashboard</a>
            <a href="config.php" class="active">Configuration</a>
            <a href="hidden.php">Hidden Services</a>
            <a href="logs.php">Logs</a>
            <a href="tokens.php">API Tokens</a>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <?php foreach ($config_files as $key => $file): ?>
                <div class="tab <?php echo $key === $current_config ? 'active' : ''; ?>" 
                     onclick="location.href='config.php?config=<?php echo $key; ?>'">
                    <?php echo ucfirst(str_replace('-', ' ', $key)); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="config-section">
            <h3>Editing: <?php echo ucfirst(str_replace('-', ' ', $current_config)); ?> Configuration</h3>
            <p><strong>File:</strong> <?php echo $config_files[$current_config]; ?></p>
            
            <form method="POST">
                <input type="hidden" name="config" value="<?php echo $current_config; ?>">
                <div class="form-group">
                    <label for="content">Configuration Content:</label>
                    <textarea name="content" id="content" rows="20" 
                              style="font-family: 'Courier New', monospace; font-size: 14px;"
                              required><?php echo htmlspecialchars($config_content); ?></textarea>
                </div>
                <button type="submit">Save Configuration</button>
            </form>
        </div>

        <div class="info">
            <strong>Important Notes:</strong><br>
            • Changes to Tor configurations will automatically restart the affected service<br>
            • Backup your configurations before making changes<br>
            • Invalid configurations may prevent services from starting<br>
            • Check logs if services fail to start after configuration changes
        </div>
    </div>
</body>
</html>