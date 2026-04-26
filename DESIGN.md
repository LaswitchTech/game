# Game Design Document

## Overview

Halo Online — an OGame-style browser-based space strategy game themed around Halo. Core loop: resources, buildings, research, shipyard, fleet movement. Single-player with PHP CLI cron for game simulation, with multiplayer-ready architecture.

## Theme

- **Halo universe** — factions, Flood as core endgame threat
- **Goal**: Control all 7 Halo rings to defeat the Flood
- **Flood**: Neutral aggressive AI event that starts after N ticks, scales with player count/power, spreads to unoccupied planets, pushes players toward cooperative endgame

## Resources (4)

| Resource | Role | Notes |
|----------|------|-------|
| Supply | Core production | Used for buildings, ships, basic operations |
| Power | Energy infrastructure | Fuel/power for operations |
| Gas | Advanced tech | Rare resource for tier 2/3 buildings and ships |
| Gold | Premium/currency | Obtainable in-game (mining anomalies, Flood missions) — not just paid |

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
- Starts after configurable tick (e.g., tick 100 or when 3rd planet discovered)
- Each tick, chance to infect a neighbor of any infected planet
- Infection chance = f(activePlayerCount, globalPowerLevel, gameProgress)
- Scaling: based on **total player population growth rate**, not just count
- Infected planets become Flood Outposts with their own production
- Wave intensity scales every N ticks
- Ring control: requires ships at system + Halo Activation tech, 7 rings = endgame

### Live Updates
- Resource ticker: client calculates interpolated production/sec every frame
  - Uses `lastTickData + elapsedSeconds * productionPerTick / 60`
  - Server validates on focus/visibility change
- Action queue: optimistic UI updates
  - Click "build" → API call → optimistic update → countdown timer
  - On success: confirm. On failure: revert with error toast
- Game state version: monotonically increasing `stateVersion` per server response

## Game Flow

1. Register/login
2. Choose starting planet
3. Build mines → produce resources
4. Research tech → unlock buildings/ships
5. Build fleet → explore galaxy
6. Discover planets → attack/claim/spy
7. Control planets → build defenses
8. Research Halo Activation → control rings
9. Flood intensifies → cooperate or lose

## Implementation Priority

1. Core loop (buildings, resources, tick engine)
2. Flood event framework (spread logic, basic visuals)
3. 3D solar system
4. Planet modal (attack/spy/fleet commands)
5. Bottom dock UI
6. Assets + art (parallel track)
7. Ring control + endgame

## Existing Codebase (OGame-style base)

- PHP 8+ custom Router, SQLite-first
- 15 building types, 12 tech + 12 ships
- OGame-style cost/production/formula system already in place
- Composer PSR-4 autoloader
- REST API with auth middleware
- SPA-style frontend (vanilla JS)
