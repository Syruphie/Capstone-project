<?php
$message = strtolower($_POST['message'] ?? '');

if (str_contains($message, 'order')) {
    echo "You can approve orders from the My Orders → Approvals section.";
}
elseif (str_contains($message, 'equipment')) {
    echo "Equipment management is available under the Equipment tab.";
}
elseif (str_contains($message, 'user')) {
    echo "User accounts are managed in the Users section.";
}
elseif (str_contains($message, 'report')) {
    echo "Reports can be accessed from the Reports menu.";
}
elseif (str_contains($message, 'logout')) {
    echo "Click the Logout button at the top-right corner.";
}
else {
    echo "I can help you navigate the dashboard. Try asking about Orders, Equipment, Users, or Reports.";
} 
