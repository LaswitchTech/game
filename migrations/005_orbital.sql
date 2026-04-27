-- Orbital ship deployment system
-- Ships can be: on-planet (in shipyard), in-orbit (defending), on-mission

-- Add orbit status to ships table
ALTER TABLE ships ADD COLUMN orbit_status TEXT NOT NULL DEFAULT 'planet' CHECK (orbit_status IN ('planet', 'orbit'));

-- Add orbital defense capability to fleet table
ALTER TABLE fleets ADD COLUMN gas_cost INTEGER NOT NULL DEFAULT 0;

-- Add fleet combat stats tracking
ALTER TABLE fleets ADD COLUMN attack_value INTEGER NOT NULL DEFAULT 0;
ALTER TABLE fleets ADD COLUMN shield_value INTEGER NOT NULL DEFAULT 0;
ALTER TABLE fleets ADD COLUMN armor_value INTEGER NOT NULL DEFAULT 0;

-- Combat log for battle records
CREATE TABLE IF NOT EXISTS combat_log (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    tick            INTEGER NOT NULL,
    attacker_planet_id INTEGER DEFAULT NULL,
    defender_planet_id INTEGER NOT NULL,
    attacker_fleet_id INTEGER DEFAULT NULL,
    winner          TEXT    NOT NULL,
    attacker_loss   TEXT    NOT NULL DEFAULT '{}',
    defender_loss   TEXT    NOT NULL DEFAULT '{}',
    outcome         TEXT    NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_combat_tick ON combat_log(tick);
CREATE INDEX idx_combat_defender ON combat_log(defender_planet_id);
