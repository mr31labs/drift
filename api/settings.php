<?php
/**
 * Drift — Settings API
 * 
 * GET  — Fetch settings
 * POST — Save settings
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$user = require_auth();
$settings_file = user_data_dir($user['user_id']) . '/settings.json';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get_settings($settings_file);
        break;
    case 'POST':
        handle_save_settings($settings_file);
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

/**
 * Get settings (mask API key)
 */
function handle_get_settings(string $file): void
{
    $settings = read_json($file);

    // Mask the API key for display
    $has_key = !empty($settings['ai_api_key']);
    $masked_key = '';
    if ($has_key) {
        $decrypted = decrypt_string($settings['ai_api_key']);
        if (strlen($decrypted) > 8) {
            $masked_key = substr($decrypted, 0, 4) . '••••' . substr($decrypted, -4);
        } else {
            $masked_key = '••••••••';
        }
    }

    json_response([
        'settings' => [
            'ai_provider' => $settings['ai_provider'] ?? 'openai',
            'ai_api_key_masked' => $masked_key,
            'has_api_key' => $has_key,
            'theme' => $settings['theme'] ?? 'dark',
        ]
    ]);
}

/**
 * Save settings
 */
function handle_save_settings(string $file): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $settings = read_json($file);

    // Update provider
    if (isset($input['ai_provider'])) {
        $valid_providers = ['openai', 'gemini'];
        if (in_array($input['ai_provider'], $valid_providers)) {
            $settings['ai_provider'] = $input['ai_provider'];
        }
    }

    // Update API key (encrypt before storing)
    if (isset($input['ai_api_key']) && $input['ai_api_key'] !== '') {
        $settings['ai_api_key'] = encrypt_string($input['ai_api_key']);
    }

    // Allow clearing the key
    if (isset($input['clear_api_key']) && $input['clear_api_key'] === true) {
        $settings['ai_api_key'] = '';
    }

    // Update theme
    if (isset($input['theme'])) {
        $settings['theme'] = $input['theme'];
    }

    write_json($file, $settings);

    json_response(['success' => true]);
}
