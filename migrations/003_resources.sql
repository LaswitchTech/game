-- Resource system migration
-- metal → supply, crystal → gas, deuterium removed, power becomes primary energy
-- gold added to users table

-- ----== USERS TABLE ----==
-- Add gold column
ALTER TABLE users ADD COLUMN gold INTEGER NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN gold_total INTEGER NOT NULL DEFAULT 0;

-- ----== RESOURCES TABLE ----==
-- SQLite requires table recreation for column renames
CREATE TABLE resources_backup AS SELECT * FROM resources;

DROP TABLE resources;

CREATE TABLE IF NOT EXISTS resources (
    planet_id            INTEGER PRIMARY KEY REFERENCES planets(id),
    supply               INTEGER NOT NULL DEFAULT 500,
    power                INTEGER NOT NULL DEFAULT 0,
    gas                  INTEGER NOT NULL DEFAULT 0,
    power_production     INTEGER NOT NULL DEFAULT 0,
    power_consumption    INTEGER NOT NULL DEFAULT 0,
    supply_storage       INTEGER NOT NULL DEFAULT 5000,
    gas_storage          INTEGER NOT NULL DEFAULT 5000,
    energy_balance       INTEGER NOT NULL DEFAULT 0
);

-- ----== PRODUCTION HISTORY TABLE ----==
CREATE TABLE production_history_backup AS SELECT * FROM production_history;
DROP TABLE production_history;

CREATE TABLE production_history (
    planet_id      INTEGER NOT NULL,
    tick           INTEGER NOT NULL,
    supply         INTEGER NOT NULL,
    power          INTEGER NOT NULL,
    gas            INTEGER NOT NULL,
    power_balance  INTEGER NOT NULL,
    PRIMARY KEY (planet_id, tick)
);

CREATE INDEX idx_production_tick ON production_history(tick);
