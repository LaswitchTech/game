<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Planet;

class AuthController
{
    public function register(array $params): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['faction'])) {
            http_response_code(400);
            return ['error' => 'Missing required fields'];
        }

        return AuthService::register($data['username'], $data['email'], $data['password'], $data['faction']);
    }

    public function login(array $params): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            return ['error' => 'Missing required fields'];
        }

        return AuthService::login($data['username'], $data['password']);
    }

    public function logout(array $params): array
    {
        AuthService::logout();
        return ['message' => 'Logged out successfully'];
    }

    public function me(array $params): array
    {
        $user = AuthService::requireAuth();
        if ($user === null) return ['error' => 'Not authenticated'];

        $planet = Planet::findByUserId($user->getId(), $user->getFaction());

        return [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'faction' => $user->getFaction(),
            'planet_id' => $planet !== null ? $planet->getId() : null,
        ];
    }
}
