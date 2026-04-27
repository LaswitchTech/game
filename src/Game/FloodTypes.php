<?php

namespace Game;

class FloodTypes
{
    // Flood phases
    const PHASE_DORMANT = 'dormant';
    const PHASE_EMERGE = 'emerge';
    const PHASE_SPREAD = 'spread';
    const PHASE_WAVES = 'waves';
    const PHASE_SUPPRESSION = 'suppression';
    const PHASE_ENDGAME = 'endgame';

    const PHASES = [
        self::PHASE_DORMANT,
        self::PHASE_EMERGE,
        self::PHASE_SPREAD,
        self::PHASE_WAVES,
        self::PHASE_SUPPRESSION,
        self::PHASE_ENDGAME,
    ];

    const PHASE_LABELS = [
        self::PHASE_DORMANT => 'Flood dormant — no signs of infection',
        self::PHASE_EMERGE => 'ALERT: Flood emergence detected!',
        self::PHASE_SPREAD => 'ALERT: Flood spreading across the galaxy!',
        self::PHASE_WAVES => 'CRITICAL: Flood assault waves incoming!',
        self::PHASE_SUPPRESSION => 'Rings active — Flood held at bay',
        self::PHASE_ENDGAME => 'VICTORY: The Flood has been defeated',
    ];

    // Trigger conditions
    const TRIGGER_MIN_TICK = 150;
    // Server age trigger: ~2 weeks in seconds (multiplayer)
    const TRIGGER_MIN_SERVER_AGE = 1209600; // 14 days
    // Player power threshold (research level + defenses combined)
    const TRIGGER_MIN_POWER_THRESHOLD = 5000;

    // Spread rates per phase (chance per tick per infected planet)
    const PHASE_SPREAD_CHANCE = [
        self::PHASE_SPREAD => 0.001,
        self::PHASE_WAVES => 0.003,
        self::PHASE_SUPPRESSION => 0.0005,
    ];

    // Wave frequency (ticks between waves) per phase
    const PHASE_WAVE_INTERVAL = [
        self::PHASE_SPREAD => 50,
        self::PHASE_WAVES => 25,
    ];

    // Ring suppression multiplier (each controlled ring reduces spread by this amount)
    const RING_SUPPRESSION_MULTIPLIER = 0.15;
}
