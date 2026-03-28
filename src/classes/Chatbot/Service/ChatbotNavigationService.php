<?php
declare(strict_types=1);

class ChatbotNavigationService
{
    public function reply(string $message): string
    {
        $text = strtolower(trim($message));

        if (str_contains($text, 'order')) {
            return 'You can approve orders from the My Orders -> Approvals section.';
        }

        if (str_contains($text, 'equipment')) {
            return 'Equipment management is available under the Equipment tab.';
        }

        if (str_contains($text, 'user')) {
            return 'User accounts are managed in the Users section.';
        }

        if (str_contains($text, 'report')) {
            return 'Reports can be accessed from the Reports menu.';
        }

        if (str_contains($text, 'logout')) {
            return 'Click the Logout button at the top-right corner.';
        }

        return 'I can help you navigate the dashboard. Try asking about Orders, Equipment, Users, or Reports.';
    }
}

