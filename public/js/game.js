const Game = {
    buildingCost(baseCost, level) {
        const mult = Math.pow(1.5, level);
        return { metal: Math.floor(baseCost.metal * mult), crystal: Math.floor(baseCost.crystal * mult), deuterium: Math.floor(baseCost.deuterium * mult) };
    },

    buildingProduction(base, level) {
        if (level < 1) return 0;
        return Math.floor(base * level * level * 0.1);
    },

    energyProduction(type, level) {
        return (type.base_energy_production || 0) * level * level * 0.1;
    },

    storageCapacity(type, level) {
        const total = {};
        for (const [key, bonus] of Object.entries(type.storage_bonus || {})) {
            total[key] = bonus * level;
        }
        return total;
    },

    constructionTime(type, targetLevel) {
        if (type.base_time <= 0) return 0;
        return type.base_time * targetLevel * targetLevel;
    },

    researchCost(baseCost, currentLevel) {
        return {
            metal: Math.floor(baseCost.metal * Math.pow(currentLevel + 1, 3) * 1.5),
            crystal: Math.floor(baseCost.crystal * Math.pow(currentLevel + 1, 3) * 1.5),
            deuterium: Math.floor(baseCost.deuterium * Math.pow(currentLevel + 1, 3) * 1.5),
        };
    },

    researchTime(type, currentLevel) {
        return type.base_time * (currentLevel + 1);
    },

    formatTime(seconds) {
        if (seconds <= 0) return 'Instant';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        let parts = [];
        if (h > 0) parts.push(h + 'h');
        if (m > 0) parts.push(m + 'm');
        if (s > 0 || parts.length === 0) parts.push(s + 's');
        return parts.join(' ');
    },

    formatResource(value) {
        if (value >= 1e9) return (value / 1e9).toFixed(1) + 'B';
        if (value >= 1e6) return (value / 1e6).toFixed(1) + 'M';
        if (value >= 1e3) return (value / 1e3).toFixed(1) + 'K';
        return Math.floor(value).toString();
    },
};
