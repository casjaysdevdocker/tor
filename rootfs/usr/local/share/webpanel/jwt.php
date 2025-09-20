<?php

class SimpleJWT {
    private static $secret_key;
    
    public static function init() {
        $secret_file = '/config/secure/jwt_secret';
        
        if (isset($_ENV['TOR_JWT_SECRET'])) {
            self::$secret_key = $_ENV['TOR_JWT_SECRET'];
        } elseif (file_exists($secret_file)) {
            self::$secret_key = trim(file_get_contents($secret_file));
        } else {
            // Generate random secret and save it
            self::$secret_key = bin2hex(random_bytes(32));
            if (!is_dir(dirname($secret_file))) {
                mkdir(dirname($secret_file), 0700, true);
            }
            file_put_contents($secret_file, self::$secret_key);
            chmod($secret_file, 0600);
        }
    }
    
    public static function getSecret() {
        self::init();
        return self::$secret_key;
    }
    
    public static function encode($payload) {
        self::init();
        
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($payload, [
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ]));
        
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, self::$secret_key, true);
        $base64Signature = self::base64UrlEncode($signature);
        
        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }
    
    public static function decode($jwt) {
        self::init();
        
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        $signature = self::base64UrlDecode($base64Signature);
        $expectedSignature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, self::$secret_key, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expired
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

function generateApiToken($username) {
    return SimpleJWT::encode([
        'username' => $username,
        'role' => 'admin'
    ]);
}

function validateApiToken($token) {
    $payload = SimpleJWT::decode($token);
    return $payload !== false ? $payload : null;
}

?>