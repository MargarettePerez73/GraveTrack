<?php
$pageTitle   = 'Public Cemetery Map';
$currentPage = 'public_cemetery_map';
include 'includes/header.php';
?>

<style>
    *, *::before, *::after { box-sizing: border-box; }

    .cemetery-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    /* Sidebar */
    .cemetery-sidebar {
        width: 320px;
        background: white;
        border-right: 3px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .sidebar-section {
        padding: 20px;
        border-bottom: 2px solid #f1f5f9;
    }

    .sidebar-section h6 {
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 15px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Public view badge */
    .public-view-badge {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
        margin-bottom: 15px;
    }

    /* Search Box */
    .search-box { position: relative; }

    .search-box input {
        width: 100%;
        padding: 10px 40px 10px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .search-box input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    /* Simplified Legend - Public only shows vacant/occupied */
    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .legend-box {
        width: 30px;
        height: 16px;
        border-radius: 4px;
        border: 2px solid;
        flex-shrink: 0;
    }

    .legend-box.vacant    { background: #10b981; border-color: #059669; }
    .legend-box.occupied  { background: #ef4444; border-color: #dc2626; }

    /* Stats */
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f8fafc;
        border-radius: 6px;
        margin-bottom: 8px;
    }

    .stat-label { font-size: 12px; color: #64748b; font-weight: 600; }
    .stat-value { font-size: 16px; font-weight: 700; color: #1e3a8a; }

    /* Map Container */
    .map-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f5f5f5;
        padding: 20px;
    }

    .map-scaler {
        transform-origin: center center;
        display: inline-block;
    }

    .map-inner {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .all-blocks { display: flex; align-items: flex-end; gap: 0; }

    .phase-divider {
        width: 4px;
        background: #1e3a8a;
        align-self: stretch;
        flex-shrink: 0;
        margin: 0 8px;
    }

    .pair-gap { width: 12px; flex-shrink: 0; }

    .block-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 4px;
    }

    .block-label {
        font-size: 14px;
        font-weight: 900;
        color: #1e3a8a;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .plots-stack { display: flex; flex-direction: column; gap: 3px; }

    .lot-box {
        width: 50px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 700;
        transition: transform 0.1s, box-shadow 0.1s;
        position: relative;
    }

    .lot-box:hover {
        transform: scale(1.25);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 100;
    }

    .lot-box.vacant    { background: #10b981; border-color: #059669; color: white; }
    .lot-box.occupied  { background: #ef4444; border-color: #dc2626; color: white; }

    /* Simplified highlight */
    .lot-box.highlighted {
        box-shadow: 0 0 0 4px #fbbf24, 0 0 20px rgba(251,191,36,0.8) !important;
        transform: scale(1.3) !important;
        z-index: 1000 !important;
        background: linear-gradient(45deg, #fbbf24, #f59e0b) !important;
        border-color: #d97706 !important;
        animation: pulse 1.5s infinite !important;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px #fbbf24, 0 0 20px rgba(251,191,36,0.8); }
        50% { box-shadow: 0 0 0 6px #fbbf24, 0 0 30px rgba(251,191,36,1); }
    }

    .phase-labels-row {
        display: flex;
        width: 100%;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 3px solid #1e3a8a;
        font-size: 14px;
        font-weight: 900;
        color: #1e3a8a;
        letter-spacing: 1px;
    }

    .phase-label-cell { text-align: center; text-transform: uppercase; }

    /* Public plot modal - simplified, read-only */
    #publicPlotModal .modal-header {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    #publicPlotModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .public-plot-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    @media (max-width: 576px) {
        .public-plot-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    .public-plot-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 10px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        min-width: 0;
    }

    .public-plot-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 3px;
        display: block;
    }

    .public-plot-value {
        font-size: 13px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .public-deceased-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 768px) {
        .public-deceased-grid { grid-template-columns: 1fr; }
    }

    .public-deceased-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .public-deceased-name {
        font-weight: 900;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.2;
        margin: 0 0 6px;
    }

    .public-deceased-meta {
        font-size: 12px;
        color: #475569;
        margin: 0;
        line-height: 1.35;
    }
</style>

<div class="cemetery-layout">
    <!-- Sidebar -->
    <div class="cemetery-sidebar">
        <div class="sidebar-section">
            <div class="public-view-badge">
                <i class="fas fa-globe"></i> Public View
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-search"></i> Search Deceased</h6>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Type name to search..." autocomplete="off">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-map"></i> Legend</h6>
            <div>
                <div class="legend-item">
                    <div class="legend-box vacant"></div>
                    <span>Vacant Plot</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box occupied"></div>
                    <span>Occupied Plot</span>
                </div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-layer-group"></i> AA Block Section (Phase 3)</h6>
            <select id="aaSectionSelect" class="filter-input" onchange="changeAASectionPublic(this.value)">
                <option value="1">Section 1 (Default)</option>
                <option value="2">Section 2</option>
                <option value="3">Section 3</option>
            </select>
        </div>
        <div class="sidebar-section">
            <h6><i class="fas fa-chart-bar"></i> Statistics</h6>
            <div class="stat-item">
                <span class="stat-label">Total Plots</span>
                <span class="stat-value" id="totalPlots">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Vacant</span>
                <span class="stat-value" id="vacantPlots" style="color:#10b981;">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Occupied</span>
                <span class="stat-value" id="occupiedPlots" style="color:#ef4444;">0</span>
            </div>
        </div>

        <div class="sidebar-section">
            <a href="cemetery_map.php" class="btn btn-primary w-100" style="font-weight:700;">
                <i class="fas fa-user-lock me-2"></i>Staff Dashboard
            </a>
        </div>
    </div>

    <!-- Map -->
    <div class="map-container" id="mapContainer">
        <div class="map-scaler" id="mapScaler">
            <div class="map-inner" id="mapInner">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Loading cemetery map...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Public Plot Details Modal (read-only, simplified) -->
<div class="modal fade" id="publicPlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publicModalTitle">Plot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="publicPlotModalContent">Loading...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let allPublicPlots = [];
let currentAASectionPublic = '1';

function changeAASectionPublic(section) {
    currentAASectionPublic = section;
    document.getElementById('aaSectionSelect').value = section;
    renderPublicCemeteryMap();
    scaleMap();
}

async function loadPublicCemeteryMap() {
    try {
        // Load ALL plots (no phase filter) from public API
        const response = await fetch('/api/get_public_cemetery_map.php');
        const data = await response.json();

        if (data.success) {
            allPublicPlots = data.plots;
            renderPublicCemeteryMap();
            updatePublicStatistics();
        }
    } catch (error) {
        console.error('Error loading public cemetery map:', error);
        document.getElementById('mapInner').innerHTML =
            '<p class="text-center text-danger">Error loading map</p>';
    }
}

function renderPublicCemeteryMap() {
    // Same phases structure as staff map
    const phases = { 'Phase 1': {}, 'Phase 2': {}, 'Phase 3': {} };

    allPublicPlots.forEach(plot => {
        const phase = plot.phase || 'Phase 1';
        const block = plot.block;

        if (!phases[phase]) phases[phase] = {};
        if (!phases[phase][block]) phases[phase][block] = [];
        phases[phase][block].push(plot);
    });

    // Populate missing blocks for Phase 1/2
    const allKnownBlocks = ['A','B','C','D','E','F','G','H','I','T','U','V','W','X','Y','Z'];
    ['Phase 1', 'Phase 2'].forEach(phase => {
        allKnownBlocks.forEach(block => {
            if (!phases[phase][block]) phases[phase][block] = [];
        });
    });

    function findPublicPlot(block, lot, phase, section = null) {
        const plots = phases[phase]?.[block] || [];
        return plots.find(p => parseInt(p.lot) === lot && (!section || p.section === section)) || null;
    }

    let html = '<div class="all-blocks" id="allBlocks">';

    // Render AA Block Phase 3 (section 1 always, others optional)
    for (let sec = 1; sec <= 3; sec++) {
        if (sec === 1 || currentAASectionPublic === sec.toString()) {
            html += `<div class="block-col" data-section="${sec}">`;
            html += `<div class="block-label">AA Sec ${sec}</div>`;
            html += '<div class="plots-stack">';
            for (let lot = 20; lot >= 1; lot--) {
                const plot = findPublicPlot('AA', lot, 'Phase 3', sec);
                const colorClass = plot ? (plot.status === 'Vacant' ? 'vacant' : 'occupied') : 'vacant';
                const tooltip = `Block AA Sec ${sec} Lot ${lot} (${plot ? plot.status : 'Vacant'})`;
                const displayClass = (currentAASectionPublic !== sec.toString()) ? 'blurred' : '';
                html += `
                    <div class="lot-box ${colorClass} ${displayClass}"
                         title="${tooltip}"
                         data-block="AA" data-section="${sec}" data-lot="${lot}"
                         onclick="viewPublicPlotDetails('${plot ? plot.plot_id : ''}', 'AA', ${lot}, 'Phase 3', ${sec})">
                        ${lot}
                    </div>`;
            }
            html += '</div></div>';
        }
    }

    html += '<div class="phase-divider"></div>';

    // Phase 2
    const phase2Groups = [['Z','Y'], ['X','W'], ['V','U'], ['T']];
    phase2Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(b => html += renderPublicBlockColumn(b, 'Phase 2', findPublicPlot));
    });

    html += '<div class="phase-divider"></div>';

    // Phase 1
    const phase1Groups = [['I','H'], ['G','F'], ['E','D'], ['C','B'], ['A']];
    phase1Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(b => html += renderPublicBlockColumn(b, 'Phase 1', findPublicPlot));
    });

    html += '</div>';
    html += `
        <div class="phase-labels-row">
            <div class="phase-label-cell" style="flex:1;">PHASE 3</div>
            <div style="width:20px;"></div>
            <div class="phase-label-cell" style="flex:3;">PHASE 2</div>
            <div style="width:20px;"></div>
            <div class="phase-label-cell" style="flex:3;">PHASE 1</div>
        </div>
    `;

    document.getElementById('mapInner').innerHTML = html;
    setTimeout(() => scaleMap(), 100);
}

function renderPublicBlockColumn(blockName, phaseName, findPlot) {
    let html = '<div class="block-col">';
    html += `<div class="block-label">${blockName || 'Unnamed'}</div>`;
    html += '<div class="plots-stack">';

    for (let lot = 20; lot >= 1; lot--) {
        const plot = findPlot(blockName, lot, phaseName);
        const colorClass = plot ? (plot.status === 'Vacant' ? 'vacant' : 'occupied') : 'vacant';
        const tooltip = `Block ${blockName || 'Unnamed'} Lot ${lot} (${plot ? plot.status : 'Vacant'})`;
        html += `
            <div class="lot-box ${colorClass}"
                 title="${tooltip}"
                 data-block="${blockName || 'Unnamed'}" data-lot="${lot}"
                 onclick="viewPublicPlotDetails('${plot ? plot.plot_id : ''}', '${blockName}', ${lot}, '${phaseName}')">
                ${lot}
            </div>`;
    }

    html += '</div></div>';
    return html;
}

function updatePublicStatistics() {
    const vacant = allPublicPlots.filter(p => p.status === 'Vacant').length;
    const occupied = allPublicPlots.filter(p => p.status === 'Occupied').length;

    document.getElementById('totalPlots').textContent = allPublicPlots.length;
    document.getElementById('vacantPlots').textContent = vacant;
    document.getElementById('occupiedPlots').textContent = occupied;
}

async function viewPublicPlotDetails(plotId, block, lot, phase, section = '') {
    if (!plotId) {
        document.getElementById('publicModalTitle').textContent = `Block ${block || 'Unnamed'}${section ? ` Sec ${section}` : ''} Lot ${lot} (${phase})`;
        document.getElementById('publicPlotModalContent').innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Vacant Plot</h6>
                <p>This plot is currently available.</p>
                <p class="mb-0"><em>${phase} | Block ${block || 'Unnamed'}${section ? ` Sec ${section}` : ''} | Lot ${lot}</em></p>
            </div>`;
    } else {
        try {
            const response = await fetch(`/api/get_public_lot_details.php?plot_id=${plotId}`);
            const data = await response.json();

            if (data.success) {
                const plot = data.plot;
                const deceased = data.deceased || [];

                document.getElementById('publicModalTitle').textContent = 
                    `Block ${plot.block}${plot.section ? ` Sec ${plot.section}` : ''} Lot ${plot.lot} (${plot.phase || phase})`;

                let content = `
                    <div class="public-plot-meta-grid">
                        <div class="public-plot-card">
                            <span class="public-plot-label">Block</span>
                            <div class="public-plot-value">${plot.block}</div>
                        </div>
                        <div class="public-plot-card">
                            <span class="public-plot-label">Section</span>
                            <div class="public-plot-value">${plot.section || 'N/A'}</div>
                        </div>
                        <div class="public-plot-card">
                            <span class="public-plot-label">Lot</span>
                            <div class="public-plot-value">${plot.lot}</div>
                        </div>
                        <div class="public-plot-card">
                            <span class="public-plot-label">Phase</span>
                            <div class="public-plot-value">${plot.phase || phase}</div>
                        </div>
                        <div class="public-plot-card">
                            <span class="public-plot-label">Type</span>
                            <div class="public-plot-value">${plot.type || 'N/A'}</div>
                        </div>
                        <div class="public-plot-card">
                            <span class="public-plot-label">Status</span>
                            <div class="public-plot-value">${plot.status}</div>
                        </div>
                    </div>
                `;

                if (deceased.length > 0) {
                    content += `
                        <h6 class="mt-3 mb-2" style="font-weight:700;color:#1e3a8a;">
                            <i class="fas fa-users me-2"></i>Buried Here
                        </h6>
                        <div class="public-deceased-grid">
                    `;
                    deceased.forEach(person => {
                        content += `
                            <div class="public-deceased-card">
                                <div class="public-deceased-name">${person.full_name}</div>
                                <div class="public-deceased-meta">
                                    Born: ${formatPublicDate(person.birth_date)}<br>
                                    Died: ${formatPublicDate(person.date_of_death)}<br>
                                    Buried: ${formatPublicDate(person.date_of_burial)}
                                </div>
                            </div>
                        `;
                    });
                    content += `</div>`;
                }

                document.getElementById('publicPlotModalContent').innerHTML = content;
            }
        } catch (error) {
            console.error('Public plot details error:', error);
            document.getElementById('publicPlotModalContent').innerHTML = 
                '<div class="alert alert-warning">Unable to load plot details</div>';
        }
    }

    new bootstrap.Modal(document.getElementById('publicPlotModal')).show();
}

function formatPublicDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Search functionality (client-side)
document.getElementById('searchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    if (term.length < 2) {
        renderPublicCemeteryMap();
        return;
    }

    const matches = allPublicPlots.filter(p => 
        p.deceased_names?.toLowerCase().includes(term) ||
        p.block.toLowerCase().includes(term) ||
        p.lot.toString().includes(term)
    );

    highlightPublicSearchResults(matches);
});

function highlightPublicSearchResults(matches) {
    // Simplified: just highlight matching plots on map
    document.querySelectorAll('.lot-box').forEach(box => {
        const block = box.dataset.block;
        const section = box.dataset.section;
        const lot = box.dataset.lot;
        
        const isMatch = matches.some(p => 
            p.block === block && p.lot == lot && (!section || p.section === section)
        );
        
        if (isMatch) {
            box.classList.add('highlighted');
        }
    });
}

// Map scaling
function scaleMap() {
    const container = document.getElementById('mapContainer');
    const scaler = document.getElementById('mapScaler');
    const inner = document.getElementById('mapInner');
    
    if (!container || !scaler || !inner) return;
    
    scaler.style.transform = 'scale(1)';
    const scale = Math.min(
        (container.clientWidth - 40) / inner.scrollWidth,
        (container.clientHeight - 40) / inner.scrollHeight,
        1
    );
    scaler.style.transform = `scale(${scale})`;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadPublicCemeteryMap();
    window.addEventListener('resize', scaleMap);
});
</script>
