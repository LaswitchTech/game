<?php

namespace Model;

use Database\Connection;

class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private ?string $sessionToken;
    private ?string $sessionExpires;
    private string $createdAt;
    private ?string $lastLogin;
    private ?string $faction;

    public function __construct(
        ?int $id,
        string $username,
        string $email,
        string $passwordHash,
        ?string $sessionToken,
        ?string $sessionExpires,
        string $createdAt,
        ?string $lastLogin,
        ?string $faction = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->sessionToken = $sessionToken;
        $this->sessionExpires = $sessionExpires;
        $this->createdAt = $createdAt;
        $this->lastLogin = $lastLogin;
        $this->faction = $faction;
    }

    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getSessionToken(): ?string { return $this->sessionToken; }
    public function getFaction(): ?string { return $this->faction; }

    public function hasActiveSession(): bool
    {
        if ($this->sessionToken === null || $this->sessionExpires === null) return false;
        return strtotime($this->sessionExpires) > time();
    }

    public static function findById(int $id): ?self
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByUsername(string $username): ?self
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function create(string $username, string $email, string $passwordHash, string $faction): ?self
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, faction) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $passwordHash, $faction]);
        $id = (int)$db->lastInsertId();

        // Create default planet with faction
        $planetId = Planet::createDefault($id, $faction);

        return new self($id, $username, $email, $passwordHash, null, null, date('Y-m-d H:i:s'), null, $faction);
    }

    public function updateSession(?string $token, ?string $expires): void
    {
        $this->sessionToken = $token;
        $this->sessionExpires = $expires;
        $db = Connection::getInstance();
        $stmt = $db->prepare('UPDATE users SET session_token = ?, session_expires = ?, last_login = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$token, $expires, $this->id]);
    }

    public function clearSession(): void
    {
        $this->sessionToken = null;
        $this->sessionExpires = null;
        $db = Connection::getInstance();
        $stmt = $db->prepare('UPDATE users SET session_token = NULL, session_expires = NULL WHERE id = ?');
        $stmt->execute([$this->id]);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int)$row['id'] : null,
            $row['username'] ?? '',
            $row['email'] ?? '',
            $row['password_hash'] ?? '',
            $row['session_token'] ?? null,
            $row['session_expires'] ?? null,
            $row['created_at'] ?? date('Y-m-d H:i:s'),
            $row['last_login'] ?? null,
            $row['faction'] ?? null
        );
    }

    public static function getByFaction(string $faction): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE faction = ?');
        $stmt->execute([$faction]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'fromRow'], $rows);
    }
}
