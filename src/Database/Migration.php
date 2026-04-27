<?php

namespace Database;

class Migration
{
    private \PDO $db;
    private array $migrations = [];

    public function __construct()
    {
        $this->db = Connection::getInstance();
        $this->loadMigrations();
    }

    private function loadMigrations(): void
    {
        $migrationDir = __DIR__ . '/../../migrations';
        if (!is_dir($migrationDir)) {
            return;
        }

        $files = glob($migrationDir . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.sql');
            $this->migrations[$name] = file_get_contents($file);
        }
    }

    public function run(): void
    {
        $this->createMigrationsTable();

        $applied = $this->getAppliedMigrations();

        foreach ($this->migrations as $name => $sql) {
            if (in_array($name, $applied)) {
                continue;
            }

            $statements = $this->splitStatements($sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue;
                }
                $this->db->exec($statement);
            }

            $this->db->exec("INSERT INTO migrations (name) VALUES (" . $this->db->quote($name) . ")");
        }
    }

    private function createMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function getAppliedMigrations(): array
    {
        $result = $this->db->query('SELECT name FROM migrations');
        return array_column($result->fetchAll(), 'name');
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';

        $lines = explode("\n", $sql);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '--') === 0) {
                continue;
            }
            $current .= $line . "\n";
            if (substr($line, -1) === ';') {
                $statements[] = $current;
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }
}
