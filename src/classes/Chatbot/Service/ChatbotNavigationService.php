<?php

class ChatbotNavigationService
{
    public function reply($message)
    {
        $message = trim((string)$message);

        if ($message === '') {
            return 'Please ask a question.';
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

        $payload = array(
            'model' => 'llama3.2',
            'prompt' => $systemPrompt . "\n\nUser: " . $message . "\nAssistant:",
            'stream' => false
        );

        $ch = curl_init('http://localhost:11434/api/generate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            return 'AI service is not running right now. Please start Ollama.';
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return 'AI service returned an unexpected response.';
        }

        $data = json_decode($response, true);

        if (!is_array($data) || !isset($data['response'])) {
            return 'I could not generate a response.';
        }

        return trim((string)$data['response']);
    }
}