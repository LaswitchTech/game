-- Flood event system
-- Tracks the global Flood state and infected planets

CREATE TABLE IF NOT EXISTS flood_state (
    id              INTEGER PRIMARY KEY CHECK (id = 1),
    phase           TEXT    NOT NULL DEFAULT 'dormant',
    tick            INTEGER NOT NULL DEFAULT 0,
    infected_planets TEXT   NOT NULL DEFAULT '[]',
    wave_count      INTEGER NOT NULL DEFAULT 0,
    last_wave_tick  INTEGER NOT NULL DEFAULT 0,
    emerge_tick     INTEGER NOT NULL DEFAULT 0
);

-- Halo ring control tracking
CREATE TABLE IF NOT EXISTS halo_rings (
    id              INTEGER PRIMARY KEY,
    ring_id         INTEGER NOT NULL UNIQUE CHECK (ring_id BETWEEN 1 AND 7),
    name            TEXT    NOT NULL,
    system_id       INTEGER NOT NULL,
    position        INTEGER NOT NULL,
    discovered      INTEGER NOT NULL DEFAULT 0,
    discovered_by   INTEGER REFERENCES users(id),
    controlled_by   INTEGER REFERENCES users(id),
    control_since   TIMESTAMP DEFAULT NULL,
    flood_infected  INTEGER NOT NULL DEFAULT 0
);

-- Flood events log (for client notifications)
CREATE TABLE IF NOT EXISTS flood_events (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    tick            INTEGER NOT NULL,
    event_type      TEXT    NOT NULL,
    message         TEXT    NOT NULL,
    planet_id       INTEGER DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_flood_events_tick ON flood_events(tick);
CREATE INDEX idx_flood_events_type ON flood_events(event_type);
