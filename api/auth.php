<?php
/**
 * Drift — Authentication API
 * 
 * POST ?action=register  — Create account
 * POST ?action=login     — Login
 * POST ?action=logout    — Logout  
 * GET  ?action=check     — Check session
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handle_register();
        break;
    case 'login':
        handle_login();
        break;
    case 'logout':
        handle_logout();
        break;
    case 'check':
        handle_check();
        break;
    default:
        json_response(['error' => 'Invalid action'], 400);
}

/**
 * Register a new user
 */
function handle_register(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'Method not allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $display_name = trim($input['display_name'] ?? $username);

    // Validation
    if (strlen($username) < 3 || strlen($username) > 30) {
        json_response(['error' => 'Username must be 3-30 characters'], 400);
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        json_response(['error' => 'Username can only contain letters, numbers, and underscores'], 400);
    }
    if (strlen($password) < 8) {
        json_response(['error' => 'Password must be at least 8 characters'], 400);
    }
    if (strlen($password) > 128) {
        json_response(['error' => 'Password too long'], 400);
    }

    // Check password strength
    $strength = 0;
    if (preg_match('/[a-z]/', $password))
        $strength++;
    if (preg_match('/[A-Z]/', $password))
        $strength++;
    if (preg_match('/[0-9]/', $password))
        $strength++;
    if (preg_match('/[^a-zA-Z0-9]/', $password))
        $strength++;
    if ($strength < 2) {
        json_response(['error' => 'Password must contain at least 2 of: lowercase, uppercase, numbers, special characters'], 400);
    }

    // Check if username exists
    $users = read_json(USERS_FILE);
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            json_response(['error' => 'Username already taken'], 409);
        }
    }

    // Create user
    $user_id = generate_id();
    $user = [
        'id' => $user_id,
        'username' => $username,
        'display_name' => sanitize($display_name),
        'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
        'created_at' => date('c'),
    ];

    $users[] = $user;
    write_json(USERS_FILE, $users);

    // Create user data directory
    user_data_dir($user_id);

    // Initialize empty notes and settings
    write_json(user_data_dir($user_id) . '/notes.json', []);
    write_json(user_data_dir($user_id) . '/settings.json', [
        'ai_provider' => 'openai',
        'ai_api_key' => '',
        'theme' => 'dark',
    ]);

    // Auto-login after registration
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;

    json_response([
        'success' => true,
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'display_name' => $user['display_name'],
        ],
        'csrf_token' => csrf_token(),
    ], 201);
}

/**
 * Login
 */
function handle_login(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'Method not allowed'], 405);
    }

    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limits = read_json(RATE_LIMIT_FILE);
    $now = time();

    // Clean old entries
    $rate_limits = array_filter($rate_limits, function ($entry) use ($now) {
        return ($now - $entry['time']) < RATE_LIMIT_WINDOW;
    });

    // Count attempts for this IP
    $attempts = array_filter($rate_limits, function ($entry) use ($ip) {
        return $entry['ip'] === $ip;
    });

    if (count($attempts) >= MAX_LOGIN_ATTEMPTS) {
        $oldest = min(array_column($attempts, 'time'));
        $wait = RATE_LIMIT_WINDOW - ($now - $oldest);
        json_response([
            'error' => "Too many login attempts. Try again in " . ceil($wait / 60) . " minutes.",
        ], 429);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        json_response(['error' => 'Username and password required'], 400);
    }

    // Find user
    $users = read_json(USERS_FILE);
    $found_user = null;
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            $found_user = $user;
            break;
        }
    }

    if (!$found_user || !password_verify($password, $found_user['password_hash'])) {
        // Record failed attempt
        $rate_limits[] = ['ip' => $ip, 'time' => $now];
        write_json(RATE_LIMIT_FILE, array_values($rate_limits));

        json_response(['error' => 'Invalid username or password'], 401);
    }

    // Success — clear rate limits for this IP
    $rate_limits = array_filter($rate_limits, function ($entry) use ($ip) {
        return $entry['ip'] !== $ip;
    });
    write_json(RATE_LIMIT_FILE, array_values($rate_limits));

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $found_user['id'];
    $_SESSION['username'] = $found_user['username'];

    json_response([
        'success' => true,
        'user' => [
            'id' => $found_user['id'],
            'username' => $found_user['username'],
            'display_name' => $found_user['display_name'] ?? $found_user['username'],
        ],
        'csrf_token' => csrf_token(),
    ]);
}

/**
 * Logout
 */
function handle_logout(): void
{
    session_destroy();
    json_response(['success' => true]);
}

/**
 * Check session
 */
function handle_check(): void
{
    if (!empty($_SESSION['user_id'])) {
        json_response([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
            ],
            'csrf_token' => csrf_token(),
        ]);
    } else {
        json_response(['authenticated' => false]);
    }
}
