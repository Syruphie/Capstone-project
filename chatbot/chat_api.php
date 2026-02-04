<?php
$message = strtolower($_POST['message']);

if (str_contains($message, 'order')) {
    echo "Go to the Approvals tab to review and approve orders.";
}
elseif (str_contains($message, 'equipment')) {
    echo "Click on Equipment in the top menu to manage lab equipment.";
}
elseif (str_contains($message, 'users')) {
    echo "User management is available under the Users tab (Admins only).";
}
elseif (str_contains($message, 'logout')) {
    echo "Use the Logout button on the top-right corner.";
}
else {
    echo "I can help with navigation. Try asking about Orders, Equipment, or Users.";
}
