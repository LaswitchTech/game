-- Faction system migration
-- Adds faction support to all relevant tables

-- Add faction to users table
ALTER TABLE users ADD COLUMN faction TEXT DEFAULT NULL;

-- Add faction to planets table
ALTER TABLE planets ADD COLUMN faction TEXT DEFAULT NULL;

-- Add faction to buildings table (drop and recreate for proper constraint)
CREATE TABLE IF NOT EXISTS buildings_backup AS SELECT * FROM buildings;
DROP TABLE buildings;
CREATE TABLE buildings (
    planet_id INTEGER NOT NULL REFERENCES planets(id),
    faction TEXT NOT NULL DEFAULT 'unsc',
    building_key TEXT NOT NULL,
    level INTEGER NOT NULL DEFAULT 0,
    constructed_at TIMESTAMP DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (planet_id, building_key, faction)
);
CREATE INDEX idx_buildings_constructed_at ON buildings(constructed_at) WHERE constructed_at IS NOT NULL;

-- Add faction to construction_queue
CREATE TABLE construction_queue_backup AS SELECT * FROM construction_queue;
DROP TABLE construction_queue;
CREATE TABLE construction_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id INTEGER NOT NULL REFERENCES planets(id),
    faction TEXT NOT NULL DEFAULT 'unsc',
    building_key TEXT NOT NULL,
    target_level INTEGER NOT NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP DEFAULT NULL
);
CREATE INDEX idx_construction_completed ON construction_queue(completed_at) WHERE completed_at IS NULL;

-- Add faction to technologies table
CREATE TABLE technologies_backup AS SELECT * FROM technologies;
DROP TABLE technologies;
CREATE TABLE technologies (
    planet_id INTEGER NOT NULL REFERENCES planets(id),
    faction TEXT NOT NULL DEFAULT 'unsc',
    technology_key TEXT NOT NULL,
    level INTEGER NOT NULL DEFAULT 0,
    completed_at TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (planet_id, technology_key, faction)
);
CREATE INDEX idx_technologies_level ON technologies(technology_key, level);

-- Add faction to research_queue
CREATE TABLE research_queue_backup AS SELECT * FROM research_queue;
DROP TABLE research_queue;
CREATE TABLE research_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    planet_id INTEGER NOT NULL REFERENCES planets(id),
    faction TEXT NOT NULL DEFAULT 'unsc',
    technology_key TEXT NOT NULL,
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP DEFAULT NULL
);
CREATE INDEX idx_research_completed ON research_queue(completed_at) WHERE completed_at IS NULL;

-- Add faction to ships table
CREATE TABLE ships_backup AS SELECT * FROM ships;
DROP TABLE ships;
CREATE TABLE ships (
    planet_id INTEGER NOT NULL REFERENCES planets(id),
    faction TEXT NOT NULL DEFAULT 'unsc',
    ship_key TEXT NOT NULL,
    count INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (planet_id, ship_key, faction)
);

-- Add faction to fleets table
ALTER TABLE fleets ADD COLUMN faction TEXT DEFAULT NULL;
