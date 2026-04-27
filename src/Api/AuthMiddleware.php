<?php

namespace Api\Middleware;

use Service\AuthService;

class AuthMiddleware
{
    /**
     * Check if the current request is authenticated.
     */
    public static function check(): ?array
    {
        $user = AuthService::getCurrentUser();
        if ($user === null) {
            http_response_code(401);
            return ['error' => 'Authentication required'];
        }
        return ['user' => $user];
    }
}
