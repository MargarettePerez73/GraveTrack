<?php
$pageTitle   = 'Cemetery Map';
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

    .search-results {
        position: absolute;
        top: 100%;
        left: 0; right: 0;
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

    .search-results.active { display: block; }

    .search-result-item {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.2s;
    }

    .search-result-item:hover { background: #f8fafc; }
    .search-result-item:last-child { border-bottom: none; }

    .search-result-name  { font-weight: 600; color: #1e3a8a; font-size: 13px; }
    .search-result-location { font-size: 11px; color: #64748b; margin-top: 2px; }
    .search-result-dates    { font-size: 11px; color: #94a3b8; margin-top: 2px; }

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

    .legend-box.vacant        { background: #10b981; border-color: #059669; }
    .legend-box.occupied      { background: #ef4444; border-color: #dc2626; }
    .legend-box.fully-paid    { background: #10b981; border-color: #059669; }
    .legend-box.partially-paid{ background: #f59e0b; border-color: #d97706; }
    .legend-box.unpaid        { background: #ef4444; border-color: #dc2626; }
    .legend-box.overdue       { background: #991b1b; border-color: #7f1d1d; }

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

    .lot-box.vacant        { background: #10b981; border-color: #059669; color: white; }
    .lot-box.occupied      { background: #ef4444; border-color: #dc2626; color: white; }
    .lot-box.fully-paid    { background: #10b981; border-color: #059669; color: white; }
    .lot-box.partially-paid{ background: #f59e0b; border-color: #d97706; color: white; }
    .lot-box.unpaid        { background: #ef4444; border-color: #dc2626; color: white; }
    .lot-box.overdue       { background: #991b1b; border-color: #7f1d1d; color: white; }

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
</style>

<div class="cemetery-layout">
    <!-- Sidebar -->
    <div class="cemetery-sidebar">
        <div class="sidebar-section">
            <div class="view-mode-badge" id="viewModeBadge">
                <i class="fas fa-user"></i> Loading...
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-search"></i> Search Deceased</h6>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Type name to search..." autocomplete="off">
                <i class="fas fa-search search-icon"></i>
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-map"></i> Legend</h6>

            <div id="engineerLegend" style="display:none;">
                <div class="legend-item">
                    <div class="legend-box vacant"></div>
                    <span>Vacant Plot</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box occupied"></div>
                    <span>Occupied Plot</span>
                </div>
            </div>

            <div id="treasurerLegend" style="display:none;">
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
let allPlots          = [];
let paymentData       = {};
let userRole          = '';
const LOTS_PER_BLOCK  = 20;
let allDeceasedRecords = [];
let searchTimeout     = null;

/* ─── Data loading ─── */

async function loadCemeteryMap() {
    try {
        const response = await fetch('/api/get_cemetery_map.php');
        const data     = await response.json();

        if (data.success) {
            allPlots = data.plots;
            await loadAllDeceasedRecords();

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
        let totalDeceased  = 0;

        for (const plot of allPlots) {
            if (plot.status === 'Occupied' && plot.deceased_count > 0) {
                const response = await fetch(`/api/get_lot_details.php?plot_id=${plot.plot_id}`);
                const data     = await response.json();

                if (data.success && data.deceased_records) {
                    data.deceased_records.forEach(record => {
                        allDeceasedRecords.push({
                            ...record,
                            plot_id: plot.plot_id,
                            block  : plot.block,
                            section: plot.section,
                            lot    : plot.lot,
                            phase  : plot.phase,
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
        const data     = await response.json();

        if (data.success) {
            data.data.forEach(payment => {
                paymentData[payment['Plot Location']] = payment['Status'];
            });
        }
    } catch (error) {
        console.error('Error loading payment data:', error);
    }
}

/* ─── Statistics ─── */

function updateStatistics() {
    const vacant   = allPlots.filter(p => p.status === 'Vacant').length;
    const occupied = allPlots.filter(p => p.status === 'Occupied').length;

    document.getElementById('totalPlots').textContent   = allPlots.length;
    document.getElementById('vacantPlots').textContent  = vacant;
    document.getElementById('occupiedPlots').textContent = occupied;
}

/* ─── Map rendering ─── */

function renderCemeteryMap() {
    userRole = currentUser ? currentUser.role : 'Engineer';

    document.getElementById('viewModeBadge').innerHTML =
        `<i class="fas fa-user"></i> ${userRole} View`;

    if (userRole === 'Treasurer') {
        document.getElementById('treasurerLegend').style.display = 'block';
        document.getElementById('engineerLegend').style.display  = 'none';
    } else {
        document.getElementById('engineerLegend').style.display  = 'block';
        document.getElementById('treasurerLegend').style.display = 'none';
    }

    /*
     * Group plots by phase and block.
     *
     * FIX: The API (get_cemetery_map.php) now correctly returns phase='Phase 3'
     * for block 'AA'. This client-side grouping therefore works correctly for
     * all phases without any additional changes here.
     *
     * Previously, block 'AA' came back with phase='Phase 1' due to the REGEXP
     * evaluation order bug in the API's CASE expression, which meant
     * phases['Phase 3']['AA'] was always empty and AA plots never appeared.
     */
    const phases = { 'Phase 1': {}, 'Phase 2': {}, 'Phase 3': {} };

    allPlots.forEach(plot => {
        const phase = plot.phase || 'Phase 1'; // safe default
        const block = plot.block;

        if (!phases[phase])        phases[phase]        = {};
        if (!phases[phase][block]) phases[phase][block] = [];

        phases[phase][block].push(plot);
    });

    function findPlot(block, lot, phase) {
        const plots = phases[phase] ? phases[phase][block] : null;
        if (!plots) return null;
        return plots.find(p => parseInt(p.lot) === lot) || null;
    }

    let html = '<div class="all-blocks" id="allBlocks">';

    // PHASE 3 — Block AA (20 lots) + unnamed overflow column (10 lots)
    html += renderBlockColumn('AA', 'Phase 3', findPlot, 20);
    html += renderBlockColumn('',   'Phase 3', findPlot, 10);

    html += '<div class="phase-divider"></div>';

    // PHASE 2 — Blocks T–Z
    const phase2Groups = [['Z','Y'], ['X','W'], ['V','U'], ['T']];
    phase2Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(b => { html += renderBlockColumn(b, 'Phase 2', findPlot, 20); });
    });

    html += '<div class="phase-divider"></div>';

    // PHASE 1 — Blocks A–I
    const phase1Groups = [['I','H'], ['G','F'], ['E','D'], ['C','B'], ['A']];
    phase1Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(b => { html += renderBlockColumn(b, 'Phase 1', findPlot, 20); });
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
    setTimeout(scaleMap, 100);
}

function renderBlockColumn(blockName, phaseName, findPlot, lotsCount = 20) {
    let html = '<div class="block-col">';
    html += `<div class="block-label">${blockName || '&nbsp;'}</div>`;
    html += '<div class="plots-stack">';

    for (let lot = lotsCount; lot >= 1; lot--) {
        const plot        = findPlot(blockName, lot, phaseName);
        const colorClass  = plot ? getPlotColorClass(plot) : 'vacant';
        const plotId      = plot ? plot.plot_id : null;
        const displayBlock = blockName || 'Unnamed';

        const tooltip = plot
            ? `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot} - ${plot.status}`
            : `Block ${displayBlock}, Lot ${lot} - Vacant`;

        // All lot boxes have a working onclick — plotId may be null for truly
        // vacant plots that have no DB row yet; viewPlotDetails handles that case.
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
    if (userRole === 'Treasurer') {
        if (plot.status === 'Vacant') return 'vacant';

        const key           = `${plot.block} - ${plot.section} - ${plot.lot}`;
        const paymentStatus = paymentData[key];

        if (paymentStatus === 'Paid')    return 'fully-paid';
        if (paymentStatus === 'Overdue') return 'overdue';
        if (paymentStatus === 'Pending') return 'partially-paid';
        return 'unpaid';
    }

    return plot.status === 'Vacant' ? 'vacant' : 'occupied';
}

/* ─── Plot detail modal ─── */

async function viewPlotDetails(plotId, blockName, lotNumber, phaseName) {
    // Vacant plot with no DB record
    if (!plotId || plotId === null || plotId === 'null') {
        const displayBlock = blockName || 'Unnamed';

        document.getElementById('modalTitle').textContent =
            `Plot: Block ${displayBlock}, Lot ${lotNumber} (${phaseName})`;

        let vacantContent = `
            <div class="alert alert-info">
                <p><strong>Vacant Plot</strong></p>
                <p>This plot is currently vacant and available for burial.</p>
                <p><em>Phase: ${phaseName} | Block: ${displayBlock} | Lot: ${lotNumber}</em></p>
            </div>
        `;

        // Engineer can add burial record to vacant plot
        if (userRole === 'Engineer') {
            // For truly vacant plots with no DB record, we can't link via plot_id
            // Display message instead
            vacantContent += `
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle"></i> This plot exists on the map but has no database record yet. 
                    <a href="adding_burial_records.php" class="btn btn-success btn-sm mt-2">
                        <i class="fas fa-plus"></i> Add Burial Record
                    </a>
                </div>
            `;
        } else if (userRole === 'Treasurer') {
            vacantContent += `
                <div class="alert alert-secondary mt-3">
                    <i class="fas fa-lock"></i> No payment records for vacant plots.
                </div>
            `;
        }

        document.getElementById('plotModalContent').innerHTML = vacantContent;
        document.getElementById('editBtn').style.display = 'none';

        new bootstrap.Modal(document.getElementById('plotModal')).show();
        return;
    }

    try {
        const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
        const data     = await response.json();

        if (!data.success) throw new Error(data.message || 'Failed to load plot details');

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

        // Role-based content display
        if (data.userRole === 'Engineer') {
            // Engineer view - show full burial records with edit/delete options
            if (data.deceased_records && data.deceased_records.length > 0) {
                content += '<hr><h6><strong>Deceased Records:</strong></h6>';
                content += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                content += '<thead><tr><th>Name</th><th>Date of Death</th><th>Contact</th><th>Actions</th></tr></thead><tbody>';

                data.deceased_records.forEach(record => {
                    content += `
                        <tr>
                            <td><strong>${record.full_name}</strong></td>
                            <td>${formatDate(record.date_of_death)}</td>
                            <td>${record.contact_person || 'N/A'}<br>
                                <small>${record.contact_number || ''}</small></td>
                            <td>
                                <a href="edit_burial_record.php?id=${record.deceased_id}"
                                   class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-danger"
                                        onclick="deleteDeceasedRecord(${record.deceased_id}, '${record.full_name}')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                });

                content += '</tbody></table></div>';
            } else {
                content += '<hr><p class="text-muted text-center">No deceased records for this plot</p>';
                content += `<div class="text-center mt-3">
                    <a href="adding_burial_records.php?plot_id=${plotId}"
                       class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Add Burial Record
                    </a>
                </div>`;
            }

        } else if (data.userRole === 'Treasurer') {
            // Treasurer view - show only payment information
            if (data.deceased_records && data.deceased_records.length > 0) {
                content += '<hr><h6><strong>Burial Records (Payment Information):</strong></h6>';
                content += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                content += '<thead><tr><th>Name</th><th>Date of Burial</th><th>Payment Status</th></tr></thead><tbody>';

                data.deceased_records.forEach(record => {
                    const key = `${data.plot.block} - ${data.plot.section} - ${data.plot.lot}`;
                    const paymentStatus = paymentData[key] || 'Unknown';
                    const statusBadgeColor = 
                        paymentStatus === 'Paid' ? 'success' :
                        paymentStatus === 'Pending' ? 'warning' :
                        paymentStatus === 'Overdue' ? 'danger' : 'secondary';

                    content += `
                        <tr>
                            <td><strong>${record.full_name}</strong></td>
                            <td>${formatDate(record.date_of_burial)}</td>
                            <td><span class="badge bg-${statusBadgeColor}">${paymentStatus}</span></td>
                        </tr>
                    `;
                });

                content += '</tbody></table></div>';
            } else {
                content += '<hr><p class="text-muted text-center">No burial records for this plot</p>';
            }
        }

        document.getElementById('plotModalContent').innerHTML = content;
        document.getElementById('editBtn').style.display = 'none';

        new bootstrap.Modal(document.getElementById('plotModal')).show();

    } catch (error) {
        console.error('Error fetching plot details:', error);
        document.getElementById('plotModalContent').innerHTML =
            `<div class="alert alert-danger">Error loading plot details: ${error.message}</div>`;
        new bootstrap.Modal(document.getElementById('plotModal')).show();
    }
}

/* ─── Delete Deceased Record ─── */

async function deleteDeceasedRecord(deceasedId, fullName) {
    // Only engineers can delete records
    if (userRole !== 'Engineer') {
        alert('Only engineers can delete burial records.');
        return;
    }

    const confirmed = await Swal.fire({
        title: 'Delete Record?',
        text: `Are you sure you want to delete the burial record for ${fullName}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    });

    if (confirmed.isConfirmed) {
        try {
            const response = await fetch('/api/delete_burial_record.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({ deceased_id: deceasedId })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Deleted!', 'Burial record has been deleted successfully.', 'success');
                setTimeout(() => {
                    document.getElementById('plotModal').closest('.modal').click(); // Close modal
                    loadCemeteryMap(); // Reload map
                }, 1500);
            } else {
                Swal.fire('Error', data.message || 'Failed to delete record', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Swal.fire('Error', error.message || 'An error occurred while deleting the record', 'error');
        }
    }
}

/* ─── Helpers ─── */

function getStatusBadge(status) {
    const colour = status === 'Vacant' ? 'success' : 'danger';
    return `<span class="badge bg-${colour}">${status}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const d = new Date(dateString);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

/* ─── Live search ─── */

document.getElementById('searchInput').addEventListener('input', function () {
    const searchTerm = this.value.trim();
    clearTimeout(searchTimeout);

    if (searchTerm.length < 2) { hideSearchResults(); return; }

    searchTimeout = setTimeout(() => performLiveSearch(searchTerm), 300);
});

async function performLiveSearch(searchTerm) {
    try {
        const response = await fetch(`/api/search_deceased.php?q=${encodeURIComponent(searchTerm)}`);
        const data     = await response.json();

        if (data.success) displaySearchResults(data.results);
    } catch (error) {
        console.error('Search error:', error);
    }
}

function displaySearchResults(results) {
    const container = document.getElementById('searchResults');

    if (!results || results.length === 0) {
        container.innerHTML = '<div class="no-results">No deceased found</div>';
        container.classList.add('active');
        return;
    }

    container.innerHTML = results.map(record => `
        <div class="search-result-item"
             onclick="selectSearchResult(${record.plot_id}, '${record.block}', ${record.lot})">
            <div class="search-result-name">${record.full_name}</div>
            <div class="search-result-location">
                <i class="fas fa-map-marker-alt"></i>
                Block ${record.block}, Section ${record.section}, Lot ${record.lot}
            </div>
            <div class="search-result-dates">
                <i class="fas fa-calendar"></i> ${formatDate(record.date_of_death)}
            </div>
        </div>
    `).join('');

    container.classList.add('active');
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
    document.querySelectorAll('.lot-box').forEach(box => {
        const t = box.getAttribute('title');
        if (t && t.includes(`Block ${block}`) && t.includes(`Lot ${lot}`)) {
            box.style.outline       = '4px solid #fbbf24';
            box.style.outlineOffset = '2px';
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

function removeHighlight() {
    document.querySelectorAll('.lot-box').forEach(box => {
        box.style.outline = box.style.outlineOffset = '';
    });
}

/* ─── Map scaling ─── */

function scaleMap() {
    const outer  = document.getElementById('mapContainer');
    const scaler = document.getElementById('mapScaler');
    const inner  = document.getElementById('mapInner');

    if (!outer || !scaler || !inner) return;

    scaler.style.transform = 'scale(1)';

    const scale = Math.min(
        (outer.clientWidth  - 40) / inner.scrollWidth,
        (outer.clientHeight - 40) / inner.scrollHeight,
        1
    );

    scaler.style.transform = `scale(${scale})`;
}

/* ─── Click-outside closes search ─── */

document.addEventListener('click', e => {
    if (!e.target.closest('.search-box')) hideSearchResults();
});

/* ─── Initialise ─── */

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(loadCemeteryMap, 500);
    window.addEventListener('resize', scaleMap);
});
</script>