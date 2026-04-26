# Game Design Document

## Overview

Halo Online — an OGame-style browser-based space strategy game themed around Halo. Core loop: resources, buildings, research, shipyard, fleet movement. Single-player with PHP CLI cron for game simulation, with multiplayer-ready architecture.

## Theme

- **Halo universe** — factions (UNSC, Covenant, Banished), Flood as core endgame threat
- **Goal**: Control all 7 Halo rings to defeat the Flood
- **Flood**: Neutral aggressive AI event that emerges at tick 150 (minimum) or when server reaches ~2-3 weeks, scales with player count/power, spreads to unoccupied planets, pushes players toward cooperative endgame

## Resources (4)

| Resource | Role | Notes |
|----------|------|-------|
| Supply | Core production material | Building, ships, defenses, construction |
| Power | Energy consumption | Buildings consume power. If production < consumption, all production reduced by 75% |
| Gas | Fleet/consumable resource | Fuel for fleet movement (scales with distance from homeworld), research, some defenses |
| Gold | Account-bound premium | Cosmetics, hero rental, acceleration. Rare but obtainable in-game |

### Gold Acquisition
- Forerunner artifact events (discovered during galaxy exploration)
- Flood-related events/missions
- Daily/weekly objectives
- Free tier battle pass
- Rare cosmic anomalies

### Gold Usage (account-bound, not game-bound)
- **Cosmetic upgrades** — building skins, UI themes, ship decals
- **Hero rental** — Master Chief, Arbiter, Cortana, Lord Hood, Prophet of Regret
  - Each hero unlocks features: building queue expansion, resource production boost, extra fleet slots
  - Rented, not purchased — temporary buffs
- **Acceleration** — reduce construction/research timers, speed up fleet travel
- **Active players earn enough** that paying is optional

## Factions

| Faction | Description |
|---------|-------------|
| UNSC | Human coalition, balanced approach |
| Covenant | Alien hierarchy, elite forces |
| Banished | Exiled warriors, aggressive playstyle |

**Phase 1**: Factions are cosmetic only. Later phases can add unique mechanics per faction.

## Population System

- Each planet has a `population` cap that grows with certain buildings (Habitat Domes, etc.)
- Population multiplies production speed and ship capacity
- Keep it simple — no troop micromanagement

## Assets Needed

- **Planets** — 3-4 base types (arid, icy, volcanic, gas giant core)
- **Buildings** — 15 structures, 3 states each (empty, constructing, active)
- **Ships** — 12 types across 3 classes (fighter, cruiser, capital)
- **Tech icons** — 12 icons
- **UI** — bottom dock icons (Citadel, Research, Shipyard, Fleet, Defense)
- **Flood** — infection animation, variant sprites (spore/grunt/pukemaster)
- **Solar system** — 3D dual stars, orbit rings, planet markers
- **Halo rings** — 7 unique rings with visual + gameplay modifier
- **Flood event** — spreading infection visual, ring control interface
- **Heroes** — Master Chief, Arbiter, Cortana, Lord Hood, Prophet of Regret (character art)
- **Forerunner** — structures, ships, tech art

## UI Architecture

```
┌──────────────────────────────────────────────────┐
│  Top bar: resources (supply, power, gas, gold)    │
│  + faction + rank + mini map toggle               │
├──────────────────────────────────────────────────┤
│                                                   │
│              Main view area                       │
│    (solar system / planet view / fleet map)       │
│                                                   │
│                                                   │
├──────────────────────────────────────────────────┤
│  Bottom dock: Citadel | Research | Shipyard |     │
│  Fleet | Defense | Galaxy | Intel                 │
└──────────────────────────────────────────────────┘
```

- Dock items have **notification badges** (construction complete, fleet arrived, flood alert)
- Active dock item highlights with **Halo-style blue glow**
- Dock can **expand into a full panel** on hover/click
- Full-screen optimized, responsive support
- Best played full-screen

## Core Mechanics

### Flood Spread

**Phases**: emerge → spread → waves → ring suppression → endgame

**Trigger conditions**:
- **Hard minimum**: tick 150
- **Alternative**: server age ~2-3 weeks (multiplayer)
- **Strength-based trigger**: Flood emerges when player overall power reaches threshold
  - Evaluated by: total research level, total defenses, total planets controlled
- Multiple triggers, whichever comes first ensures the event is always reachable

**Spread mechanics**:
- Each tick, chance to infect a neighbor of any infected planet
- Infection chance = f(activePlayerCount, globalPowerLevel, gameProgress)
- Scaling: based on **total player population growth rate**, not just count
- Infected planets become Flood Outposts with their own production
- Wave intensity scales every N ticks

**Flood visual/audio cues**:
- Each phase has distinct visual/audio feedback
- Phase changes announced globally to all players

**Endgame**:
- **Single-player**: Find + activate 7 Halo rings → rings fire → Flood destroyed → victory
- **Multiplayer**: Alliance controls 7 rings → alliance members get victory, others get defeat
- Controlled rings slow Flood spread (less effective in multiplayer than single-player control)

### Halo Rings

Each ring provides:

1. **Passive bonus** — stacking multiplier (production, research speed, fleet range)
2. **Active ability** — ring firing mode (the Halo feel)
3. **Flood suppression** — slows Flood spread in system/region
4. **Forerunner access** — controlled ring unlocks Forerunner ships and research tree

**Single-player**: Each ring slows Flood independently. All 7 = victory.
**Multiplayer**: Alliance controls ring. Bonus attributed to alliance members.

### Forerunner Tech Tree

- Separate tech tree unlocked by controlling Halo rings
- Includes Forerunner ships, weapons, defenses
- Exclusive to ring controllers
- Adds strategic value to ring control beyond bonuses

### Live Updates

- Resource ticker: client calculates interpolated production/sec every frame
  - Uses `lastTickData + elapsedSeconds * productionPerTick / 60`
  - Server validates on focus/visibility change
- Action queue: optimistic UI updates
  - Click "build" → API call → optimistic update → countdown timer
  - On success: confirm. On failure: revert with error toast
- Game state version: monotonically increasing `stateVersion` per server response

### Fleet Combat

**Ship classes**:
- **Fighter** — cheap, swarm, fast
- **Cruiser** — balanced
- **Capital** — expensive, slow, powerful

**Combat**: Single "Attack" type (no subtypes for now)

**Fleet range**: Gas-based fuel cost. Distance from homeworld determines gas cost.

**Planet defense**: Combination of planetary defenses + ships in orbit
- Ships must be **deployed in orbit** (not in shipyard or on mission)
- Orbital ships participate in defense
- Ships can be recalled from orbit back to shipyard (costs gas)

### Economy Progression

```
Phase 1 (early): Supply → build mines → more Supply
Phase 2 (mid):   Supply + Power → research → unlock tier 2
Phase 3 (late):  Supply + Power + Gas → fleet → explore + control rings
Phase 4 (end):   All resources + Gold → acceleration → Halo rings → Flood defeat
```

- Gold is account-bound (persists across games/servers)
- Gold accelerates, doesn't replace core gameplay
- Active players earn gold in-game without paying

## Game Flow

1. Register/login
2. Choose faction + starting planet
3. Build mines → produce resources
4. Research tech → unlock buildings/ships
5. Build fleet → explore galaxy (spend Gas)
6. Discover planets → attack/claim/spy
7. Control planets → build defenses + orbital ships
8. Discover Halo ring → find path to it (Forerunner artifacts guide player)
9. Control rings → unlock Forerunner tech
10. Flood intensifies → cooperate or lose

## Implementation Priority

1. **Core loop** — buildings, resources, tick engine (map Supply/Power/Gas/Gold to existing data)
2. **Flood event framework** — spread logic, basic visuals, phase system
3. **Fleet gas consumption** — range mechanics, fuel calculation
4. **Gold system** — account-bound economy, events, hero rental
5. **3D solar system** — orbit visualization
6. **Planet modal** — attack/spy/fleet commands, orbital deployment
7. **Forerunner tech tree** — ring-gated progression
8. **Bottom dock UI** — Halo-style interface
9. **7 Halo rings** — unique mechanics, endgame
10. **Assets + art** — parallel track
11. **Factions** — phase 2 mechanics

## Existing Codebase (OGame-style base)

- PHP 8+ custom Router, SQLite-first
- 15 building types, 12 tech + 12 ships
- OGame-style cost/production/formula system already in place
- Composer PSR-4 autoloader
- REST API with auth middleware
- SPA-style frontend (vanilla JS)
