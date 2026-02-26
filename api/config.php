<?php
/**
 * Drift — Configuration & Utilities
 */

// Error reporting (disable display in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Base paths
define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', BASE_DIR . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('RATE_LIMIT_FILE', DATA_DIR . '/rate_limits.json');

// Security
define('BCRYPT_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 minutes in seconds
define('ENCRYPTION_METHOD', 'aes-256-cbc');

// Session config
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

session_start();

// Ensure data directories exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0700, true);
}

/**
 * Derive encryption key from server-specific info
 */
function get_encryption_key(): string {
    $secret_file = DATA_DIR . '/.secret_key';
    if (!file_exists($secret_file)) {
        $key = bin2hex(random_bytes(32));
        file_put_contents($secret_file, $key);
        chmod($secret_file, 0600);
    }
    return file_get_contents($secret_file);
}

/**
 * Encrypt a string
 */
function encrypt_string(string $plaintext): string {
    $key = hex2bin(get_encryption_key());
    $iv = random_bytes(openssl_cipher_iv_length(ENCRYPTION_METHOD));
    $encrypted = openssl_encrypt($plaintext, ENCRYPTION_METHOD, $key, 0, $iv);
    return base64_encode($iv . '::' . $encrypted);
}

/**
 * Decrypt a string
 */
function decrypt_string(string $ciphertext): string {
    $key = hex2bin(get_encryption_key());
    $parts = explode('::', base64_decode($ciphertext), 2);
    if (count($parts) !== 2) return '';
    [$iv, $encrypted] = $parts;
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, $key, 0, $iv) ?: '';
}

/**
 * Read JSON file safely
 */
function read_json(string $file): array {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

/**
 * Write JSON file safely (atomic write)
 */
function write_json(string $file, array $data): bool {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $tmp = $file . '.tmp.' . uniqid();
    $result = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($result === false) {
        @unlink($tmp);
        return false;
    }
    return rename($tmp, $file);
}

/**
 * Send JSON response
 */
function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Require authentication
 */
function require_auth(): array {
    if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
        json_response(['error' => 'Not authenticated'], 401);
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username']
    ];
}

/**
 * Get user data directory
 */
function user_data_dir(string $user_id): string {
    $dir = DATA_DIR . '/users/' . $user_id;
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    return $dir;
}

/**
 * Generate CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate unique ID
 */
function generate_id(): string {
    return bin2hex(random_bytes(16));
}
