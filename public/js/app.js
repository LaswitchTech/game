const App = {
    state: { user: null, planet: null, faction: null, currentTab: 'planet', buildingTypes: {}, researchTypes: {}, shipTypes: {} },

    async init(tab = 'planet') {
        this.state.currentTab = tab;
        this.state.user = await Api.getMe();
        if (!this.state.user) return window.location.href = '/pages/login.html';

        this.state.faction = this.state.user.faction || 'unsc';
        document.body.classList.add('faction-' + this.state.faction);
        document.getElementById('faction-badge').textContent = this.state.faction.toUpperCase();

        this.state.planet = await Api.getPlanet();
        this.state.buildingTypes = await Api.getBuildingTypes();
        this.state.researchTypes = await Api.getResearchTypes();
        this.state.shipTypes = await Api.getShipTypes();

        this.updateResources();
        this.updatePlanet();

        if (tab === 'planet') this.renderPlanet();
        if (tab === 'research') this.renderResearch();
        if (tab === 'shipyard') this.renderShipyard();
        if (tab === 'fleet') this.renderFleet();

        // Auto-refresh
        setInterval(async () => {
            this.state.planet = await Api.getPlanet();
            this.state.buildingTypes = await Api.getBuildingTypes();
            this.state.researchTypes = await Api.getResearchTypes();
            this.state.shipTypes = await Api.getShipTypes();
            this.updateResources();
            this.updatePlanet();
            if (tab === 'planet') this.renderPlanet();
            if (tab === 'research') this.renderResearch();
            if (tab === 'shipyard') this.renderShipyard();
            if (tab === 'fleet') this.renderFleet();
        }, 10000);
    },

    updateResources() {
        if (!this.state.planet) return;
        const r = this.state.planet.resources;
        document.getElementById('res-supply').textContent = Game.formatResource(r.supply);
        document.getElementById('res-gas').textContent = Game.formatResource(r.gas);
        document.getElementById('res-supply-panel').textContent = Game.formatResource(r.supply);
        document.getElementById('res-gas-panel').textContent = Game.formatResource(r.gas);
    },

    updatePlanet() {
        if (!this.state.planet) return;
        document.getElementById('planet-name').textContent = this.state.planet.planet.name;
        document.getElementById('planet-system').textContent = this.state.planet.planet.system;
        document.getElementById('planet-position').textContent = this.state.planet.planet.position;
        document.getElementById('energy-balance').textContent = (this.state.planet.energy_balance >= 0 ? '+' : '') + this.state.planet.energy_balance;
        document.getElementById('energy-balance').style.color = this.state.planet.energy_balance >= 0 ? 'var(--success)' : 'var(--danger)';
    },

    renderPlanet() {
        const grid = document.getElementById('building-grid');
        if (!grid) return;
        const types = this.state.buildingTypes;
        grid.innerHTML = '';
        for (const [key, type] of Object.entries(types)) {
            const level = this.state.planet.buildings.find(b => b.key === key)?.level || 0;
            const cell = document.createElement('div');
            cell.className = 'building-cell' + (type.required_tech && !type.prereq_met ? ' locked' : '');
            cell.innerHTML = `
                <div class="building-name">${type.name}</div>
                <div class="building-level">${level}</div>
                ${type.base_energy_production ? `<div class="building-prod">+${Game.energyProduction(type, level)} energy</div>` : ''}
                ${type.energy_consumption > 0 ? `<div class="building-consume">-${type.energy_consumption} energy</div>` : ''}
            `;
            if (!cell.classList.contains('locked')) {
                cell.onclick = () => this.showBuildingModal(key);
            }
            grid.appendChild(cell);
        }

        // Construction queue
        const cq = document.getElementById('construction-queue');
        if (this.state.planet.construction_queue && this.state.planet.construction_queue.length > 0) {
            cq.innerHTML = this.state.planet.construction_queue.map(q => `<div class="queue-item">${q.key} → Lv.${q.target_level} (<span id="cq-${q.key}">${Game.formatTime(q.time_remaining)}</span>)</div>`).join('');
        } else {
            cq.innerHTML = '<p class="empty-queue">No buildings under construction</p>';
        }

        // Research queue
        const rq = document.getElementById('research-queue');
        if (this.state.planet.research_queue && this.state.planet.research_queue.length > 0) {
            rq.innerHTML = this.state.planet.research_queue.map(q => `<div class="queue-item">${q.technology_key} (<span id="rq-${q.technology_key}">${Game.formatTime(q.time_remaining)}</span>)</div>`).join('');
        } else {
            rq.innerHTML = '<p class="empty-queue">No research in progress</p>';
        }

        document.getElementById('building-title').textContent = this.state.faction === 'unsc' ? 'UNSC Buildings' : 'Covenant Buildings';
    },

    showBuildingModal(key) {
        const type = this.state.buildingTypes[key];
        const level = this.state.planet.buildings.find(b => b.key === key)?.level || 0;
        const cost = Game.buildingCost(type.cost, level);
        document.getElementById('modal-building-name').textContent = type.name;
        document.getElementById('modal-building-desc').textContent = type.description;
        document.getElementById('modal-building-cost').innerHTML =
            `Cost: ${Game.formatResource(cost.supply)} supply, ${Game.formatResource(cost.gas)} gas<br>Time: ${Game.formatTime(type.base_time)}`;
        document.getElementById('modal-build-btn').onclick = () => this.buildBuilding(key);
        document.getElementById('building-modal').style.display = 'flex';
    },

    async buildBuilding(key) {
        const result = await Api.buildBuilding(key);
        document.getElementById('building-modal').style.display = 'none';
        alert(result.message || result.error);
        this.state.planet = await Api.getPlanet();
        this.updatePlanet();
        this.renderPlanet();
    },

    renderResearch() {
        const list = document.getElementById('research-list');
        if (!list) return;

        // Shared tech
        let html = '<h3 style="color:var(--accent)">Shared Technologies</h3>';
        for (const [key, type] of Object.entries(this.state.researchTypes)) {
            if (type.base_time === undefined) continue; // Not a tech
            html += this.renderTechNode(key, type);
        }

        // Faction tech
        html += '<h3 style="color:var(--accent); margin-top:1rem;">' + (this.state.faction === 'unsc' ? 'UNSC' : 'Covenant') + ' Technologies</h3>';
        for (const [key, type] of Object.entries(this.state.researchTypes)) {
            if (type.base_time === undefined) continue;
            html += this.renderTechNode(key, type);
        }
        list.innerHTML = html;

        // Update research queue progress
        setInterval(async () => {
            this.state.planet = await Api.getPlanet();
            this.updatePlanet();
        }, 10000);
    },

    renderTechNode(key, type) {
        const level = type.current_level || 0;
        const cost = type.cost;
        const time = type.research_time || 0;
        const researched = level > 0;
        const available = type.prereq_met && !researched;
        const locked = !type.prereq_met && !researched;
        const cls = researched ? 'researched' : (available ? 'available' : 'locked');

        let prereqHtml = '';
        if (!type.prereq_met && type.prerequisites?.length > 0) {
            prereqHtml = '<div class="tech-prereq">Requires: ' + type.prerequisites.join(', ') + '</div>';
        }

        return `
            <div class="tech-node ${cls}">
                <div class="tech-name">${type.name}</div>
                <div class="tech-desc">${type.description}</div>
                <div class="tech-cost">Cost: ${Game.formatResource(cost.supply)} supply, ${Game.formatResource(cost.gas)} gas</div>
                ${type.effects?.length ? `<div class="tech-cost">Effects: ${type.effects.join(', ')}</div>` : ''}
                <div class="tech-level">${type.prereq_met ? (researched ? `Level ${level} ✓` : 'Available to research') : prereqHtml}</div>
                ${researched ? '' : `<button class="btn btn-secondary" style="margin-top:0.3rem; font-size:0.75rem;" onclick="App.startResearch('${key}')" ${!type.prereq_met ? 'disabled' : ''}>Research (${Game.formatTime(time)})</button>`}
            </div>
        `;
    },

    async startResearch(key) {
        const result = await Api.startResearch(key);
        if (result.message) {
            this.state.planet = await Api.getPlanet();
            this.state.researchTypes = await Api.getResearchTypes();
            this.renderResearch();
        } else {
            alert(result.error);
        }
    },

    renderShipyard() {
        // Fleet list
        const fleetList = document.getElementById('fleet-list');
        if (fleetList) {
            if (this.state.planet.ships.length > 0) {
                fleetList.innerHTML = this.state.planet.ships.map(s => `<div class="ship-row"><span class="ship-name">${s.key}</span><span class="ship-count">${s.count}</span></div>`).join('');
            } else {
                fleetList.innerHTML = '<p class="empty-queue">No ships yet</p>';
            }
        }

        // Ship types
        const typesGrid = document.getElementById('ship-types');
        if (typesGrid) {
            typesGrid.innerHTML = '';
            for (const [key, ship] of Object.entries(this.state.shipTypes)) {
                const card = document.createElement('div');
                card.className = 'ship-type-card';
                card.innerHTML = `
                    <div class="ship-header">
                        <span class="ship-name">${ship.name}</span>
                        <span class="ship-count">${ship.count}</span>
                    </div>
                    <div class="ship-desc">${ship.description}</div>
                    <div class="ship-cost">Cost: ${Game.formatResource(ship.cost.supply)} supply, ${Game.formatResource(ship.cost.gas)} gas</div>
                    <div class="ship-stats">
                        <div class="stat stat-attack">⚔ ${ship.properties.attack}</div>
                        <div class="stat stat-shield">🛡 ${ship.properties.shield}</div>
                        <div class="stat stat-armor">🔒 ${ship.properties.armor}</div>
                    </div>
                    <div class="ship-build-section">
                        <span style="font-size:0.75rem; color:var(--text-secondary);">Qty:</span>
                        <input type="number" id="build-${key}" min="0" value="0" onchange="App.validateShipBuild('${key}', this.value)">
                        <button class="btn btn-secondary" style="font-size:0.7rem;" onclick="App.buildShip('${key}')" ${!ship.prereq_met ? 'disabled' : ''}>Build</button>
                    </div>
                    <div class="ship-locked" style="${ship.prereq_met ? 'display:none' : ''}">Requires: ${ship.required_tech}</div>
                `;
                typesGrid.appendChild(card);
            }
        }
    },

    validateShipBuild(key, value) {
        document.getElementById('build-' + key).value = Math.max(0, parseInt(value) || 0);
    },

    async buildShip(key) {
        const count = parseInt(document.getElementById('build-' + key).value) || 1;
        const result = await Api.buildShips({ [key]: count });
        if (result.success) {
            this.state.planet = await Api.getPlanet();
            this.state.shipTypes = await Api.getShipTypes();
            this.updateResources();
            this.renderShipyard();
        } else {
            alert(result.error);
        }
    },

    renderFleet() {
        const myShips = document.getElementById('my-ships');
        if (myShips) {
            const planetShips = this.state.planet.ships.filter(s => s.orbit_status === 'planet' || !s.orbit_status);
            if (planetShips.length > 0) {
                myShips.innerHTML = planetShips.map(s =>
                    `<div class="my-ship-row"><span>${s.key}</span><span>x${s.count}</span><input type="checkbox" id="dispatch-${s.key}"></div>`
                ).join('');
            } else {
                myShips.innerHTML = '<p class="empty-queue">No ships to dispatch</p>';
            }
        }

        const missions = document.getElementById('active-missions');
        if (missions) {
            if (this.state.planet.fleets?.length > 0) {
                missions.innerHTML = this.state.planet.fleets.map(f =>
                    `<div class="mission-item"><div class="mission-type">${f.mission_type}</div><div class="mission-coords">→ System ${f.target_system}, Planet ${f.target_planet}</div><div class="mission-time">Arrives: ${f.arrival_at}</div></div>`
                ).join('');
            } else {
                missions.innerHTML = '<p class="empty-queue">No active missions</p>';
            }
        }

        const dispatchBtn = document.getElementById('dispatch-btn');
        if (dispatchBtn) {
            dispatchBtn.onclick = () => this.dispatchFleet();
        }

        // Orbital defense
        this.renderOrbitalDefense();
    },

    async renderOrbitalDefense() {
        const defense = await Api.getOrbitalDefense();
        if (!defense) return;

        const countEl = document.getElementById('orbit-count');
        const capacityEl = document.getElementById('orbit-capacity');
        const attackEl = document.getElementById('orbit-attack');
        const shieldEl = document.getElementById('orbit-shield');
        const armorEl = document.getElementById('orbit-armor');
        if (countEl) countEl.textContent = defense.orbit_ships;
        if (capacityEl) capacityEl.textContent = defense.orbit_capacity;
        if (attackEl) attackEl.textContent = defense.attack;
        if (shieldEl) shieldEl.textContent = defense.shield;
        if (armorEl) armorEl.textContent = defense.armor;

        // List ships in orbit vs on planet
        const listEl = document.getElementById('orbit-ship-list');
        if (!listEl) return;
        const planetShips = this.state.planet.ships || [];
        const orbitShips = [];
        const planetShipsList = [];
        for (const s of planetShips) {
            // We'll show all ships with a selector
        }
        let html = '';
        for (const s of planetShips) {
            html += `<div class="orbit-ship-row"><span>${s.key} (x${s.count})</span><input type="number" class="orbit-qty" data-key="${s.key}" data-max="${s.count}" min="0" max="${s.count}" value="0"></div>`;
        }
        if (planetShips.length === 0) {
            html = '<p class="empty-queue">No ships</p>';
        }
        listEl.innerHTML = html;
    },

    openOrbitalPanel(mode) {
        const listEl = document.getElementById('orbit-ship-list');
        const actionsEl = listEl.parentElement.querySelector('.orbital-actions');
        if (!listEl) return;

        const planetShips = this.state.planet.ships || [];
        // Filter by orbit_status if available from API
        const ships = mode === 'deploy'
            ? planetShips.filter(s => s.orbit_status === 'planet' || !s.orbit_status)
            : planetShips.filter(s => s.orbit_status === 'orbit');
        let html = `<label>Select ships to ${mode}:</label>`;
        for (const s of ships) {
            if (mode === 'deploy') {
                html += `<div class="orbit-ship-row"><span>${s.key} (on planet: x${s.count})</span><input type="number" class="orbit-qty" data-key="${s.key}" data-max="${s.count}" min="0" max="${s.count}" value="0"></div>`;
            } else {
                html += `<div class="orbit-ship-row"><span>${s.key} (in orbit: x${s.count})</span><input type="number" class="orbit-qty" data-key="${s.key}" data-max="${s.count}" min="0" value="0"></div>`;
            }
        }
        listEl.innerHTML = html;
        listEl.classList.add('orbital-panel');

        // Replace actions with confirm/cancel
        if (actionsEl) {
            actionsEl.innerHTML = `
                <button class="btn btn-primary" onclick="App.confirmOrbital('${mode}')">Confirm</button>
                <button class="btn btn-secondary" onclick="App.renderOrbitalDefense()">Cancel</button>
            `;
        }
    },

    async confirmOrbital(mode) {
        const inputs = document.querySelectorAll('#orbit-ship-list .orbit-qty');
        const ships = {};
        for (const input of inputs) {
            const val = parseInt(input.value) || 0;
            if (val > 0) ships[input.dataset.key] = val;
        }
        if (Object.keys(ships).length === 0) { alert('Select at least one ship'); return; }

        const fn = (mode === 'deploy') ? Api.deployToOrbit : Api.recallFromOrbit;
        const result = await fn(ships);
        if (result.success) {
            this.state.planet = await Api.getPlanet();
            this.updateResources();
            this.renderFleet();
        } else {
            alert(result.error);
        }
    },

    async dispatchFleet() {
        const system = parseInt(document.getElementById('target-system').value) || 1;
        const planet = parseInt(document.getElementById('target-planet').value) || 1;
        const type = document.getElementById('mission-type').value;
        const ships = {};
        for (const s of this.state.planet.ships) {
            if (document.getElementById('dispatch-' + s.key)?.checked) {
                ships[s.key] = s.count;
            }
        }

        const result = await Api.sendFleet(system, planet, type, ships);
        if (result.success) {
            this.state.planet = await Api.getPlanet();
            this.updatePlanet();
            this.renderFleet();
        } else {
            alert(result.error);
        }
    },
};
