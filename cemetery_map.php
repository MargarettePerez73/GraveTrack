<?php
$pageTitle = 'Cemetery Map';
$currentPage = 'cemetery_map';
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

    /* Search Box */
    .search-box {
        position: relative;
    }

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
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        margin-top: 5px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
    }

    .search-results.active {
        display: block;
    }

    .search-result-item {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.2s;
    }

    .search-result-item:hover {
        background: #f8fafc;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-name {
        font-weight: 600;
        color: #1e3a8a;
        font-size: 13px;
    }

    .search-result-location {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }

    .search-result-dates {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .no-results {
        padding: 15px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
    }

    /* Legend */
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

    .legend-box.vacant { background: #10b981; border-color: #059669; }
    .legend-box.occupied { background: #ef4444; border-color: #dc2626; }
    .legend-box.fully-paid { background: #10b981; border-color: #059669; }
    .legend-box.partially-paid { background: #f59e0b; border-color: #d97706; }
    .legend-box.unpaid { background: #ef4444; border-color: #dc2626; }
    .legend-box.overdue { background: #991b1b; border-color: #7f1d1d; }

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

    .stat-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    .stat-value {
        font-size: 16px;
        font-weight: 700;
        color: #1e3a8a;
    }

    /* View Mode Badge */
    .view-mode-badge {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
        margin-bottom: 15px;
    }

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

    .all-blocks {
        display: flex;
        align-items: flex-end;
        gap: 0;
    }

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

    .plots-stack {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

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

    .lot-box.vacant { background: #10b981; border-color: #059669; color: white; }
    .lot-box.occupied { background: #ef4444; border-color: #dc2626; color: white; }
    .lot-box.fully-paid { background: #10b981; border-color: #059669; color: white; }
    .lot-box.partially-paid { background: #f59e0b; border-color: #d97706; color: white; }
    .lot-box.unpaid { background: #ef4444; border-color: #dc2626; color: white; }
    .lot-box.overdue { background: #991b1b; border-color: #7f1d1d; color: white; }

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

    .phase-label-cell {
        text-align: center;
        text-transform: uppercase;
    }
</style>

<div class="cemetery-layout">
    <!-- Sidebar -->
    <div class="cemetery-sidebar">
        <!-- View Mode -->
        <div class="sidebar-section">
            <div class="view-mode-badge" id="viewModeBadge">
                <i class="fas fa-user"></i> Loading...
            </div>
        </div>

        <!-- Search -->
        <div class="sidebar-section">
            <h6><i class="fas fa-search"></i> Search Deceased</h6>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Type name to search..." autocomplete="off">
                <i class="fas fa-search search-icon"></i>
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <!-- Legend -->
        <div class="sidebar-section">
            <h6><i class="fas fa-map"></i> Legend</h6>
            <div id="engineerLegend" style="display: none;">
                <div class="legend-item">
                    <div class="legend-box vacant"></div>
                    <span>Vacant Plot</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box occupied"></div>
                    <span>Occupied Plot</span>
                </div>
            </div>
            <div id="treasurerLegend" style="display: none;">
                <div class="legend-item">
                    <div class="legend-box fully-paid"></div>
                    <span>Fully Paid (3 Years)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box partially-paid"></div>
                    <span>Partially Paid</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box unpaid"></div>
                    <span>Unpaid</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box overdue"></div>
                    <span>Overdue (Penalty Applied)</span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="sidebar-section">
            <h6><i class="fas fa-chart-bar"></i> Statistics</h6>
            <div class="stat-item">
                <span class="stat-label">Total Plots</span>
                <span class="stat-value" id="totalPlots">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Vacant</span>
                <span class="stat-value" id="vacantPlots" style="color: #10b981;">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Occupied</span>
                <span class="stat-value" id="occupiedPlots" style="color: #ef4444;">0</span>
            </div>
            <div class="stat-item" id="deceasedCountStat">
                <span class="stat-label">Total Deceased</span>
                <span class="stat-value" id="totalDeceased">0</span>
            </div>
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

<!-- Plot Details Modal -->
<div class="modal fade" id="plotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Plot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="plotModalContent">Loading...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="editBtn" style="display:none;">
                    <i class="fas fa-edit"></i> Edit Record
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let allPlots = [];
let paymentData = {};
let userRole = '';
const LOTS_PER_BLOCK = 20;
let allDeceasedRecords = [];
let searchTimeout = null;

async function loadCemeteryMap() {
    try {
        const response = await fetch('/api/get_cemetery_map.php');
        const data = await response.json();

        if (data.success) {
            allPlots = data.plots;

            // Load all deceased records for search
            await loadAllDeceasedRecords();

            // Load payment data if Treasurer
            if (currentUser && currentUser.role === 'Treasurer') {
                await loadPaymentData();
            }

            renderCemeteryMap();
            updateStatistics();
        }
    } catch (error) {
        console.error('Error loading cemetery map:', error);
        document.getElementById('mapInner').innerHTML =
            '<p class="text-center text-danger">Error loading map</p>';
    }
}

async function loadAllDeceasedRecords() {
    try {
        allDeceasedRecords = [];
        let totalDeceased = 0;

        for (const plot of allPlots) {
            if (plot.status === 'Occupied' && plot.deceased_count > 0) {
                const response = await fetch(`/api/get_lot_details.php?plot_id=${plot.plot_id}`);
                const data = await response.json();
                if (data.success && data.deceased_records) {
                    data.deceased_records.forEach(record => {
                        allDeceasedRecords.push({
                            ...record,
                            plot_id: plot.plot_id,
                            block: plot.block,
                            section: plot.section,
                            lot: plot.lot,
                            phase: plot.phase
                        });
                        totalDeceased++;
                    });
                }
            }
        }

        document.getElementById('totalDeceased').textContent = totalDeceased;
    } catch (error) {
        console.error('Error loading deceased records:', error);
    }
}

async function loadPaymentData() {
    try {
        const response = await fetch('/api/get_payment_summary.php', { credentials: 'include' });
        const data = await response.json();

        if (data.success) {
            data.data.forEach(payment => {
                paymentData[payment['Plot Location']] = payment['Status'];
            });
        }
    } catch (error) {
        console.error('Error loading payment data:', error);
    }
}

function updateStatistics() {
    const vacant = allPlots.filter(p => p.status === 'Vacant').length;
    const occupied = allPlots.filter(p => p.status === 'Occupied').length;

    document.getElementById('totalPlots').textContent = allPlots.length;
    document.getElementById('vacantPlots').textContent = vacant;
    document.getElementById('occupiedPlots').textContent = occupied;
}

function renderCemeteryMap() {
    userRole = currentUser ? currentUser.role : 'Engineer';

    // Update view mode badge
    document.getElementById('viewModeBadge').innerHTML =
        `<i class="fas fa-user"></i> ${userRole} View`;

    // Show appropriate legend
    if (userRole === 'Treasurer') {
        document.getElementById('treasurerLegend').style.display = 'block';
        document.getElementById('engineerLegend').style.display = 'none';
    } else {
        document.getElementById('engineerLegend').style.display = 'block';
        document.getElementById('treasurerLegend').style.display = 'none';
    }

    // Group plots by phase and block
    const phases = {
        'Phase 1': {},
        'Phase 2': {},
        'Phase 3': {}
    };

    allPlots.forEach(plot => {
        const phase = plot.phase;
        const block = plot.block;

        if (!phases[phase]) phases[phase] = {};
        if (!phases[phase][block]) phases[phase][block] = [];

        phases[phase][block].push(plot);
    });

    // Create plot lookup function
    function findPlot(block, lot, phase) {
        const plots = phases[phase] ? phases[phase][block] : null;
        if (!plots) return null;
        return plots.find(p => parseInt(p.lot) === lot);
    }

    // Build the map HTML
    let html = '<div class="all-blocks" id="allBlocks">';

    // PHASE 3: AA + Unnamed block
    html += renderBlockColumn('AA', 'Phase 3', findPlot, 20);
    html += renderBlockColumn('', 'Phase 3', findPlot, 10);

    html += '<div class="phase-divider"></div>';

    // PHASE 2: Blocks T-Z
    const phase2Groups = [['Z', 'Y'], ['X', 'W'], ['V', 'U'], ['T']];

    phase2Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(blockName => {
            html += renderBlockColumn(blockName, 'Phase 2', findPlot, 20);
        });
    });

    html += '<div class="phase-divider"></div>';

    // PHASE 1: Blocks A-I
    const phase1Groups = [['I', 'H'], ['G', 'F'], ['E', 'D'], ['C', 'B'], ['A']];

    phase1Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(blockName => {
            html += renderBlockColumn(blockName, 'Phase 1', findPlot, 20);
        });
    });

    html += '</div>';

    // Phase labels
    html += `
        <div class="phase-labels-row">
            <div class="phase-label-cell" style="flex: 1;">PHASE 3</div>
            <div style="width: 20px;"></div>
            <div class="phase-label-cell" style="flex: 3;">PHASE 2</div>
            <div style="width: 20px;"></div>
            <div class="phase-label-cell" style="flex: 3;">PHASE 1</div>
        </div>
    `;

    document.getElementById('mapInner').innerHTML = html;

    // Auto-scale to fit
    setTimeout(scaleMap, 100);
}

function renderBlockColumn(blockName, phaseName, findPlot, lotsCount = 20) {
    let html = '<div class="block-col">';
    html += `<div class="block-label">${blockName || '&nbsp;'}</div>`;
    html += '<div class="plots-stack">';

    // Render from bottom to top
    for (let lot = lotsCount; lot >= 1; lot--) {
        const plot = findPlot(blockName, lot, phaseName);
        const colorClass = plot ? getPlotColorClass(plot) : 'vacant';
        const plotId = plot ? plot.plot_id : null;
        const displayBlock = blockName || 'Unnamed';

        const tooltip = plot ?
            `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot} - ${plot.status}` :
            `Block ${displayBlock}, Lot ${lot} - Vacant`;

        html += `
            <div class="lot-box ${colorClass}"
                 title="${tooltip}"
                 onclick="viewPlotDetails(${plotId}, '${blockName}', ${lot}, '${phaseName}')">
                ${lot}
            </div>
        `;
    }

    html += '</div></div>';
    return html;
}

function getPlotColorClass(plot) {
    const plotLocation = `${plot.block} - ${plot.section} - ${plot.lot}`;

    if (userRole === 'Treasurer') {
        if (plot.status === 'Vacant') return 'vacant';

        const paymentStatus = paymentData[plotLocation];

        if (paymentStatus === 'Paid') return 'fully-paid';
        if (paymentStatus === 'Overdue') return 'overdue';
        if (paymentStatus === 'Pending') return 'partially-paid';
        return 'unpaid';
    } else {
        return plot.status === 'Vacant' ? 'vacant' : 'occupied';
    }
}

async function viewPlotDetails(plotId, blockName, lotNumber, phaseName) {
    if (!plotId || plotId === null) {
        const displayBlock = blockName || 'Unnamed';
        document.getElementById('modalTitle').textContent = `Plot: Block ${displayBlock}, Lot ${lotNumber}`;
        document.getElementById('plotModalContent').innerHTML = `
            <div class="alert alert-info">
                <p><strong>Vacant Plot</strong></p>
                <p>This plot is currently vacant and available for burial.</p>
            </div>
        `;
        document.getElementById('editBtn').style.display = 'none';

        const modal = new bootstrap.Modal(document.getElementById('plotModal'));
        modal.show();
        return;
    }

    try {
        const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('modalTitle').textContent =
                `Plot: Block ${data.plot.block}, Section ${data.plot.section}, Lot ${data.plot.lot}`;

            let content = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Type:</strong> ${data.plot.type}</p>
                        <p><strong>Status:</strong> ${getStatusBadge(data.plot.status)}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date Added:</strong> ${formatDate(data.plot.date_added)}</p>
                    </div>
                </div>
            `;

            if (data.deceased_records && data.deceased_records.length > 0) {
                content += '<hr><h6><strong>Deceased Records:</strong></h6>';
                content += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                content += '<thead><tr><th>Name</th><th>Date of Death</th><th>Contact</th><th>Action</th></tr></thead><tbody>';

                data.deceased_records.forEach(record => {
                    content += `
                        <tr>
                            <td><strong>${record.full_name}</strong></td>
                            <td>${formatDate(record.date_of_death)}</td>
                            <td>${record.contact_person || 'N/A'}<br><small>${record.contact_number || ''}</small></td>
                            <td>
                                <a href="edit_burial_record.php?id=${record.deceased_id}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    `;
                });

                content += '</tbody></table></div>';
            } else {
                content += '<hr><p class="text-muted text-center">No deceased records</p>';
            }

            document.getElementById('plotModalContent').innerHTML = content;

            const modal = new bootstrap.Modal(document.getElementById('plotModal'));
            modal.show();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Live Search Functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.trim();

    clearTimeout(searchTimeout);

    if (searchTerm.length < 2) {
        hideSearchResults();
        return;
    }

    searchTimeout = setTimeout(() => {
        performLiveSearch(searchTerm);
    }, 300);
});

async function performLiveSearch(searchTerm) {
    try {
        const response = await fetch(`/api/search_deceased.php?q=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();

        if (data.success) {
            displaySearchResults(data.results);
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

function displaySearchResults(results) {
    const resultsContainer = document.getElementById('searchResults');

    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No deceased found</div>';
        resultsContainer.classList.add('active');
        return;
    }

    let html = '';
    results.forEach(record => {
        html += `
            <div class="search-result-item" onclick="selectSearchResult(${record.plot_id}, '${record.block}', ${record.lot})">
                <div class="search-result-name">${record.full_name}</div>
                <div class="search-result-location">
                    <i class="fas fa-map-marker-alt"></i> Block ${record.block}, Section ${record.section}, Lot ${record.lot}
                </div>
                <div class="search-result-dates">
                    <i class="fas fa-calendar"></i> ${formatDate(record.date_of_death)}
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
    resultsContainer.classList.add('active');
}

function selectSearchResult(plotId, block, lot) {
    hideSearchResults();
    document.getElementById('searchInput').value = '';
    viewPlotDetails(plotId, block, lot, '');
    highlightPlot(block, lot);
}

function hideSearchResults() {
    document.getElementById('searchResults').classList.remove('active');
}

function highlightPlot(block, lot) {
    removeHighlight();
    const boxes = document.querySelectorAll('.lot-box');
    boxes.forEach(box => {
        const text = box.getAttribute('title');
        if (text && text.includes(`Block ${block}`) && text.includes(`Lot ${lot}`)) {
            box.style.outline = '4px solid #fbbf24';
            box.style.outlineOffset = '2px';
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

function removeHighlight() {
    const boxes = document.querySelectorAll('.lot-box');
    boxes.forEach(box => {
        box.style.outline = '';
        box.style.outlineOffset = '';
    });
}

function scaleMap() {
    const outer = document.getElementById('mapContainer');
    const scaler = document.getElementById('mapScaler');
    const inner = document.getElementById('mapInner');

    if (!outer || !scaler || !inner) return;

    scaler.style.transform = 'scale(1)';

    const availW = outer.clientWidth - 40;
    const availH = outer.clientHeight - 40;
    const natW = inner.scrollWidth;
    const natH = inner.scrollHeight;

    const scale = Math.min(availW / natW, availH / natH, 1);
    scaler.style.transform = `scale(${scale})`;
}

// Click outside to close search results
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) {
        hideSearchResults();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        loadCemeteryMap();
    }, 500);

    window.addEventListener('resize', scaleMap);
});
</script>
