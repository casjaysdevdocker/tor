<?php
require_once 'auth.php';
require_once 'functions.php';
require_once 'jwt.php';
requireAuth();

$message = '';
$messageType = '';
$new_token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate') {
        $description = sanitizeInput($_POST['description'] ?? '');
        $username = $_SESSION['username'];
        
        $new_token = generateApiToken($username);
        $message = "New API token generated successfully";
        $messageType = 'success';
        
        // Store token info (in a real app, you'd store this in a database)
        $token_file = "/config/secure/tokens.log";
        $token_info = date('Y-m-d H:i:s') . " - Token generated for $username - $description\n";
        file_put_contents($token_file, $token_info, FILE_APPEND | LOCK_EX);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Tokens - Tor Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧅 Tor Admin Panel - API Tokens</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" style="color: white; margin-left: 20px;">Logout</a>
            </div>
        </div>

        <div class="nav">
            <a href="index.php">Dashboard</a>
            <a href="config.php">Configuration</a>
            <a href="hidden.php">Hidden Services</a>
            <a href="logs.php">Logs</a>
            <a href="tokens.php" class="active">API Tokens</a>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($new_token): ?>
            <div class="config-section">
                <h3>🔑 Your New API Token</h3>
                <div class="onion-address" style="background: #f0f8ff; border: 2px solid #0984e3;">
                    <?php echo htmlspecialchars($new_token); ?>
                </div>
                <div class="error" style="background: #fff3cd; color: #856404; border-left-color: #ffc107;">
                    <strong>Important:</strong> Save this token now! It will not be shown again.
                </div>
            </div>
        <?php endif; ?>

        <div class="config-section">
            <h2>🔐 JWT Secret Key</h2>
            <p>Current JWT secret (auto-generated if TOR_JWT_SECRET not set):</p>
            <div class="onion-address" style="background: #fff3cd; border: 1px solid #ffc107;">
                <small><?php echo htmlspecialchars(SimpleJWT::getSecret()); ?></small>
            </div>
            <p><small>Set TOR_JWT_SECRET environment variable to use a custom secret</small></p>
        </div>

        <div class="config-section">
            <h2>Generate New API Token</h2>
            <form method="POST">
                <input type="hidden" name="action" value="generate">
                <div class="form-group">
                    <label for="description">Token Description:</label>
                    <input type="text" id="description" name="description" required 
                           placeholder="e.g., Production monitoring, Mobile app, External script">
                </div>
                <button type="submit">Generate Token</button>
            </form>
        </div>

        <div class="config-section">
            <h2>📚 API Documentation</h2>
            
            <h3>Authentication</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h4>1. Get a token:</h4>
                <code style="display: block; margin: 10px 0; padding: 10px; background: #1a1a1a; color: #00ff00; border-radius: 5px;">
curl -X POST http://your-server/admin/api.php?action=login \<br>
  -d "username=admin&password=yourpass"
                </code>
                
                <h4>2. Use the token:</h4>
                <code style="display: block; margin: 10px 0; padding: 10px; background: #1a1a1a; color: #00ff00; border-radius: 5px;">
curl -H "Authorization: Bearer YOUR_TOKEN" \<br>
  http://your-server/admin/api.php?action=status
                </code>
            </div>

            <h3>Available Endpoints</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h4>📊 GET /admin/api.php?action=status</h4>
                <p>Get status of all services</p>
                
                <h4>📈 GET /admin/api.php?action=stats</h4>
                <p>Get system statistics</p>
                
                <h4>🧅 GET /admin/api.php?action=hidden-services</h4>
                <p>Get list of hidden services</p>
                
                <h4>⚙️ POST /admin/api.php?action=service-control</h4>
                <p>Control services (start/stop/restart)</p>
                <code style="display: block; margin: 10px 0; padding: 10px; background: #1a1a1a; color: #00ff00; border-radius: 5px;">
POST data: service=tor-server&command=restart
                </code>
                
                <h4>🔑 POST /admin/api.php?action=login</h4>
                <p>Get authentication token</p>
                <code style="display: block; margin: 10px 0; padding: 10px; background: #1a1a1a; color: #00ff00; border-radius: 5px;">
POST data: username=admin&password=yourpass
                </code>
            </div>
        </div>

        <div class="info">
            <strong>Security Notes:</strong><br>
            • Tokens expire after 24 hours<br>
            • Set custom JWT secret via TOR_JWT_SECRET environment variable<br>
            • Store tokens securely - they provide full admin access<br>
            • API supports both Bearer tokens and web session authentication
        </div>
    </div>
</body>
</html>