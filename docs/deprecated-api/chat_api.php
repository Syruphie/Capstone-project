<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/classes/Chatbot/Service/ChatbotNavigationService.php';

$message = (string)($_POST['message'] ?? '');
$service = new ChatbotNavigationService();

echo $service->reply($message);

