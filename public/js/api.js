const Api = {
    baseUrl: '/api',

    async request(method, path, data = null) {
        const options = { method, headers: { 'Content-Type': 'application/json' } };
        if (data) options.body = JSON.stringify(data);
        try {
            const response = await fetch(this.baseUrl + path, options);
            const result = await response.json();
            if (response.status === 401) window.location.href = '/pages/login.html';
            return result;
        } catch (error) { return { error: 'Connection failed' }; }
    },

    async register(username, email, password, faction) { return this.request('POST', '/auth/register', { username, email, password, faction }); },
    async login(username, password) { return this.request('POST', '/auth/login', { username, password }); },
    async logout() { return this.request('POST', '/auth/logout'); },
    async getMe() { return this.request('GET', '/auth/me'); },
    async getPlanet() { return this.request('GET', '/planet'); },
    async buildBuilding(buildingKey) { return this.request('PUT', '/planet/build', { building_key: buildingKey }); },
    async startResearch(technologyKey) { return this.request('PUT', '/planet/research', { technology_key: technologyKey }); },
    async getBuildingTypes() { return this.request('GET', '/planet/building-types'); },
    async getResearchTypes() { return this.request('GET', '/planet/research-types'); },
    async getShipyard() { return this.request('GET', '/shipyard'); },
    async buildShips(ships) { return this.request('PUT', '/shipyard/build', { ships }); },
    async getShipTypes() { return this.request('GET', '/shipyard/ship-types'); },
    async sendFleet(system, planet, type, ships) { return this.request('POST', '/fleet/send', { target_system: system, target_planet: planet, mission_type: type, ships }); },
    async getActiveFleets() { return this.request('GET', '/fleet/active'); },
    async deployToOrbit(ships) { return this.request('PUT', '/fleet/orbit/deploy', { ships }); },
    async recallFromOrbit(ships) { return this.request('PUT', '/fleet/orbit/recall', { ships }); },
    async getOrbitalDefense() { return this.request('GET', '/fleet/orbital-defense'); },
};
