<?php
/**
 * Drift — Notes API
 * 
 * GET              — Fetch all notes
 * POST             — Create note
 * PUT ?id=<id>     — Update note
 * DELETE ?id=<id>  — Delete note
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$user = require_auth();
$notes_file = user_data_dir($user['user_id']) . '/notes.json';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get_notes($notes_file);
        break;
    case 'POST':
        handle_create_note($notes_file);
        break;
    case 'PUT':
        handle_update_note($notes_file);
        break;
    case 'DELETE':
        handle_delete_note($notes_file);
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

/**
 * Get all notes, sorted by pinned first then newest first
 */
function handle_get_notes(string $file): void
{
    $notes = read_json($file);

    // Special action: return all content for AI context
    if (($_GET['action'] ?? '') === 'all_content') {
        json_response(['notes' => $notes]);
        return;
    }

    // Apply search filter if provided
    $search = trim($_GET['q'] ?? '');
    if ($search !== '') {
        $search_lower = strtolower($search);
        $notes = array_filter($notes, function ($note) use ($search_lower) {
            return strpos(strtolower($note['content']), $search_lower) !== false;
        });
    }

    // Apply mood filter if provided
    $mood_filter = $_GET['mood'] ?? '';
    if ($mood_filter !== '') {
        $notes = array_filter($notes, function ($note) use ($mood_filter) {
            return ($note['mood'] ?? '') === $mood_filter;
        });
    }

    // Sort: pinned first, then by created_at descending
    usort($notes, function ($a, $b) {
        $a_pinned = $a['pinned'] ?? false;
        $b_pinned = $b['pinned'] ?? false;
        if ($a_pinned !== $b_pinned)
            return $b_pinned ? 1 : -1;
        return strcmp($b['created_at'], $a['created_at']);
    });

    json_response(['notes' => array_values($notes)]);
}

/**
 * Create a new note
 */
function handle_create_note(string $file): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    $content = trim($input['content'] ?? '');

    if ($content === '') {
        json_response(['error' => 'Content cannot be empty'], 400);
    }

    if (strlen($content) > 50000) {
        json_response(['error' => 'Note too long (max 50,000 characters)'], 400);
    }

    $mood = $input['mood'] ?? '';
    $valid_moods = ['', 'urgent', 'idea', 'task', 'calm'];
    if (!in_array($mood, $valid_moods)) {
        $mood = '';
    }

    $note = [
        'id' => generate_id(),
        'content' => $content,
        'mood' => $mood,
        'pinned' => false,
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];

    $notes = read_json($file);
    $notes[] = $note;
    write_json($file, $notes);

    json_response(['note' => $note], 201);
}

/**
 * Update an existing note
 */
function handle_update_note(string $file): void
{
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        json_response(['error' => 'Note ID required'], 400);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $notes = read_json($file);
    $found = false;
    $updated_note = null;

    foreach ($notes as &$note) {
        if ($note['id'] === $id) {
            if (isset($input['content'])) {
                $content = trim($input['content']);
                if ($content === '') {
                    json_response(['error' => 'Content cannot be empty'], 400);
                }
                if (strlen($content) > 50000) {
                    json_response(['error' => 'Note too long'], 400);
                }
                $note['content'] = $content;
            }
            if (isset($input['mood'])) {
                $valid_moods = ['', 'urgent', 'idea', 'task', 'calm'];
                $note['mood'] = in_array($input['mood'], $valid_moods) ? $input['mood'] : '';
            }
            if (isset($input['pinned'])) {
                $note['pinned'] = (bool) $input['pinned'];
            }
            $note['updated_at'] = date('c');
            $updated_note = $note;
            $found = true;
            break;
        }
    }
    unset($note);

    if (!$found) {
        json_response(['error' => 'Note not found'], 404);
    }

    write_json($file, $notes);
    json_response(['note' => $updated_note]);
}

/**
 * Delete a note
 */
function handle_delete_note(string $file): void
{
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        json_response(['error' => 'Note ID required'], 400);
    }

    $notes = read_json($file);
    $original_count = count($notes);
    $notes = array_filter($notes, function ($note) use ($id) {
        return $note['id'] !== $id;
    });

    if (count($notes) === $original_count) {
        json_response(['error' => 'Note not found'], 404);
    }

    write_json($file, array_values($notes));
    json_response(['success' => true]);
}
