<?php
require_once '../auth.php';
require_once '../functions.php';
require_once '../jwt.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Authentication check
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

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/admin/api', '', $path);
$path = trim($path, '/');

// Public endpoints (no auth required)
if ($method === 'POST' && $path === 'auth/login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (authenticate($username, $password)) {
        $token = generateApiToken($username);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'expires_in' => 86400
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit;
}

// Require authentication for all other endpoints
if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Protected endpoints
switch ($method) {
    case 'GET':
        handleGetRequest($path);
        break;
    case 'POST':
        handlePostRequest($path);
        break;
    case 'PUT':
        handlePutRequest($path);
        break;
    case 'DELETE':
        handleDeleteRequest($path);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGetRequest($path) {
    switch ($path) {
        case 'services':
        case 'services/status':
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
                $status[] = [
                    'id' => $service,
                    'name' => $name,
                    'running' => getServiceStatus($service),
                    'pid' => getServicePid($service)
                ];
            }
            
            echo json_encode(['data' => $status]);
            break;
            
        case 'system/stats':
            echo json_encode(['data' => getSystemStats()]);
            break;
            
        case 'hidden-services':
            echo json_encode(['data' => getHiddenServices()]);
            break;
            
        default:
            if (preg_match('/^services\/([^\/]+)$/', $path, $matches)) {
                $service = $matches[1];
                $valid_services = ['tor-bridge', 'tor-relay', 'tor-server', 'unbound', 'privoxy', 'nginx'];
                
                if (in_array($service, $valid_services)) {
                    echo json_encode([
                        'data' => [
                            'id' => $service,
                            'running' => getServiceStatus($service),
                            'pid' => getServicePid($service)
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Service not found']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
    }
}

function handlePostRequest($path) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($path) {
        case 'hidden-services':
            $name = $input['name'] ?? '';
            $port_mapping = $input['port_mapping'] ?? '';
            
            if (empty($name) || empty($port_mapping)) {
                http_response_code(400);
                echo json_encode(['error' => 'Name and port_mapping required']);
                return;
            }
            
            if (!validateServiceName($name) || !validatePortMapping($port_mapping)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid name or port mapping format']);
                return;
            }
            
            if (createHiddenService($name, $port_mapping)) {
                echo json_encode(['success' => true, 'message' => 'Hidden service created']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create hidden service']);
            }
            break;
            
        default:
            if (preg_match('/^services\/([^\/]+)\/([^\/]+)$/', $path, $matches)) {
                $service = $matches[1];
                $action = $matches[2];
                
                $valid_services = ['tor-bridge', 'tor-relay', 'tor-server', 'unbound', 'privoxy', 'nginx'];
                $valid_actions = ['start', 'stop', 'restart'];
                
                if (!in_array($service, $valid_services)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Service not found']);
                    return;
                }
                
                if (!in_array($action, $valid_actions)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                    return;
                }
                
                $success = false;
                switch ($action) {
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
                        "Successfully {$action}ed $service" : 
                        "Failed to $action $service"
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
    }
}

function handlePutRequest($path) {
    http_response_code(501);
    echo json_encode(['error' => 'PUT method not implemented yet']);
}

function handleDeleteRequest($path) {
    if (preg_match('/^hidden-services\/([^\/]+)$/', $path, $matches)) {
        $name = $matches[1];
        
        if (deleteHiddenService($name)) {
            echo json_encode(['success' => true, 'message' => 'Hidden service deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete hidden service']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
}
?>