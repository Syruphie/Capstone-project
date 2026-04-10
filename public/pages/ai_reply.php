<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$message = trim($data['message'] ?? '');

if ($message === '') {
    echo json_encode(['reply' => 'Please enter a message.']);
    exit;
}

$systemPrompt = "You are a helpful assistant for the GlobenTech Laboratory Order Management System.
Answer clearly, briefly, and professionally.
Help only with topics related to:
- orders
- approvals
- equipment
- users
- reports
- calendar
- account settings
- logout

If the question is unrelated, politely guide the user back to system-related help.";

$payload = json_encode([
    'model' => 'llama3.2',
    'prompt' => $systemPrompt . "\n\nUser: " . $message . "\nAssistant:",
    'stream' => false
]);

if (!function_exists('curl_init')) {
    echo json_encode(['reply' => 'PHP cURL is not enabled.']);
    exit;
}

$ch = curl_init('http://127.0.0.1:11434/api/generate');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        'reply' => 'cURL error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        'reply' => 'Ollama returned HTTP ' . $httpCode,
        'raw' => $response
    ]);
    exit;
}

$result = json_decode($response, true);

if (!$result) {
    echo json_encode([
        'reply' => 'Invalid JSON from Ollama.',
        'raw' => $response
    ]);
    exit;
}

echo json_encode([
    'reply' => $result['response'] ?? 'No response from model.'
]);