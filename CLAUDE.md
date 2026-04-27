# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Galactic Empire — an OGame-style browser-based space strategy game. Core loop: resources, buildings, research, shipyard, fleet movement. Single-player with PHP CLI cron for game simulation.

## Tech Stack

- **Backend:** PHP 8+ (no framework, custom Router)
- **Frontend:** HTML, CSS, vanilla JavaScript (SPA-style)
- **Database:** SQLite (with MariaDB/MySQL compatibility — use standard SQL types)
- **Autoloading:** Composer PSR-4

## Directory Structure

```
game/
├── cli/
│   └── game-loop.php          # CLI cron script — runs game tick
├── config/
│   └── config.php             # Game constants (tick interval, paths, etc.)
├── migrations/
│   └── 001_initial.sql        # Full DDL schema
├── public/                    # Apache DocumentRoot
│   ├── api/
│   │   └── index.php          # REST API entry point (routes /api/*)
│   ├── css/                   # global.css, planet.css, shipyard.css, research.css, fleet.css, auth.css
│   ├── js/
│   │   ├── api.js             # HTTP client wrapper
│   │   ├── app.js             # SPA router + state manager
│   │   ├── game.js            # Client-side formula engine
│   │   └── ui.js              # DOM rendering helpers
│   ├── pages/
│   │   ├── login.html
│   │   ├── planet.html        # Planet overview + building matrix
│   │   ├── shipyard.html
│   │   ├── research.html
│   │   └── fleet.html
│   ├── index.php              # Web page router
│   └── .htaccess
├── src/
│   ├── Api/
│   │   ├── Controller/        # Auth, Planet, Shipyard, Fleet controllers
│   │   ├── Middleware/
│   │   │   └── AuthMiddleware.php
│   │   └── Router.php         # Custom request router (~60 lines)
│   ├── Database/
│   │   ├── Connection.php     # PDO factory (SQLite-first)
│   │   └── Migration.php      # Migration runner
│   ├── Game/
│   │   ├── BuildingTypes.php  # Building definitions (costs, production, energy)
│   │   ├── Engine.php         # Core tick engine (production, construction, research)
│   │   ├── Formulas.php       # OGame-style formulas (costs, production, combat)
│   │   ├── Resources.php      # Resource type constants
│   │   └── TechnologyTypes.php # Tech + ship definitions
│   ├── Model/
│   │   ├── Building.php       # Building CRUD
│   │   ├── Mission.php        # Fleet mission CRUD
│   │   ├── Planet.php         # Planet CRUD
│   │   ├── Ship.php           # Ship CRUD
│   │   ├── Technology.php     # Technology CRUD
│   │   └── User.php           # User CRUD
│   └── Service/
│       ├── AuthService.php    # Registration, login, session
│       ├── FleetService.php   # Fleet dispatch/active missions
│       └── GameTickService.php # Single-player tick logic
├── composer.json              # PSR-4 autoloader
└── data/                      # Runtime data (DB, logs) — gitignored
```

## API Endpoints

All endpoints return JSON. Auth endpoints set PHP session cookies.

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/auth/register` | Create account |
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/me` | Current user |
| GET | `/api/planet` | Full planet state |
| PUT | `/api/planet/build` | Start/upgrade building |
| PUT | `/api/planet/research` | Start research |
| GET | `/api/planet/building-types` | Building definitions + costs |
| GET | `/api/planet/research-types` | Technology definitions + costs |
| GET | `/api/shipyard` | Current fleet on planet |
| PUT | `/api/shipyard/build` | Build ships |
| GET | `/api/shipyard/ship-types` | Ship definitions + costs |
| POST | `/api/fleet/send` | Dispatch fleet |
| GET | `/api/fleet/active` | Active missions |

## Key OGame Formulas

- **Building cost:** `base_cost * 1.5^level` per level
- **Building production:** `base_production * level^2 * 0.1` per tick
- **Construction time:** `base_time * target_level^2` seconds
- **Research cost:** `base_cost * (level+1)^3 * 1.5`
- **Research time:** `base_time * (level+1)` seconds
- **Energy:** If production < consumption, resource production reduced by 75%

## Development Commands

```bash
# Install dependencies
composer install

# Run game tick manually
php cli/game-loop.php

# Start local server for testing
php -S localhost:8080 -t public/

# Check database schema
sqlite3 data/game.sqlite ".tables"
```

## Game Data Files

- `src/Game/BuildingTypes.php` — All 15 building types (metal_mine, crystal_mine, deuterium_synthesizer, solar_plant, fusion_reactor, storage buildings, research_lab, shipyard, robotics_workshop, ai_computer, tech buildings)
- `src/Game/TechnologyTypes.php` — 12 technology types + 12 ship types
- `src/Game/Formulas.php` — All calculation formulas (add new formulas here)
- `src/Game/Engine.php` — Core tick engine (resource production, construction completion, research completion, fleet missions)

## Adding New Content

**New building:** Add entry to `BuildingTypes::$buildings` with name, description, base_cost, base_production, base_time, energy_consumption, required_tech.

**New technology:** Add entry to `TechnologyTypes::$technologies` with name, description, base_cost, base_time, prerequisites, effects.

**New ship:** Add entry to `TechnologyTypes::$ships` with name, description, base_cost, build_time, required_tech, properties (capacity, speed, attack, shield, armor).

## Deployment Notes

- Set Apache DocumentRoot to `public/`
- Ensure `data/` is writable by the web server
- Add cron: `* * * * * /usr/bin/php /path/to/game/cli/game-loop.php`
- For MySQL migration: change Connection.php DSN, ensure indexes use MySQL syntax
