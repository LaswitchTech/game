<?php

namespace Service;

use Model\Flood;
use Model\Planet;
use Game\FloodTypes;

class FloodService
{
    /**
     * Check if Flood should emerge based on trigger conditions.
     */
    public static function checkEmergence(int $gameTick): void
    {
        if (Flood::isEmerged()) {
            return;
        }

        $triggered = false;

        // 1. Hard tick minimum
        if ($gameTick >= FloodTypes::TRIGGER_MIN_TICK) {
            $triggered = true;
        }

        // 2. Server age trigger (multiplayer)
        if (!$triggered && self::getServerAge() >= FloodTypes::TRIGGER_MIN_SERVER_AGE) {
            $triggered = true;
        }

        // 3. Player power threshold
        if (!$triggered) {
            $totalPower = self::calculateTotalPlayerPower();
            if ($totalPower >= FloodTypes::TRIGGER_MIN_POWER_THRESHOLD) {
                $triggered = true;
            }
        }

        if ($triggered) {
            self::triggerEmergence($gameTick);
        }
    }

    /**
     * Run Flood logic for the current tick.
     */
    public static function tick(int $gameTick, int $userId, string $faction): void
    {
        if (!Flood::isEmerged()) {
            return;
        }

        $state = Flood::getState();

        // Check phase transitions
        self::checkPhaseTransition($gameTick);

        // Check for outbreak waves
        self::checkWaveOutbreak($gameTick);

        // Spread to nearby planets
        self::spread($gameTick, $userId, $faction);
    }

    /**
     * Calculate total player power across all planets.
     */
    private static function calculateTotalPlayerPower(): int
    {
        $db = \Database\Connection::getInstance();
        $stmt = $db->query('SELECT SUM(level) as total FROM technologies WHERE level > 0');
        $row = $stmt->fetch();

        $techPower = (int)($row['total'] ?? 0) * 100;

        // Building contributions
        $buildStmt = $db->query('SELECT SUM(level) as total FROM buildings WHERE level > 0');
        $buildRow = $buildStmt->fetch();
        $buildingPower = (int)($buildRow['total'] ?? 0) * 50;

        return $techPower + $buildingPower;
    }

    /**
     * Get server age in seconds.
     */
    private static function getServerAge(): int
    {
        $db = \Database\Connection::getInstance();
        // Check earliest planet creation as rough server age proxy
        $stmt = $db->query('SELECT MIN(created_at) as earliest FROM planets');
        $row = $stmt->fetch();
        if (!$row || !$row['earliest']) {
            return 0;
        }
        return time() - strtotime($row['earliest']);
    }

    /**
     * Trigger the Flood emergence event.
     */
    private static function triggerEmergence(int $gameTick): void
    {
        Flood::updatePhase(FloodTypes::PHASE_EMERGE);
        Flood::setTick($gameTick);
        // In future: emit global event, play audio cue
    }

    /**
     * Check if the phase should transition.
     */
    private static function checkPhaseTransition(int $gameTick): void
    {
        $state = Flood::getState();

        switch ($state['phase']) {
            case FloodTypes::PHASE_EMERGE:
                // After emergence, transition to spread after initial wave
                if ($gameTick - $state['emerge_tick'] >= 10) {
                    Flood::updatePhase(FloodTypes::PHASE_SPREAD);
                }
                break;

            case FloodTypes::PHASE_SPREAD:
                // Transition to waves when spread is significant
                $infectedCount = count($state['infected_planets']);
                if ($infectedCount >= 3 && $gameTick - $state['emerge_tick'] >= 30) {
                    Flood::updatePhase(FloodTypes::PHASE_WAVES);
                }
                break;

            case FloodTypes::PHASE_WAVES:
                // Check if rings are being controlled
                $controlledRings = self::getControlledRingCount();
                if ($controlledRings >= 7) {
                    Flood::updatePhase(FloodTypes::PHASE_ENDGAME);
                } elseif ($controlledRings >= 4) {
                    Flood::updatePhase(FloodTypes::PHASE_SUPPRESSION);
                }
                break;

            case FloodTypes::PHASE_SUPPRESSION:
                // Can escalate back to waves if rings lost
                $controlledRings = self::getControlledRingCount();
                if ($controlledRings < 4) {
                    Flood::updatePhase(FloodTypes::PHASE_WAVES);
                }
                // Can end if all rings controlled
                if ($controlledRings >= 7) {
                    Flood::updatePhase(FloodTypes::PHASE_ENDGAME);
                }
                break;
        }
    }

    /**
     * Check for outbreak waves.
     */
    private static function checkWaveOutbreak(int $gameTick): void
    {
        $state = Flood::getState();
        if ($state['phase'] !== FloodTypes::PHASE_WAVES) {
            return;
        }

        $interval = FloodTypes::PHASE_WAVE_INTERVAL[FloodTypes::PHASE_WAVES];
        if (($gameTick - $state['last_wave_tick']) >= $interval) {
            $state = Flood::getState();
            $infected = $state['infected_planets'];

            // Each infected planet launches a wave
            $targets = array_filter($infected, fn($p) => $p['system_id'] !== 1);
            if (!empty($targets)) {
                // In future: dispatch flood attack fleets to target systems
            }

            // Update wave count
            $db = \Database\Connection::getInstance();
            $db->prepare("UPDATE flood_state SET wave_count = wave_count + 1, last_wave_tick = {$gameTick}")
                ->execute();
        }
    }

    /**
     * Spread Flood to nearby planets.
     */
    private static function spread(int $gameTick, int $userId, string $faction): void
    {
        $state = Flood::getState();
        $phase = $state['phase'];

        $spreadChance = FloodTypes::PHASE_SPREAD_CHANCE[$phase] ?? 0;
        if ($spreadChance <= 0) {
            return;
        }

        foreach ($state['infected_planets'] as $infected) {
            // Check if this planet has already spread this tick
            if (rand(0, 9999) / 10000 >= $spreadChance) {
                continue;
            }

            // Find nearby uninfected planets in the same system
            $neighbors = self::findNearbyPlanets($infected['system_id']);
            foreach ($neighbors as $neighbor) {
                // Skip if already infected
                if (in_array($neighbor['planet_id'], array_column($state['infected_planets'], 'planet_id'))) {
                    continue;
                }

                // Infest the planet
                Flood::addInfectedPlanet(
                    $neighbor['planet_id'],
                    $neighbor['system_id'],
                    $neighbor['planet_pos']
                );
                break; // One spread attempt per infected planet per tick
            }
        }
    }

    /**
     * Find nearby planets in or near a system.
     */
    private static function findNearbyPlanets(int $systemId): array
    {
        $db = \Database\Connection::getInstance();
        // Get planets in the same system and adjacent systems
        $sql = "SELECT p.id as planet_id, p.system as system_id, p.position as planet_pos
                FROM planets p
                WHERE p.system IN (?, ?, ?)
                AND p.id NOT IN (SELECT planet_id FROM flood_state, json_each(flood_state.infected_planets) WHERE flood_state.id = 1)";
        // This is simplified; in practice you'd use a different approach for JSON subquery
        $stmt = $db->prepare("SELECT 1 as dummy LIMIT 1");
        $stmt->execute();

        // For now, return empty — needs proper implementation with all planets table
        return [];
    }

    /**
     * Count controlled Halo rings.
     */
    private static function getControlledRingCount(): int
    {
        // Placeholder — to be implemented when ring control data exists
        return 0;
    }
}
