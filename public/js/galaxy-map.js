/**
 * Galaxy map — 2D grid view of all star systems for fleet navigation.
 */
class GalaxyMap {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.gridData = null;
        this.paths = null;
        this.bounds = null;

        // Viewport
        this.zoom = 1;
        this.panX = 0;
        this.panY = 0;
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.dragPanStartX = 0;
        this.dragPanStartY = 0;

        // Grid cell sizing
        this.cellSize = 60;
        this.gridCells = []; // {key, x, y, planets}
        this.hoveredCell = null;
        this.selectedCell = null;
        this.pathHighlight = null; // [fromKey, toKey] for fleet path

        // Colors
        this.colors = {
            bg: '#0a1628',
            gridLine: '#1a3a5c',
            playerPlanet: '#2ecc71',
            playerPlanetGlow: 'rgba(46,204,113,0.3)',
            enemyPlanet: '#e74c3c',
            enemyPlanetGlow: 'rgba(231,76,60,0.3)',
            unexplored: '#333355',
            pathLine: '#4488aa',
            pathHighlight: '#f1c40f',
            textPrimary: '#e0e0e0',
            textSecondary: '#a0a0a0',
            accent: '#14b8a6',
            cellHover: 'rgba(20,184,166,0.15)',
        };
    }

    init() {
        this.canvas = document.getElementById('galaxy-canvas');
        this.ctx = this.canvas.getContext('2d');
        this.updateCanvasSize();

        // Event listeners
        this.canvas.addEventListener('mousedown', (e) => this.onMouseDown(e));
        this.canvas.addEventListener('mousemove', (e) => this.onMouseMove(e));
        this.canvas.addEventListener('mouseup', (e) => this.onMouseUp(e));
        this.canvas.addEventListener('wheel', (e) => this.onWheel(e));
        this.canvas.addEventListener('click', (e) => this.onClick(e));
        window.addEventListener('resize', () => this.updateCanvasSize());

        // Load galaxy data
        this.loadGalaxy();

        // Start render loop
        this.render();
    }

    updateCanvasSize() {
        const w = window.innerWidth;
        const h = window.innerHeight - 50;
        this.canvas.width = w;
        this.canvas.height = h;
        this.canvas.style.width = w + 'px';
        this.canvas.style.height = h + 'px';
    }

    async loadGalaxy() {
        const data = await Api.getGalaxyMap();
        if (!data || !data.grid) {
            document.getElementById('galaxy-loading').textContent = 'Failed to load galaxy';
            return;
        }

        document.getElementById('galaxy-loading').style.opacity = '0';

        this.gridData = data.grid;
        this.paths = data.paths;
        this.bounds = data.bounds;

        // Center the view
        const gridW = (this.bounds.max_sys - this.bounds.min_sys + 1);
        const gridH = (this.bounds.max_pos - this.bounds.min_pos + 1);
        this.cellSize = Math.min(80, Math.max(40, Math.min(window.innerWidth / (gridW + 4), window.innerHeight / (gridH + 6))));

        this.panX = window.innerWidth / 2 - ((gridW + 1) * this.cellSize) / 2;
        this.panY = window.innerHeight / 2 - ((gridH + 1) * this.cellSize) / 2;
        this.zoom = Math.min(window.innerWidth, window.innerHeight) / (Math.max(gridW, gridH) * this.cellSize);
        this.zoom = Math.max(0.3, Math.min(1, this.zoom));

        // Compute cell positions
        this.computeGridCells();
    }

    computeGridCells() {
        if (!this.gridData) return;
        this.gridCells = [];
        for (const group of this.gridData) {
            const key = group.system + ',' + group.position;
            const x = (group.system - this.bounds.min_sys) * this.cellSize + this.cellSize / 2;
            const y = (group.position - this.bounds.min_pos) * this.cellSize + this.cellSize / 2;
            this.gridCells.push({ ...group, key, x, y, size: this.cellSize });
        }
    }

    // --- Drawing ---

    render() {
        const ctx = this.ctx;
        const w = this.canvas.width;
        const h = this.canvas.height;

        // Clear
        ctx.fillStyle = this.colors.bg;
        ctx.fillRect(0, 0, w, h);

        // Grid background
        ctx.save();
        ctx.translate(this.panX, this.panY);
        ctx.scale(this.zoom, this.zoom);

        // Draw grid lines (subtle)
        if (this.gridData) {
            ctx.strokeStyle = this.colors.gridLine;
            ctx.lineWidth = 0.5;
            ctx.globalAlpha = 0.3;
            for (let sys = this.bounds.min_sys; sys <= this.bounds.max_sys; sys++) {
                const x = (sys - this.bounds.min_sys + 0.5) * this.cellSize;
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, (this.bounds.max_pos - this.bounds.min_pos + 1) * this.cellSize);
                ctx.stroke();
            }
            for (let pos = this.bounds.min_pos; pos <= this.bounds.max_pos; pos++) {
                const y = (pos - this.bounds.min_pos + 0.5) * this.cellSize;
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo((this.bounds.max_sys - this.bounds.min_sys + 1) * this.cellSize, y);
                ctx.stroke();
            }
            ctx.globalAlpha = 1;
        }

        // Draw paths between systems
        if (this.paths) {
            for (const [fromKey, toKey] of this.paths) {
                const from = this.getCellByKey(fromKey);
                const to = this.getCellByKey(toKey);
                if (!from || !to) continue;

                const isHighlighted = this.pathHighlight &&
                    ((this.pathHighlight[0] === fromKey && this.pathHighlight[1] === toKey) ||
                     (this.pathHighlight[0] === toKey && this.pathHighlight[1] === fromKey));

                ctx.strokeStyle = isHighlighted ? this.colors.pathHighlight : this.colors.pathLine;
                ctx.lineWidth = isHighlighted ? 2.5 : 1;
                ctx.globalAlpha = isHighlighted ? 1 : 0.4;

                if (isHighlighted) {
                    // Dashed line for highlighted path
                    ctx.setLineDash([6, 4]);
                }

                ctx.beginPath();
                ctx.moveTo(from.x, from.y);
                ctx.lineTo(to.x, to.y);
                ctx.stroke();

                ctx.setLineDash([]);
                ctx.globalAlpha = 1;
            }
        }

        // Draw systems
        if (this.gridData) {
            for (const cell of this.gridCells) {
                this.drawCell(ctx, cell);
            }
        }

        // Draw fleet path preview (if selected)
        if (this.selectedCell && this.pathHighlight) {
            this.drawFleetPathPreview(ctx);
        }

        ctx.restore();

        // Draw hint
        this.drawHint(ctx, w, h);

        requestAnimationFrame(() => this.render());
    }

    drawCell(ctx, cell) {
        const cx = cell.x;
        const cy = cell.y;
        const r = this.cellSize * 0.4;
        const isHovered = this.hoveredCell && this.hoveredCell.key === cell.key;
        const isSelected = this.selectedCell && this.selectedCell.key === cell.key;

        // Cell background
        if (isHovered || isSelected) {
            ctx.fillStyle = this.colors.cellHover;
            ctx.beginPath();
            ctx.arc(cx, cy, r + 8, 0, Math.PI * 2);
            ctx.fill();
        }

        // Draw planets in this system
        for (const planet of cell.planets) {
            const offset = this.planetOffset(cell.planets, planet);
            const px = cx + offset.x;
            const py = cy + offset.y;
            const pr = Math.max(4, r * 0.5);

            // Glow
            let glowColor;
            if (planet.is_player) glowColor = this.colors.playerPlanetGlow;
            else if (planet.faction !== 'unsc') glowColor = this.colors.enemyPlanetGlow;
            else glowColor = 'rgba(100,100,200,0.3)';

            const glow = ctx.createRadialGradient(px, py, 0, px, py, pr * 3);
            glow.addColorStop(0, glowColor);
            glow.addColorStop(1, 'transparent');
            ctx.fillStyle = glow;
            ctx.beginPath();
            ctx.arc(px, py, pr * 3, 0, Math.PI * 2);
            ctx.fill();

            // Planet dot
            let color;
            if (planet.is_player) color = this.colors.playerPlanet;
            else if (planet.faction !== 'unsc') color = this.colors.enemyPlanet;
            else color = '#6666cc';

            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(px, py, pr, 0, Math.PI * 2);
            ctx.fill();

            // Label
            ctx.fillStyle = this.colors.textPrimary;
            ctx.font = `${Math.max(9, Math.round(10 * this.zoom))}px Segoe UI`;
            ctx.textAlign = 'center';
            ctx.fillText(planet.name, px, py - pr - 4);
        }

        // System label
        if (!cell.planets.some(p => p.is_player)) {
            ctx.fillStyle = this.colors.textSecondary;
            ctx.font = `${Math.max(8, Math.round(9 * this.zoom))}px Segoe UI`;
            ctx.textAlign = 'center';
            ctx.fillText(`S${cell.system} P${cell.position}`, cx, cy + this.cellSize * 0.45);
        }
    }

    planetOffset(planets, planet) {
        const count = planets.length;
        if (count === 1) return { x: 0, y: 0 };
        const angles = [-Math.PI / 4, Math.PI * 3 / 4, -Math.PI * 3 / 4, Math.PI / 4];
        const idx = planets.indexOf(planet);
        const angle = angles[idx % angles.length];
        const dist = 12;
        return {
            x: Math.cos(angle) * dist,
            y: Math.sin(angle) * dist,
        };
    }

    drawFleetPathPreview(ctx) {
        if (!this.selectedCell || !this.pathHighlight) return;

        ctx.save();
        ctx.strokeStyle = this.colors.pathHighlight;
        ctx.lineWidth = 3;
        ctx.globalAlpha = 0.8;
        ctx.setLineDash([8, 6]);

        // Highlight path from selected cell to hovered cell
        const from = this.getCellByKey(this.selectedCell.key);
        const to = this.getCellByKey(this.pathHighlight[1] || this.pathHighlight[0]);
        if (!from || !to) {
            ctx.restore();
            return;
        }

        ctx.beginPath();
        ctx.moveTo(from.x, from.y);
        ctx.lineTo(to.x, to.y);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.restore();

        // Draw distance label
        const dist = this.calculatePathDistance(this.selectedCell.key, this.pathHighlight[1] || this.pathHighlight[0]);
        const midX = (from.x + to.x) / 2;
        const midY = (from.y + to.y) / 2;
        ctx.fillStyle = this.colors.pathHighlight;
        ctx.font = 'bold 11px Segoe UI';
        ctx.textAlign = 'center';
        ctx.fillText(`${dist} jumps`, midX, midY - 10);
    }

    drawHint(ctx, w, h) {
        ctx.fillStyle = this.colors.textSecondary;
        ctx.font = '12px Segoe UI';
        ctx.textAlign = 'center';
        ctx.globalAlpha = 0.6;
        ctx.fillText('Drag to pan, scroll to zoom, click a system to select, hover for path', w / 2, h - 30);
        ctx.globalAlpha = 1;
    }

    // --- Interaction ---

    onMouseDown(e) {
        this.isDragging = true;
        this.dragStartX = e.clientX;
        this.dragStartY = e.clientY;
        this.dragPanStartX = this.panX;
        this.dragPanStartY = this.panY;
    }

    onMouseMove(e) {
        if (!this.gridCells) return;

        const rect = this.canvas.getBoundingClientRect();
        const mx = (e.clientX - this.panX) / this.zoom;
        const my = (e.clientY - this.panY) / this.zoom;

        // Find hovered cell
        let found = null;
        for (const cell of this.gridCells) {
            const dx = mx - cell.x;
            const dy = my - cell.y;
            if (dx * dx + dy * dy < (this.cellSize * 0.5) ** 2) {
                found = cell;
                break;
            }
        }

        // Update path highlight when dragging (fleet path preview)
        if (this.isDragging && this.selectedCell) {
            // Only update if mouse has moved significantly from drag start
            const dx = e.clientX - this.dragStartX;
            const dy = e.clientY - this.dragStartY;
            if (dx * dx + dy * dy > 100) {
                this.pathHighlight = [this.selectedCell.key, found?.key || this.selectedCell.key];
            }
        }

        this.hoveredCell = found;
        this.canvas.style.cursor = found ? 'pointer' : (this.isDragging ? 'grabbing' : 'default');
    }

    onMouseUp(e) {
        // Check if it was a drag or a click
        const dx = e.clientX - this.dragStartX;
        const dy = e.clientY - this.dragStartY;
        if (dx * dx + dy * dy < 25) {
            // It was a click — handled by onClick
        }
        this.isDragging = false;
    }

    onClick(e) {
        if (!this.gridCells) return;

        const rect = this.canvas.getBoundingClientRect();
        const mx = (e.clientX - this.panX) / this.zoom;
        const my = (e.clientY - this.panY) / this.zoom;

        // Find clicked cell
        let clicked = null;
        for (const cell of this.gridCells) {
            const dx = mx - cell.x;
            const dy = my - cell.y;
            if (dx * dx + dy * dy < (this.cellSize * 0.5) ** 2) {
                clicked = cell;
                break;
            }
        }

        if (!clicked) {
            this.selectedCell = null;
            this.pathHighlight = null;
            return;
        }

        if (!this.selectedCell) {
            // First click — select this system
            this.selectedCell = clicked;
            this.updatePlanetInfo(clicked);
        } else if (this.selectedCell.key !== clicked.key) {
            // Second click — set path and show fleet dispatch
            this.pathHighlight = [this.selectedCell.key, clicked.key];
            this.updatePlanetInfo(clicked, true);
        } else {
            // Click on same cell — view solar system
            this.selectedCell = null;
            this.pathHighlight = null;
            document.getElementById('galaxy-info').style.display = 'none';
            GalaxyView.setView('system', clicked);
        }
    }

    onWheel(e) {
        e.preventDefault();
        const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
        const newZoom = Math.max(0.3, Math.min(3, this.zoom * zoomFactor));

        // Zoom toward cursor
        const mx = e.clientX;
        const my = e.clientY;
        this.panX = mx - (mx - this.panX) * (newZoom / this.zoom);
        this.panY = my - (my - this.panY) * (newZoom / this.zoom);
        this.zoom = newZoom;
    }

    getCellByKey(key) {
        return this.gridCells.find(c => c.key === key);
    }

    // --- Fleet path calculation (Manhattan distance on grid) ---
    calculatePathDistance(fromKey, toKey) {
        const [sys1, pos1] = fromKey.split(',').map(Number);
        const [sys2, pos2] = toKey.split(',').map(Number);
        return Math.abs(sys1 - sys2) + Math.abs(pos1 - pos2);
    }

    // --- UI ---

    updatePlanetInfo(cell, withPath = false) {
        const panel = document.getElementById('galaxy-info');
        const nameEl = document.getElementById('galaxy-info-name');
        const detailsEl = document.getElementById('galaxy-info-details');
        const actionsEl = document.getElementById('galaxy-info-actions');

        nameEl.textContent = cell.planets.map(p => p.name).join(', ');
        nameEl.style.color = cell.planets.some(p => p.is_player) ? '#2ecc71' : '#ffffff';

        const totalPlanets = cell.planets.length;
        const playerPlanets = cell.planets.filter(p => p.is_player).length;

        let details = `
            <div class="info-section">
                <h4>Coordinates</h4>
                <p>System ${cell.system}, Position ${cell.position}</p>
            </div>
            <div class="info-section">
                <h4>Planets</h4>
                <p>${playerPlanets} owned / ${totalPlanets} total</p>
            </div>
        `;

        if (withPath && this.selectedCell) {
            const dist = this.calculatePathDistance(this.selectedCell.key, cell.key);
            details += `
                <div class="info-section">
                    <h4>Distance</h4>
                    <p>${dist} jump${dist !== 1 ? 's' : ''}</p>
                </div>
            `;
        }
        detailsEl.innerHTML = details;

        let actions = '';
        const hasPlayerPlanet = cell.planets.some(p => p.is_player);
        const isSameAsSelected = this.selectedCell && this.selectedCell.key === cell.key;

        if (!isSameAsSelected && !hasPlayerPlanet) {
            actions = `
                <button class="btn btn-primary" onclick="GalaxyView.dispatchFleet('${cell.key}')">Send Fleet</button>
                <button class="btn btn-secondary" onclick="GalaxyView.setView('system', window._galaxyMapInstance.getCellByKey('${cell.key}'))">View System</button>
            `;
        } else if (!hasPlayerPlanet) {
            actions = `
                <button class="btn btn-primary" onclick="GalaxyView.setView('system', window._galaxyMapInstance.getCellByKey('${cell.key}'))">View System</button>
            `;
        } else {
            actions = `<a href="index.php?page=planet" class="btn btn-primary">Manage Planet</a>`;
        }
        actionsEl.innerHTML = actions;

        panel.style.display = 'block';

        // Update status bar
        const statusEl = document.getElementById('galaxy-toolbar-status');
        if (statusEl) {
            if (this.selectedCell) {
                statusEl.textContent = `Selected: S${cell.system} P${cell.position}`;
            } else {
                statusEl.textContent = '';
            }
        }
    }
}

// Singleton instance for cross-reference
GalaxyMap.prototype.getCellByKey = GalaxyMap.prototype.getCellByKey;

// Reference for cross-component access
window._galaxyMapInstance = null;
