<?php
declare(strict_types=1);

require_once __DIR__ . '/JsonResponse.php';

class ApiAuth
{
    public static function requireUser(?array $roles = null): FrontendUser
    {
        $user = new FrontendUser();

        if (!$user->isLoggedIn()) {
            JsonResponse::send(['success' => false, 'error' => 'Unauthorized'], 401);
            exit;
        }

        if ($roles !== null && !in_array($user->getRole(), $roles, true)) {
            JsonResponse::send(['success' => false, 'error' => 'Forbidden'], 403);
            exit;
        }

        return $user;
    }
}

