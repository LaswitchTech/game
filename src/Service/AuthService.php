<?php

namespace Service;

use Model\User;
use Model\Planet;

class AuthService
{
    public static function register(string $username, string $email, string $password, string $faction): array
    {
        if (strlen($username) < 3 || strlen($username) > 20) return ['error' => 'Username must be 3-20 characters'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Invalid email address'];
        if (strlen($password) < 6) return ['error' => 'Password must be at least 6 characters'];
        if (!in_array($faction, ['unsc', 'covenant'])) return ['error' => 'Invalid faction selection'];
        if (User::findByUsername($username) !== null) return ['error' => 'Username already taken'];
        if (User::findByEmail($email) !== null) return ['error' => 'Email already registered'];

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $user = User::create($username, $email, $passwordHash, $faction);
        $planet = Planet::findByUserId($user->getId());

        return ['user_id' => $user->getId(), 'planet_id' => $planet->getId(), 'username' => $username, 'faction' => $faction, 'message' => 'Account created successfully'];
    }

    public static function login(string $username, string $password): array
    {
        $user = User::findByUsername($username);
        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            return ['error' => 'Invalid username or password'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $user->updateSession($token, $expires);

        // Set PHP session cookie
        setcookie('session_token', $token, strtotime($expires), '/', '', false, true);
        setcookie('user_id', (string)$user->getId(), strtotime($expires), '/', '', false, true);
        setcookie('faction', $user->getFaction() ?? '', strtotime($expires), '/', '', false, true);

        $planet = Planet::findByUserId($user->getId(), $user->getFaction());
        return ['user_id' => $user->getId(), 'username' => $user->getUsername(), 'faction' => $user->getFaction(), 'planet_id' => $planet->getId(), 'message' => 'Login successful'];
    }

    public static function logout(): void
    {
        if (isset($_COOKIE['session_token'])) {
            $token = $_COOKIE['session_token'];
            $user = User::findById((int)($_COOKIE['user_id'] ?? 0));
            if ($user !== null && $user->getSessionToken() === $token) {
                $user->clearSession();
            }
        }
        setcookie('session_token', '', time() - 3600, '/', '', false, true);
        setcookie('user_id', '', time() - 3600, '/', '', false, true);
        setcookie('faction', '', time() - 3600, '/', '', false, true);
    }

    public static function requireAuth(): ?User
    {
        $token = $_COOKIE['session_token'] ?? null;
        $userId = (int)($_COOKIE['user_id'] ?? 0);

        if ($token === null || $userId === 0) return null;

        $user = User::findById($userId);
        if ($user === null || $user->getSessionToken() !== $token || !$user->hasActiveSession()) return null;

        return $user;
    }

    public static function getFactionFromSession(): ?string
    {
        return $_COOKIE['faction'] ?? null;
    }
}
