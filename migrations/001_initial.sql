-- Initial schema for OGame-like game
-- Compatible with SQLite and MySQL/MariaDB

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT    NOT NULL UNIQUE,
    email         TEXT    NOT NULL UNIQUE,
    password_hash TEXT    NOT NULL,
    session_token TEXT    DEFAULT NULL,
    session_expires TIMESTAMP DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login    TIMESTAMP DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS planets (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL UNIQUE REFERENCES users(id),
    name            TEXT    NOT NULL DEFAULT 'My Planet',
    system          INTEGER NOT NULL DEFAULT 1,
    position        INTEGER NOT NULL DEFAULT 1,
    primary_planet  INTEGER NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS resources (
    planet_id         INTEGER PRIMARY KEY REFERENCES planets(id),
    metal             INTEGER NOT NULL DEFAULT 500,
    crystal           INTEGER NOT NULL DEFAULT 500,
    deuterium         INTEGER NOT NULL DEFAULT 0,
    energy_production INTEGER NOT NULL DEFAULT 0,
    energy_consumption INTEGER NOT NULL DEFAULT 0,
    metal_storage     INTEGER NOT NULL DEFAULT 5000,
    crystal_storage   INTEGER NOT NULL DEFAULT 5000,
    deuterium_storage INTEGER NOT NULL DEFAULT 5000
);

CREATE TABLE IF NOT EXISTS buildings (
    planet_id      INTEGER NOT NULL REFERENCES planets(id),
    building_key   TEXT    NOT NULL,
    level          INTEGER NOT NULL DEFAULT 0,
    constructed_at TIMESTAMP DEFAULT NULL,
    completed_at   TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (planet_id, building_key)
);

CREATE TABLE IF NOT EXISTS construction_queue (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id    INTEGER NOT NULL REFERENCES planets(id),
    building_key TEXT    NOT NULL,
    target_level INTEGER NOT NULL,
    started_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS technologies (
    planet_id      INTEGER NOT NULL REFERENCES planets(id),
    technology_key TEXT    NOT NULL,
    level          INTEGER NOT NULL DEFAULT 0,
    completed_at   TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (planet_id, technology_key)
);

CREATE TABLE IF NOT EXISTS research_queue (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id       INTEGER NOT NULL REFERENCES planets(id),
    technology_key  TEXT    NOT NULL,
    started_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at    TIMESTAMP DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS ships (
    planet_id  INTEGER NOT NULL REFERENCES planets(id),
    ship_key   TEXT    NOT NULL,
    count      INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (planet_id, ship_key)
);

CREATE TABLE IF NOT EXISTS fleets (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id     INTEGER NOT NULL REFERENCES planets(id),
    ships         TEXT    NOT NULL,
    target_system INTEGER NOT NULL,
    target_planet INTEGER NOT NULL,
    departure_at  TIMESTAMP NOT NULL,
    arrival_at    TIMESTAMP NOT NULL,
    mission_type  TEXT    NOT NULL DEFAULT 'transport',
    status        TEXT    NOT NULL DEFAULT 'in_transit',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS production_history (
    planet_id INTEGER NOT NULL,
    tick      INTEGER NOT NULL,
    metal     INTEGER NOT NULL,
    crystal   INTEGER NOT NULL,
    deuterium INTEGER NOT NULL,
    energy    INTEGER NOT NULL,
    PRIMARY KEY (planet_id, tick)
);

CREATE INDEX IF NOT EXISTS idx_buildings_constructed_at ON buildings(constructed_at) WHERE constructed_at IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_technologies_level ON technologies(technology_key, level);
CREATE INDEX IF NOT EXISTS idx_fleets_status ON fleets(status);
CREATE INDEX IF NOT EXISTS idx_fleets_arrival_at ON fleets(arrival_at);
CREATE INDEX IF NOT EXISTS idx_construction_completed ON construction_queue(completed_at) WHERE completed_at IS NULL;
CREATE INDEX IF NOT EXISTS idx_research_completed ON research_queue(completed_at) WHERE completed_at IS NULL;
