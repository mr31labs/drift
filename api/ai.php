<?php
/**
 * Drift — AI Proxy API (Enhanced)
 * 
 * POST ?action=<action> — Process AI request
 * 
 * Actions: summarize, expand, rewrite, connect, ask, freeform,
 *          auto_mood, polish, digest, extract_actions, chat, coach, smart_search
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$user = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
$content = trim($input['content'] ?? '');
$question = trim($input['question'] ?? '');
$all_notes = $input['all_notes'] ?? '';

if ($content === '' && !in_array($action, ['freeform', 'digest', 'chat', 'smart_search'])) {
    json_response(['error' => 'Content is required'], 400);
}

// Get user settings for API key
$settings_file = user_data_dir($user['user_id']) . '/settings.json';
$settings = read_json($settings_file);

$api_key = '';
if (!empty($settings['ai_api_key'])) {
    $api_key = decrypt_string($settings['ai_api_key']);
}

if (empty($api_key) && !empty($input['api_key'])) {
    $api_key = $input['api_key'];
}

if (empty($api_key)) {
    json_response(['error' => 'No API key configured. Go to Settings to add your AI API key.'], 400);
}

$provider = $settings['ai_provider'] ?? 'openai';

// Build the prompt based on action
$system_prompt = "You are a helpful thinking partner integrated into a note-taking app called Drift. Keep responses concise and useful.";
$user_prompt = '';
$max_tokens = 1000;
$temperature = 0.7;
$response_format = 'text'; // or 'json'

switch ($action) {
    // ── Original Actions ──────────────────────────────────
    case 'summarize':
        $user_prompt = "Summarize the following note concisely, capturing the key points:\n\n" . $content;
        break;
    case 'expand':
        $user_prompt = "Expand on the following idea. Add depth, examples, and related thoughts:\n\n" . $content;
        $max_tokens = 1500;
        break;
    case 'rewrite':
        $user_prompt = "Rewrite the following note for clarity and better structure, keeping the same meaning:\n\n" . $content;
        break;
    case 'connect':
        $user_prompt = "Based on this note, suggest related ideas, connections, and follow-up thoughts:\n\n" . $content;
        break;
    case 'ask':
        if (empty($question)) {
            json_response(['error' => 'Question is required for ask action'], 400);
        }
        $user_prompt = "Given this note:\n\n" . $content . "\n\nAnswer this question about it: " . $question;
        break;
    case 'freeform':
        if (empty($question)) {
            json_response(['error' => 'Prompt is required'], 400);
        }
        $user_prompt = $question;
        if (!empty($content)) {
            $user_prompt = "Context from my note:\n\n" . $content . "\n\n" . $question;
        }
        break;

    // ── NEW: Auto Mood Detection ──────────────────────────
    case 'auto_mood':
        $system_prompt = "You are a content classifier. Analyze the note and return ONLY one of these mood labels based on the content's nature. Return just the single word, nothing else.";
        $user_prompt = "Classify this note into exactly one mood category:\n" .
            "- urgent: time-sensitive, deadlines, emergencies, critical items\n" .
            "- task: to-dos, action items, chores, reminders, lists of things to do\n" .
            "- idea: creative thoughts, brainstorming, concepts, possibilities, inspiration\n" .
            "- calm: reflections, gratitude, journaling, observations, relaxed thoughts\n\n" .
            "If it doesn't clearly fit any category, respond with: none\n\n" .
            "Note:\n" . $content;
        $max_tokens = 10;
        $temperature = 0.1;
        break;

    // ── NEW: Smart Capture (Polish) ───────────────────────
    case 'polish':
        $system_prompt = "You are a writing assistant. Clean up the user's raw input: fix grammar, improve clarity, add markdown formatting where helpful (lists, bold for key terms). Keep the original meaning and voice. Do NOT add information that wasn't there. Return ONLY the polished text, no explanations.";
        $user_prompt = $content;
        $temperature = 0.3;
        break;

    // ── NEW: Daily Digest ─────────────────────────────────
    case 'digest':
        if (empty($all_notes)) {
            // Load notes from file
            $notes_file = user_data_dir($user['user_id']) . '/notes.json';
            $notes = read_json($notes_file);
            $all_notes = format_notes_for_context($notes);
        }
        if (empty($all_notes)) {
            json_response(['error' => 'No notes to digest'], 400);
        }
        $system_prompt = "You are a personal assistant summarizing a user's notes. Create a clear, organized daily digest. Group by themes, highlight action items, surface key ideas. Use markdown formatting with headers, bullets, and bold text. Be concise but thorough.";
        $user_prompt = "Create a digest of these notes. Summarize themes, surface action items, and highlight key ideas:\n\n" . $all_notes;
        $max_tokens = 2000;
        break;

    // ── NEW: Extract Action Items ─────────────────────────
    case 'extract_actions':
        $system_prompt = "You are a task extraction specialist. Extract every actionable item, to-do, commitment, or task from the text. Return them as a clean markdown checklist. If there are no action items, say so. Do NOT invent tasks that aren't implied by the text.";
        if (!empty($all_notes)) {
            $user_prompt = "Extract all action items and to-dos from these notes:\n\n" . $all_notes;
        } else {
            $user_prompt = "Extract all action items and to-dos from this note:\n\n" . $content;
        }
        $max_tokens = 1500;
        $temperature = 0.2;
        break;

    // ── NEW: AI Chat (Contextual Q&A) ─────────────────────
    case 'chat':
        if (empty($question)) {
            json_response(['error' => 'Message is required'], 400);
        }
        // Load all notes for context
        if (empty($all_notes)) {
            $notes_file = user_data_dir($user['user_id']) . '/notes.json';
            $notes = read_json($notes_file);
            $all_notes = format_notes_for_context($notes);
        }
        $system_prompt = "You are a smart assistant with access to the user's personal notes. Answer questions based on their notes. If the answer isn't in the notes, say so but still try to be helpful. Reference specific notes when relevant. Keep responses conversational and concise. Use markdown formatting.";
        $user_prompt = "My notes:\n\n" . $all_notes . "\n\n---\n\nUser question: " . $question;
        if (!empty($content)) {
            $user_prompt .= "\n\nConversation context:\n" . $content;
        }
        $max_tokens = 1500;
        break;

    // ── NEW: Writing Coach ────────────────────────────────
    case 'coach':
        $system_prompt = "You are a writing coach. Analyze the note for: clarity, structure, tone, and completeness. Provide specific, actionable suggestions. Use this format:\n\n## Overall Assessment\nBrief overall rating (★ out of 5).\n\n## Suggestions\n- Specific, numbered suggestions with examples of improved text\n\n## Rewritten Version\nA polished version incorporating your suggestions.\n\nBe encouraging but honest.";
        $user_prompt = "Coach me on this note:\n\n" . $content;
        $max_tokens = 2000;
        $temperature = 0.5;
        break;

    // ── NEW: Smart Search ─────────────────────────────────
    case 'smart_search':
        if (empty($question)) {
            json_response(['error' => 'Search query is required'], 400);
        }
        if (empty($all_notes)) {
            $notes_file = user_data_dir($user['user_id']) . '/notes.json';
            $notes = read_json($notes_file);
            $all_notes = format_notes_for_context($notes, true);
        }
        $system_prompt = "You are a semantic search engine for personal notes. Given a search query and a set of notes, find the most relevant notes. Return a JSON array of matching note IDs with a brief reason why each matches. Format: [{\"id\": \"note_id\", \"reason\": \"why it matches\"}]. Return at most 10 results. If no notes match, return an empty array []. Return ONLY valid JSON, no other text.";
        $user_prompt = "Search query: \"" . $question . "\"\n\nNotes:\n" . $all_notes;
        $max_tokens = 500;
        $temperature = 0.1;
        break;

    default:
        json_response(['error' => 'Invalid action. Available: summarize, expand, rewrite, connect, ask, freeform, auto_mood, polish, digest, extract_actions, chat, coach, smart_search'], 400);
}

// Route to the right provider
try {
    if ($provider === 'gemini') {
        $result = call_gemini($api_key, $system_prompt, $user_prompt, $max_tokens, $temperature);
    } else {
        $result = call_openai($api_key, $system_prompt, $user_prompt, $max_tokens, $temperature);
    }

    // Post-process for specific actions
    if ($action === 'auto_mood') {
        $mood = strtolower(trim($result));
        $valid = ['urgent', 'task', 'idea', 'calm', 'none'];
        if (!in_array($mood, $valid))
            $mood = 'none';
        json_response(['mood' => $mood === 'none' ? '' : $mood]);
    } elseif ($action === 'smart_search') {
        $parsed = json_decode($result, true);
        if (!is_array($parsed))
            $parsed = [];
        json_response(['results' => $parsed]);
    } else {
        json_response(['result' => $result]);
    }
} catch (Exception $e) {
    json_response(['error' => 'AI request failed: ' . $e->getMessage()], 502);
}

/**
 * Format notes array into a text block for AI context
 */
function format_notes_for_context(array $notes, bool $include_ids = false): string
{
    if (empty($notes))
        return '';

    $lines = [];
    foreach ($notes as $note) {
        $date = date('M j, g:ia', strtotime($note['created_at']));
        $mood = !empty($note['mood']) ? " [{$note['mood']}]" : '';
        $pin = !empty($note['pinned']) ? " [pinned]" : '';

        if ($include_ids) {
            $lines[] = "--- Note ID: {$note['id']} | {$date}{$mood}{$pin} ---\n{$note['content']}";
        } else {
            $lines[] = "--- {$date}{$mood}{$pin} ---\n{$note['content']}";
        }
    }
    return implode("\n\n", $lines);
}

/**
 * Call OpenAI API
 */
function call_openai(string $api_key, string $system, string $user_msg, int $max_tokens = 1000, float $temperature = 0.7): string
{
    $url = 'https://api.openai.com/v1/chat/completions';
    $payload = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user_msg],
        ],
        'max_tokens' => $max_tokens,
        'temperature' => $temperature,
    ];

    $response = http_post_json($url, $payload, [
        'Authorization: Bearer ' . $api_key,
    ]);

    $data = json_decode($response, true);
    if (isset($data['error'])) {
        throw new Exception($data['error']['message'] ?? 'OpenAI error');
    }

    return $data['choices'][0]['message']['content'] ?? 'No response generated.';
}

/**
 * Call Google Gemini API
 */
function call_gemini(string $api_key, string $system, string $user_msg, int $max_tokens = 1000, float $temperature = 0.7): string
{
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($api_key);
    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $system]]
        ],
        'contents' => [
            ['parts' => [['text' => $user_msg]]]
        ],
        'generationConfig' => [
            'maxOutputTokens' => $max_tokens,
            'temperature' => $temperature,
        ]
    ];

    $response = http_post_json($url, $payload);

    $data = json_decode($response, true);
    if (isset($data['error'])) {
        throw new Exception($data['error']['message'] ?? 'Gemini error');
    }

    return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';
}

/**
 * Generic HTTP POST with JSON body
 */
function http_post_json(string $url, array $payload, array $extra_headers = []): string
{
    $json_body = json_encode($payload);
    $headers = array_merge([
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_body),
    ], $extra_headers);

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $json_body,
            'timeout' => 60,
            'ignore_errors' => true,
        ]
    ]);

    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) {
        throw new Exception('Failed to connect to AI service');
    }

    return $response;
}
