<?php
require_once 'auth.php';
require_once 'functions.php';
require_once 'jwt.php';

header('Content-Type: application/json');

// Check for token authentication first
$authenticated = false;
$auth_headers = getallheaders();
$auth_header = $auth_headers['Authorization'] ?? '';

if ($auth_header && strpos($auth_header, 'Bearer ') === 0) {
    $token = substr($auth_header, 7);
    $payload = validateApiToken($token);
    if ($payload) {
        $authenticated = true;
        $_SESSION['api_user'] = $payload['username'];
    }
} elseif (isAuthenticated()) {
    $authenticated = true;
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required. Use Bearer token or login session.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        // Token-based login endpoint
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (authenticate($username, $password)) {
            $token = generateApiToken($username);
            echo json_encode([
                'success' => true,
                'token' => $token,
                'expires_in' => 86400 // 24 hours
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;
        
    case 'status':
        $services = [
            'tor-bridge' => 'Tor Bridge',
            'tor-relay' => 'Tor Relay', 
            'tor-server' => 'Tor Server',
            'unbound' => 'DNS Resolver',
            'privoxy' => 'HTTP Proxy',
            'nginx' => 'Web Server'
        ];
        
        $status = [];
        foreach ($services as $service => $name) {
            $status[$service] = [
                'name' => $name,
                'running' => getServiceStatus($service),
                'pid' => getServicePid($service)
            ];
        }
        
        echo json_encode($status);
        break;
        
    case 'stats':
        echo json_encode(getSystemStats());
        break;
        
    case 'hidden-services':
        echo json_encode(getHiddenServices());
        break;
        
    case 'service-control':
        $service = $_POST['service'] ?? '';
        $command = $_POST['command'] ?? '';
        
        $valid_services = ['tor-bridge', 'tor-relay', 'tor-server', 'unbound', 'privoxy', 'nginx'];
        $valid_commands = ['start', 'stop', 'restart'];
        
        if (!in_array($service, $valid_services)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid service']);
            break;
        }
        
        if (!in_array($command, $valid_commands)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid command']);
            break;
        }
        
        $success = false;
        switch ($command) {
            case 'start':
                $success = startService($service);
                break;
            case 'stop':
                $success = stopService($service);
                break;
            case 'restart':
                $success = restartService($service);
                break;
        }
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 
                "Successfully {$command}ed $service" : 
                "Failed to $command $service"
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
?>