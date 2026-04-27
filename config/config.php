<?php

// Game configuration constants

define('GAME_NAME', 'Galactic Empire');
define('GAME_VERSION', '1.0.0');

// Tick configuration (seconds between game ticks)
define('TICK_INTERVAL', 60);

// Paths
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('DB_PATH', DATA_PATH . '/game.sqlite');
define('LOG_PATH', DATA_PATH . '/game.log');

// API configuration
define('API_PREFIX', '/api');
define('SESSION_LIFETIME', 86400); // 24 hours

// Resource production tick (resources produced per tick)
define('PRODUCTION_TICK', 3600); // 1 hour of game time = 1 tick (in seconds)

// Game world settings
define('SYSTEM_COUNT', 100);
define('PLANETS_PER_SYSTEM', 10);
