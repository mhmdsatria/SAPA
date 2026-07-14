import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

const escapeHtml = (value = '') => String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));

const markerIcon = color => L.divIcon({
    className: 'gis-marker-wrap',
    html: `<span class="gis-pin" style="--pin:${escapeHtml(color)}"><span></span></span>`,
    iconSize: [34, 42], iconAnchor: [17, 40], popupAnchor: [0, -35],
});

window.gisMap = config => ({
    map: null,
    clusters: null,
    regionLayer: null,
    locationMarker: null,
    accuracyCircle: null,
    loading: true,
    error: '',
    resizeObserver: null,
    tileFailures: 0,

    init() {
        this.$nextTick(() => {
            const element = this.$refs.container;
            if (!element || this.map) return;
            this.map = L.map(element, { zoomControl: true, preferCanvas: true, minZoom: 5, maxZoom: 20, worldCopyJump: true });
            this.map.setView(config.center.map(Number), Number(config.zoom || 11));
            this.addTiles();
            this.clusters = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 52, spiderfyOnMaxZoom: true, showCoverageOnHover: false });
            this.regionLayer = L.layerGroup().addTo(this.map);
            this.map.on('zoomend', () => this.syncLayers());
            this.resizeObserver = new ResizeObserver(() => this.resizeSoon());
            this.resizeObserver.observe(element);
            this.loadAll();
            if (Array.isArray(config.location) && config.location.length === 2) {
                this.setLocation(config.location[0], config.location[1], config.accuracy ?? null);
            }
            setTimeout(() => this.resizeSoon(), 120);
            setTimeout(() => this.resizeSoon(), 600);
        });
    },

    addTiles() {
        let tiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 20, subdomains: 'abcd', attribution: '&copy; OpenStreetMap &copy; CARTO', crossOrigin: true,
        }).addTo(this.map);
        tiles.on('tileerror', () => {
            this.tileFailures += 1;
            if (this.tileFailures === 4) {
                this.map.removeLayer(tiles);
                tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20, attribution: '&copy; OpenStreetMap' }).addTo(this.map);
            }
        });
    },

    async loadAll() {
        this.loading = true;
        this.error = '';
        try {
            await Promise.all([this.loadRegions(), this.loadComplaints()]);
        } catch (error) {
            console.error(error);
            this.error = 'Peta belum dapat dimuat. Periksa koneksi dan muat ulang halaman.';
        } finally {
            this.loading = false;
            this.syncLayers();
            this.resizeSoon();
        }
    },

    async fetchJson(url) {
        if (!url) return {type:'FeatureCollection', features:[]};
        const response = await fetch(url, { headers: {Accept:'application/json'}, credentials:'same-origin' });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },

    async loadRegions() {
        this.regionLayer.clearLayers();
        const data = await this.fetchJson(config.regionEndpoint);
        (data.features || []).filter(f => Number(f.properties?.total || 0) > 0).forEach(feature => {
            const [lng, lat] = feature.geometry.coordinates;
            const total = Number(feature.properties.total || 0);
            const radius = Math.min(34, 13 + Math.sqrt(total) * 4);
            const bubble = L.circleMarker([lat, lng], { radius, color:'#2563eb', weight:2, fillColor:'#3b82f6', fillOpacity:.2 });
            bubble.bindTooltip(`<strong>${escapeHtml(feature.properties.name)}</strong><br>${total} laporan disetujui`, { direction:'top' });
            bubble.on('click', () => this.map.flyTo([lat,lng], 13, {duration:.7}));
            this.regionLayer.addLayer(bubble);
        });
    },

    async loadComplaints() {
        this.clusters.clearLayers();
        const data = await this.fetchJson(config.endpoint);
        (data.features || []).forEach(feature => {
            const [lng, lat] = feature.geometry.coordinates;
            const p = feature.properties || {};
            const marker = L.marker([lat,lng], {icon: markerIcon(p.color || '#2563eb'), keyboard:true});
            const detailUrl = escapeHtml(p.url || '#');
            const routeUrl = escapeHtml(
                p.route_url ||
                `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(`${lat},${lng}`)}&travelmode=driving`
            );
            marker.bindPopup(`
                <div class="map-popup">
                    <strong>${escapeHtml(p.title)}</strong>
                    <div>${escapeHtml(p.category_label || '')}${p.region ? ` · ${escapeHtml(p.region)}` : ''}</div>
                    <p>${escapeHtml(p.address || '')}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem">
                        <a href="${detailUrl}">Lihat detail</a>
                        <a href="${routeUrl}" target="_blank" rel="noopener noreferrer">Buka rute Google Maps</a>
                    </div>
                </div>
            `);
            this.clusters.addLayer(marker);
        });
    },

    syncLayers() {
        if (!this.map) return;
        const showMarkers = Boolean(config.alwaysShowMarkers) || this.map.getZoom() >= Number(config.markerZoomThreshold || 12);
        if (showMarkers) {
            if (!this.map.hasLayer(this.clusters)) this.map.addLayer(this.clusters);
            if (this.map.hasLayer(this.regionLayer)) this.map.removeLayer(this.regionLayer);
        } else {
            if (this.map.hasLayer(this.clusters)) this.map.removeLayer(this.clusters);
            if (!this.map.hasLayer(this.regionLayer)) this.map.addLayer(this.regionLayer);
        }
    },

    setLocation(latitude, longitude, accuracy = null) {
        if (!this.map || latitude === null || longitude === null) return;
        const latlng = [Number(latitude), Number(longitude)];
        if (this.locationMarker) this.locationMarker.remove();
        if (this.accuracyCircle) this.accuracyCircle.remove();
        this.locationMarker = L.marker(latlng, {icon: markerIcon('#0f766e'), draggable:false}).addTo(this.map);
        if (accuracy && Number(accuracy) > 0) this.accuracyCircle = L.circle(latlng, {radius:Number(accuracy), color:'#0f766e', weight:1, fillOpacity:.08}).addTo(this.map);
        this.map.flyTo(latlng, Math.max(this.map.getZoom(), 17), {duration:.6});
        this.resizeSoon();
    },

    resizeSoon() {
        if (!this.map) return;
        requestAnimationFrame(() => this.map?.invalidateSize({pan:false}));
        setTimeout(() => this.map?.invalidateSize({pan:false}), 180);
    },

    destroy() {
        this.resizeObserver?.disconnect();
        this.resizeObserver = null;
        if (this.map) {
            this.map.off();
            this.map.remove();
            this.map = null;
        }
    },
});

window.addEventListener('livewire:navigated', () => window.dispatchEvent(new CustomEvent('map-resize')));
