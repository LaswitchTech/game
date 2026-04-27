class GalaxyView {
    constructor() {
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        this.planets = [];
        this.selectedPlanet = null;
        this.starPositions = [];
        this.orbitLines = [];
        this.animationId = null;

        // Orbit controls
        this.isOrbiting = false;
        this.orbitStartX = 0;
        this.orbitStartY = 0;
        this.cameraTheta = 0; // horizontal angle
        this.cameraPhi = Math.PI / 4; // vertical angle
        this.cameraRadius = 200;
        this.targetCameraRadius = 200;
        this.target = new THREE.Vector3(15, 0, 15); // center between suns
    }

    init() {
        this.setView('map');
    }

    // Switch between map and solar system view
    setView(mode, cellData = null) {
        // Hide loading, show canvas
        document.getElementById('galaxy-loading').style.opacity = '0';

        // Update tabs
        document.querySelectorAll('.galaxy-tab').forEach(t => t.classList.remove('active'));

        if (mode === 'map') {
            document.getElementById('tab-galaxy-map').classList.add('active');
            document.getElementById('galaxy-info').style.display = 'none';
            this.initMap();
        } else if (mode === 'system') {
            document.getElementById('tab-solar-system').classList.add('active');
            this.initSolarSystem(cellData);
        }
    }

    setTab(tab) {
        if (tab === 'map') {
            this.setView('map');
        } else if (tab === 'solar-system') {
            this.setView('system');
        }
    }

    dispatchFleet(cellKey) {
        // Create a fleet dispatch modal
        const [sys, pos] = cellKey.split(',');

        // Check if there are selected cells for fleet
        const gridData = window.GalaxyMap?.gridCells;
        let fleetFrom = null;
        if (gridData) {
            for (const cell of gridData) {
                if (cell.planets.some(p => p.is_player)) {
                    fleetFrom = cell;
                    break;
                }
            }
        }

        if (!fleetFrom) {
            alert('You need at least one planet to dispatch fleets from.');
            return;
        }

        const dist = Math.abs(fleetFrom.system - parseInt(sys)) + Math.abs(fleetFrom.position - parseInt(pos));

        // Show a simple confirm prompt
        if (confirm(`Send fleet from S${fleetFrom.system} P${fleetFrom.position} to S${sys} P${pos} (${dist} jumps)?`)) {
            Api.sendFleet(parseInt(sys), parseInt(pos), 'attack', {})
                .then(result => {
                    if (result.success) {
                        alert('Fleet dispatched successfully!');
                    } else {
                        alert(result.error || 'Failed to dispatch fleet.');
                    }
                });
        }
    }

    initMap() {
        // Clean up Three.js
        if (this.renderer) {
            this.renderer.dispose();
            this.renderer = null;
        }
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }

        // Use 2D canvas for map
        if (!window.GalaxyMap) {
            window.GalaxyMap = new GalaxyMap();
        }
        window.GalaxyMap.init();
    }

    initSolarSystem(cellData = null) {
        // Clean up 2D map
        if (window.GalaxyMap) {
            // Galaxy map handles its own cleanup
        }

        const canvas = document.getElementById('galaxy-canvas');
        const container = document.getElementById('galaxy-view');

        // Scene
        this.scene = new THREE.Scene();

        // Camera
        this.camera = new THREE.PerspectiveCamera(60, window.innerWidth / (window.innerHeight - 110), 0.1, 2000);
        this.camera.position.set(0, 100, 200);
        this.camera.lookAt(this.target);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        this.renderer.setSize(window.innerWidth, window.innerHeight - 110);
        this.renderer.setPixelRatio(window.devicePixelRatio);

        // Starfield background
        this.createStarfield();

        // Dual stars (suns)
        this.createSuns();

        // Planets — frozen in place
        this.createPlanets(cellData);

        // Ambient light
        const ambient = new THREE.AmbientLight(0x404040, 0.5);
        this.scene.add(ambient);

        // Point lights from stars
        const sun1Light = new THREE.PointLight(0xffaa00, 2, 500);
        sun1Light.position.set(0, 0, 0);
        this.scene.add(sun1Light);

        const sun2Light = new THREE.PointLight(0x0088ff, 1.5, 500);
        sun2Light.position.set(30, 0, 30);
        this.scene.add(sun2Light);

        // Mouse interaction
        canvas.addEventListener('mousedown', (e) => this.onOrbitDown(e));
        canvas.addEventListener('mousemove', (e) => this.onMouseMove(e));
        canvas.addEventListener('mouseup', () => this.isOrbiting = false);
        canvas.addEventListener('click', (e) => this.onClick(e));
        canvas.addEventListener('wheel', (e) => this.onWheel(e));
        window.addEventListener('resize', () => this.onResize());

        // Start animation
        this.animate();
    }

    createStarfield() {
        const geometry = new THREE.BufferGeometry();
        const vertices = [];
        this.starPositions = [];

        for (let i = 0; i < 5000; i++) {
            const x = (Math.random() - 0.5) * 1500;
            const y = (Math.random() - 0.5) * 1500;
            const z = (Math.random() - 0.5) * 1500;
            vertices.push(x, y, z);
            this.starPositions.push([x, y, z]);
        }

        geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
        const material = new THREE.PointsMaterial({ color: 0xffffff, size: 0.8 });
        const stars = new THREE.Points(geometry, material);
        this.scene.add(stars);
    }

    createSuns() {
        // Primary star (larger, warmer)
        const sun1Geo = new THREE.SphereGeometry(8, 32, 32);
        const sun1Mat = new THREE.MeshBasicMaterial({ color: 0xffdd44 });
        this.sun1 = new THREE.Mesh(sun1Geo, sun1Mat);
        this.sun1.position.set(0, 0, 0);
        this.scene.add(this.sun1);

        // Glow for primary star
        const sun1GlowGeo = new THREE.SphereGeometry(12, 32, 32);
        const sun1GlowMat = new THREE.MeshBasicMaterial({ color: 0xffaa00, transparent: true, opacity: 0.3 });
        this.sun1Glow = new THREE.Mesh(sun1GlowGeo, sun1GlowMat);
        this.sun1.add(this.sun1Glow);

        // Secondary star (smaller, cooler)
        const sun2Geo = new THREE.SphereGeometry(5, 32, 32);
        const sun2Mat = new THREE.MeshBasicMaterial({ color: 0x4488ff });
        this.sun2 = new THREE.Mesh(sun2Geo, sun2Mat);
        this.sun2.position.set(30, 0, 30);
        this.scene.add(this.sun2);

        // Glow for secondary star
        const sun2GlowGeo = new THREE.SphereGeometry(8, 32, 32);
        const sun2GlowMat = new THREE.MeshBasicMaterial({ color: 0x2266cc, transparent: true, opacity: 0.3 });
        this.sun2Glow = new THREE.Mesh(sun2GlowGeo, sun2GlowMat);
        this.sun2.add(this.sun2Glow);
    }

    createPlanets(cellData) {
        // Use cell data from galaxy map if provided, otherwise use defaults
        const planetData = cellData && cellData.planets
            ? cellData.planets.map((p, i) => ({
                name: p.name,
                color: p.is_player ? 0x2ecc71 : (p.faction === 'covenant' ? 0xd4a017 : 0x4488ff),
                radius: 3 + Math.random() * 3,
                angle: i * 0.5,
                orbit: 30 + i * 25,
                is_player: p.is_player,
            }))
            : [
                { name: 'Home World', color: 0x2ecc71, radius: 4, orbit: 30, angle: 0, is_player: true },
                { name: 'Outpost Alpha', color: 0x3498db, radius: 3, orbit: 55, angle: 0.5, is_player: false },
                { name: 'Mining Colony', color: 0xe67e22, radius: 3.5, orbit: 80, angle: 1.0, is_player: false },
                { name: 'Flood Zone', color: 0x9b59b6, radius: 5, orbit: 110, angle: 1.5, is_player: false },
                { name: 'Forerunner Ruins', color: 0x1abc9c, radius: 4, orbit: 140, angle: 2.0, is_player: false },
                { name: 'Halo Site 1', color: 0xf1c40f, radius: 6, orbit: 170, angle: 2.5, is_player: false },
                { name: 'Halo Site 2', color: 0xf1c40f, radius: 6, orbit: 200, angle: 3.0, is_player: false },
                { name: 'Halo Site 3', color: 0xf1c40f, radius: 6, orbit: 230, angle: 3.5, is_player: false },
                { name: 'Halo Site 4', color: 0xf1c40f, radius: 6, orbit: 260, angle: 4.0, is_player: false },
                { name: 'Halo Site 5', color: 0xf1c40f, radius: 6, orbit: 290, angle: 4.5, is_player: false },
                { name: 'Halo Site 6', color: 0xf1c40f, radius: 6, orbit: 320, angle: 5.0, is_player: false },
                { name: 'Halo Site 7', color: 0xf1c40f, radius: 6, orbit: 350, angle: 5.5, is_player: false },
            ];

        planetData.forEach((data, index) => {
            // Draw orbit path
            const orbitGeo = new THREE.RingGeometry(data.orbit - 0.2, data.orbit + 0.2, 64);
            const orbitMat = new THREE.MeshBasicMaterial({
                color: data.is_player ? 0x2ecc71 : 0x444444,
                transparent: true,
                opacity: 0.2,
                side: THREE.DoubleSide
            });
            const orbit = new THREE.Mesh(orbitGeo, orbitMat);
            orbit.rotation.x = Math.PI / 2;
            this.scene.add(orbit);
            this.orbitLines.push({ mesh: orbit, radius: data.orbit });

            // Create planet
            const planetGeo = new THREE.SphereGeometry(data.radius, 32, 32);
            const planetMat = new THREE.MeshStandardMaterial({
                color: data.color,
                emissive: data.color,
                emissiveIntensity: 0.2
            });
            const planet = new THREE.Mesh(planetGeo, planetMat);
            planet.position.set(
                Math.cos(data.angle) * data.orbit,
                0,
                Math.sin(data.angle) * data.orbit
            );
            planet.userData = { ...data, index };
            this.scene.add(planet);
            this.planets.push(planet);

            // Planet label (small sprite)
            const label = this.createTextSprite(data.name, data.is_player ? '#2ecc71' : '#ffffff');
            label.position.set(0, data.radius + 3, 0);
            planet.add(label);

            // Halo ring for Halo Sites
            if (data.name.startsWith('Halo')) {
                const haloGeo = new THREE.RingGeometry(data.radius + 2, data.radius + 4, 32);
                const haloMat = new THREE.MeshBasicMaterial({
                    color: 0xf1c40f,
                    transparent: true,
                    opacity: 0.6,
                    side: THREE.DoubleSide
                });
                const halo = new THREE.Mesh(haloGeo, haloMat);
                halo.rotation.x = Math.PI / 2;
                planet.add(halo);
            }
        });
    }

    createTextSprite(text, color) {
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 64;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = color;
        ctx.font = 'Bold 24px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(text, 128, 40);

        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({ map: texture });
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(10, 2.5, 1);
        return sprite;
    }

    // Orbit controls
    onOrbitDown(e) {
        this.isOrbiting = true;
        this.orbitStartX = e.clientX;
        this.orbitStartY = e.clientY;
    }

    onMouseMove(event) {
        this.mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        this.mouse.y = -(event.clientY / (window.innerHeight - 110)) * 2 + 1;

        // Hover effect
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.planets);
        const canvas = document.getElementById('galaxy-canvas');
        canvas.style.cursor = intersects.length > 0 ? 'pointer' : (this.isOrbiting ? 'grabbing' : 'default');
    }

    onClick(event) {
        this.mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        this.mouse.y = -(event.clientY / (window.innerHeight - 110)) * 2 + 1;

        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.planets);

        if (intersects.length > 0) {
            const planet = intersects[0].object;
            this.showPlanetInfo(planet.userData);
        }
    }

    onWheel(event) {
        event.preventDefault();
        this.targetCameraRadius = Math.max(50, Math.min(500, this.targetCameraRadius + event.deltaY * 0.3));
    }

    showPlanetInfo(data) {
        const panel = document.getElementById('galaxy-info');
        const nameEl = document.getElementById('galaxy-info-name');
        const detailsEl = document.getElementById('galaxy-info-details');
        const actionsEl = document.getElementById('galaxy-info-actions');

        nameEl.textContent = data.name;
        nameEl.style.color = data.is_player ? '#2ecc71' : '#ffffff';

        const status = data.is_player ? 'Owned' : 'Unexplored';
        detailsEl.innerHTML = `
            <div class="info-section">
                <h4>Status</h4>
                <p style="color: ${data.is_player ? '#2ecc71' : '#888'}">${status}</p>
            </div>
            <div class="info-section">
                <h4>Orbit</h4>
                <p>${Math.round(data.orbit)} AU</p>
            </div>
        `;

        let actions = '';
        if (data.name.startsWith('Home')) {
            actions = `<a href="planet.html" class="btn btn-primary">Visit Planet</a>`;
        } else if (data.is_player) {
            actions = `<a href="fleet.html" class="btn btn-primary">Manage Fleet</a>`;
        } else if (data.name.startsWith('Halo')) {
            actions = `
                <button class="btn btn-secondary" disabled>Requires Halo Activation Tech</button>
                <a href="research.html" class="btn btn-primary">Research</a>
            `;
        } else {
            actions = `<a href="fleet.html" class="btn btn-secondary">Explore</a>`;
        }
        actionsEl.innerHTML = actions;

        panel.style.display = 'block';
    }

    onResize() {
        const width = window.innerWidth;
        const height = window.innerHeight - 110;
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());

        // Smooth zoom
        this.cameraRadius += (this.targetCameraRadius - this.cameraRadius) * 0.1;

        // Slow sun rotation
        this.sun1.rotation.y += 0.005;
        this.sun2.rotation.y += 0.003;

        // Update camera position from orbit angles
        this.camera.position.x = this.target.x + this.cameraRadius * Math.sin(this.cameraPhi) * Math.sin(this.cameraTheta);
        this.camera.position.y = this.target.y + this.cameraRadius * Math.cos(this.cameraPhi);
        this.camera.position.z = this.target.z + this.cameraRadius * Math.sin(this.cameraPhi) * Math.cos(this.cameraTheta);
        this.camera.lookAt(this.target);

        this.renderer.render(this.scene, this.camera);
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }
}

