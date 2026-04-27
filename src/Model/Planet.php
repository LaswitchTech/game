<?php

namespace Model;

use Database\Connection;

class Planet
{
    private int $id;
    private int $userId;
    private string $name;
    private int $system;
    private int $position;
    private int $primaryPlanet;
    private string $createdAt;
    private ?string $faction;

    public function __construct(int $id, int $userId, string $name, int $system, int $position, int $primaryPlanet, string $createdAt, ?string $faction = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->system = $system;
        $this->position = $position;
        $this->primaryPlanet = $primaryPlanet;
        $this->createdAt = $createdAt;
        $this->faction = $faction;
    }

    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void
    {
        $this->name = $name;
        $db = Connection::getInstance();
        $db->prepare('UPDATE planets SET name = ? WHERE id = ?')->execute([$name, $this->id]);
    }
    public function getSystem(): int { return $this->system; }
    public function getPosition(): int { return $this->position; }
    public function isPrimaryPlanet(): bool { return $this->primaryPlanet === 1; }
    public function getFaction(): ?string { return $this->faction; }

    public function getResources(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM resources WHERE planet_id = ?');
        $stmt->execute([$this->id]);
        $row = $stmt->fetch();
        if (!$row) return [
            'supply' => 500, 'power' => 0, 'gas' => 0,
            'power_production' => 0, 'power_consumption' => 0, 'energy_balance' => 0,
            'supply_storage' => 5000, 'gas_storage' => 5000,
        ];
        return [
            'supply' => (int)$row['supply'], 'power' => (int)$row['power'], 'gas' => (int)$row['gas'],
            'power_production' => (int)$row['power_production'], 'power_consumption' => (int)$row['power_consumption'],
            'energy_balance' => (int)$row['energy_balance'],
            'supply_storage' => (int)$row['supply_storage'], 'gas_storage' => (int)$row['gas_storage'],
        ];
    }

    public function updateResources(int $supply, int $power, int $gas, int $powerProduction, int $powerConsumption, int $energyBalance, int $supplyStorage, int $gasStorage): void
    {
        $db = Connection::getInstance();
        $db->prepare('UPDATE resources SET supply = ?, power = ?, gas = ?, power_production = ?, power_consumption = ?, energy_balance = ?, supply_storage = ?, gas_storage = ? WHERE planet_id = ?')
            ->execute([$supply, $power, $gas, $powerProduction, $powerConsumption, $energyBalance, $supplyStorage, $gasStorage, $this->id]);
    }

    public function getBuildings(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM buildings WHERE planet_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function getTechnologies(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM technologies WHERE planet_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function getShips(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM ships WHERE planet_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public static function createDefault(int $userId, string $faction): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('INSERT INTO planets (user_id, name, system, position, primary_planet, faction) VALUES (?, ?, ?, ?, 1, ?)');
        $stmt->execute([$userId, 'My Planet', 1, 1, $faction]);
        $id = (int)$db->lastInsertId();

        // Create default resources
        $db->prepare('INSERT INTO resources (planet_id, supply, power, gas, power_production, power_consumption, energy_balance, supply_storage, gas_storage) VALUES (?, 500, 0, 0, 0, 0, 0, 5000, 5000)')
            ->execute([$id]);

        return $id;
    }

    public static function findById(int $id): ?self
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM planets WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByUserId(int $userId, ?string $faction = null): ?self
    {
        $db = Connection::getInstance();
        $sql = 'SELECT * FROM planets WHERE user_id = ?';
        $params = [$userId];
        if ($faction) {
            $sql .= ' AND faction = ?';
            $params[] = $faction;
        }
        $sql .= ' LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByFaction(string $faction): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM planets WHERE faction = ?');
        $stmt->execute([$faction]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'fromRow'], $rows);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['name'] ?? '',
            (int)$row['system'],
            (int)$row['position'],
            (int)$row['primary_planet'],
            $row['created_at'] ?? date('Y-m-d H:i:s'),
            $row['faction'] ?? null
        );
    }
}
