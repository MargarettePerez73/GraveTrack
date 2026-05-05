<?php
$pageTitle   = 'Cemetery Map';
$currentPage = 'cemetery_map';
include 'includes/header.php';
?>

<style>
    *, *::before, *::after { box-sizing: border-box; }

    .overdue-compact {
        padding: 0.75rem 1rem !important;
        margin-bottom: 1rem !important;
        font-size: 0.875rem;
    }
    .overdue-compact .btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

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

    /* Search highlight/blur effects */
    .lot-box.highlighted {
        box-shadow: 0 0 0 4px #fbbf24, 0 0 20px rgba(251,191,36,0.8) !important;
        transform: scale(1.3) !important;
        z-index: 1000 !important;
        background: linear-gradient(45deg, #fbbf24, #f59e0b) !important;
        border-color: #d97706 !important;
        animation: pulse 1.5s infinite !important;
    }

    .lot-box.blurred {
        filter: blur(1.5px) !important;
        opacity: 0.4 !important;
        transform: scale(0.95) !important;
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

    /* ── Payment Modal Styles ── */
    #paymentModal .modal-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white;
        border-radius: 0.375rem 0.375rem 0 0;
    }
    #paymentModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    #paymentModal .modal-title {
        font-weight: 700;
        font-size: 15px;
    }
    .payment-plot-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #1e40af;
    }
    .payment-plot-info strong { font-size: 14px; }
    .payment-form-group {
        margin-bottom: 16px;
    }
    .payment-form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .payment-form-group label .required-star {
        color: #ef4444;
        margin-left: 2px;
    }
    .payment-form-group input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
        color: #111827;
    }
    .payment-form-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .payment-form-group input.is-invalid {
        border-color: #ef4444;
    }
    .payment-form-group .invalid-feedback {
        display: none;
        font-size: 11px;
        color: #ef4444;
        margin-top: 4px;
    }
    .payment-form-group input.is-invalid + .invalid-feedback,
    .payment-form-group input.is-invalid ~ .invalid-feedback {
        display: block;
    }
    .payment-amount-prefix {
        position: relative;
    }
    .payment-amount-prefix span {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-weight: 700;
        font-size: 14px;
        pointer-events: none;
    }
    .payment-amount-prefix input {
        padding-left: 28px;
    }
    #submitPaymentBtn {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        border: none;
        font-weight: 700;
        padding: 10px 24px;
        border-radius: 8px;
        transition: opacity 0.2s, transform 0.1s;
    }
    #submitPaymentBtn:hover { opacity: 0.9; transform: translateY(-1px); }
    #submitPaymentBtn:active { transform: translateY(0); }
    #submitPaymentBtn:disabled { opacity: 0.6; cursor: not-allowed; }
    .payment-success-msg {
        display: none;
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        padding: 12px 16px;
        color: #065f46;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
    }
    .payment-error-msg {
        display: none;
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        padding: 12px 16px;
        color: #991b1b;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
    }
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
                    <span>Paid (Full 3-Year)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box fully-paid"></div>
                    <span>Paid</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box unpaid"></div>
                    <span>Unpaid</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box overdue"></div>
                    <span>Overdue (Penalty Applied)</span>
                </div>
                <div class="mt-3">
                    <a href="payment_monitoring.php?overdue=true" class="btn btn-warning btn-sm w-100" target="_blank">
                        <i class="fas fa-exclamation-triangle"></i> View All Overdue Payments
                    </a>
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

<!-- ══════════════════════════════════════════
     PAYMENT MODAL
     ══════════════════════════════════════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-dollar-sign me-2"></i> Record Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <!-- Plot context info -->
                <div class="payment-plot-info" id="paymentPlotInfo">
                    <strong id="paymentPlotTitle">Plot Details</strong><br>
                    <span id="paymentPlotSubtitle" class="text-muted" style="font-size:12px;"></span>
                </div>

                <!-- OR Number -->
                <div class="payment-form-group">
                    <label for="payOrNumber">
                        OR Number <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="payOrNumber"
                           placeholder="e.g. OR-2024-001234"
                           maxlength="50">
                    <div class="invalid-feedback">OR Number is required.</div>
                </div>

                <!-- Paid By -->
                <div class="payment-form-group">
                    <label for="payPaidBy">
                        Paid By <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="payPaidBy"
                           placeholder="Full name of payer"
                           maxlength="100">
                    <div class="invalid-feedback">Payer name is required.</div>
                </div>

                <!-- Amount -->
                <div class="payment-form-group">
                    <label for="payAmount">
                        Amount <span class="required-star">*</span>
                    </label>
                    <div class="payment-amount-prefix">
                        <span>₱</span>
                        <input type="number"
                               id="payAmount"
                               placeholder="0.00"
                               min="0.01"
                               step="0.01">
                    </div>
                    <div class="invalid-feedback">A valid amount greater than 0 is required.</div>
                </div>

                <!-- Payment Date -->
                <div class="payment-form-group">
                    <label for="payDate">
                        Payment Date <span class="required-star">*</span>
                    </label>
                    <input type="date" id="payDate">
                    <div class="invalid-feedback">Payment date is required.</div>
                </div>

                <!-- Hidden fields used for API payload -->
                <input type="hidden" id="payPlotId">
                <input type="hidden" id="payRentalId">
                <input type="hidden" id="payDeceasedId">

                <!-- Feedback messages -->
                <div class="payment-success-msg" id="paySuccessMsg">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="paySuccessText">Payment recorded successfully!</span>
                </div>
                <div class="payment-error-msg" id="payErrorMsg">
                    <i class="fas fa-times-circle me-2"></i>
                    <span id="payErrorText">An error occurred. Please try again.</span>
                </div>

            </div><!-- /modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary text-white" id="submitPaymentBtn"
                        onclick="submitPayment()">
                    <i class="fas fa-save me-1"></i> Save Payment
                </button>
            </div>

        </div>
    </div>
</div>
<!-- ══════════════════════════════════════════ -->

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

    const phases = { 'Phase 1': {}, 'Phase 2': {}, 'Phase 3': {} };

    allPlots.forEach(plot => {
        const phase = plot.phase || 'Phase 1';
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

    html += renderBlockColumn('AA', 'Phase 3', findPlot, 20);
    html += renderBlockColumn('',   'Phase 3', findPlot, 10);

    html += '<div class="phase-divider"></div>';

    const phase2Groups = [['Z','Y'], ['X','W'], ['V','U'], ['T']];
    phase2Groups.forEach((group, gi) => {
        if (gi > 0) html += '<div class="pair-gap"></div>';
        group.forEach(b => { html += renderBlockColumn(b, 'Phase 2', findPlot, 20); });
    });

    html += '<div class="phase-divider"></div>';

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
        const plotSection = plot ? plot.section : '';

        const tooltip = plot
            ? `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot} - ${plot.status}`
            : `Block ${displayBlock}, Lot ${lot} - Vacant`;

        html += `
            <div class="lot-box ${colorClass}"
                 title="${tooltip}"
                 data-block="${displayBlock}"
                 data-section="${plotSection}"
                 data-lot="${lot}"
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

        if (paymentStatus === 'Paid' || paymentStatus === 'Partially Paid') return 'fully-paid';
        if (paymentStatus === 'Overdue' || paymentStatus === 'Overdue - Partial') return 'overdue';
        return 'unpaid';
    }

    return plot.status === 'Vacant' ? 'vacant' : 'occupied';
}

/* ─── Plot detail modal ─── */

async function viewPlotDetails(plotId, blockName, lotNumber, phaseName) {
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

        if (userRole === 'Engineer') {
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

        if (data.userRole === 'Engineer') {
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
            if (data.deceased_records && data.deceased_records.length > 0) {
                content += '<hr><h6><strong>Burial & Payment Records:</strong></h6>';
                content += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                content += '<thead><tr><th>Name</th><th>Buried</th><th>Rental Period</th><th>Status</th></tr></thead><tbody>';

                let isOverdue = false;
                const paymentTargetRecord = getPaymentTargetRecord(data.deceased_records);
                const paymentTargetName = paymentTargetRecord
                    ? escapeJsString(paymentTargetRecord.full_name || '')
                    : '';

                data.deceased_records.forEach(record => {
                    const key = `${data.plot.block} - ${data.plot.section} - ${data.plot.lot}`;
                    const paymentStatus = paymentData[key] || data.plot.payment_status || 'Unknown';
                    const displayPaymentStatus = paymentStatus === 'Partially Paid' ? 'Paid' : paymentStatus;
                    const statusBadgeColor =
                        displayPaymentStatus === 'Paid'              ? 'success' :
                        paymentStatus === 'Overdue'           ? 'danger'  :
                        paymentStatus === 'Overdue - Partial' ? 'danger'  : 'secondary';

                    const rentalInfo = record.rental_end_date
                        ? `3 years (ends ${formatDate(record.rental_end_date)})`
                        : 'No rental record';

                    if (paymentStatus === 'Overdue' || paymentStatus === 'Overdue - Partial') {
                        isOverdue = true;
                    }

                    content += `
                        <tr>
                            <td><strong>${record.full_name}</strong></td>
                            <td>${formatDate(record.date_of_burial)}</td>
                            <td><small>${rentalInfo}</small></td>
                            <td><span class="badge bg-${statusBadgeColor}">${displayPaymentStatus}</span></td>
                        </tr>
                    `;
                });

                content += '</tbody></table></div>';

                if (isOverdue) {
                    content += `
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Overdue rental payments — penalty applied</strong>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="payment_monitoring.php?plot_id=${plotId}&block=${data.plot.block}&section=${data.plot.section}&lot=${data.plot.lot}&overdue=true"
                                   class="btn btn-warning flex-fill">
                                    <i class="fas fa-dollar-sign"></i> View Payments
                                </a>
                                <button class="btn btn-outline-warning"
                                        onclick="openPaymentModal(${plotId}, '${data.plot.block}', '${data.plot.section}', '${data.plot.lot}', ${paymentTargetRecord ? paymentTargetRecord.rental_id : 'null'}, ${paymentTargetRecord ? paymentTargetRecord.deceased_id : 'null'}, '${paymentTargetName}')">
                                    <i class="fas fa-plus"></i> Record Payment
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    /* Non-overdue occupied plot: still allow recording payment */
                    content += `
                        <div class="text-end mt-3">
                            <button class="btn btn-outline-primary btn-sm"
                                    onclick="openPaymentModal(${plotId}, '${data.plot.block}', '${data.plot.section}', '${data.plot.lot}', ${paymentTargetRecord ? paymentTargetRecord.rental_id : 'null'}, ${paymentTargetRecord ? paymentTargetRecord.deceased_id : 'null'}, '${paymentTargetName}')">
                                <i class="fas fa-plus me-1"></i> Record Payment
                            </button>
                        </div>
                    `;
                }

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
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ deceased_id: deceasedId })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Deleted!', 'Burial record has been deleted successfully.', 'success');
                setTimeout(() => {
                    document.getElementById('plotModal').closest('.modal').click();
                    loadCemeteryMap();
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

/* ══════════════════════════════════════════════════════
   PAYMENT MODAL LOGIC
   ══════════════════════════════════════════════════════ */

/**
 * Opens the payment modal, pre-filling plot context.
 * Called from the "Record Payment" button injected inside the plot detail modal.
 */
function openPaymentModal(plotId, block, section, lot, rentalId = null, deceasedId = null, deceasedName = '') {
    /* Reset the form */
    ['payOrNumber', 'payPaidBy', 'payAmount', 'payDate'].forEach(id => {
        const el = document.getElementById(id);
        el.value = '';
        el.classList.remove('is-invalid');
    });

    document.getElementById('paySuccessMsg').style.display = 'none';
    document.getElementById('payErrorMsg').style.display   = 'none';
    document.getElementById('submitPaymentBtn').disabled   = false;

    /* Set today's date as default */
    document.getElementById('payDate').value = new Date().toISOString().split('T')[0];

    /* Fill plot context */
    document.getElementById('payPlotId').value = plotId;
    document.getElementById('payRentalId').value = rentalId || '';
    document.getElementById('payDeceasedId').value = deceasedId || '';
    document.getElementById('paymentPlotTitle').textContent =
        `Block ${block}, Section ${section}, Lot ${lot}`;
    document.getElementById('paymentPlotSubtitle').textContent =
        deceasedName
            ? `Plot ID: ${plotId} | Deceased: ${deceasedName}`
            : `Plot ID: ${plotId}`;

    /* Hide the plot modal and show the payment modal */
    const plotModalEl = document.getElementById('plotModal');
    const plotModal   = bootstrap.Modal.getInstance(plotModalEl);
    if (plotModal) plotModal.hide();

    setTimeout(() => {
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }, 300);
}

/** Validates the payment form; returns true if valid. */
function validatePaymentForm() {
    let valid = true;

    const orNumber = document.getElementById('payOrNumber');
    const paidBy   = document.getElementById('payPaidBy');
    const amount   = document.getElementById('payAmount');
    const date     = document.getElementById('payDate');

    if (!orNumber.value.trim()) {
        orNumber.classList.add('is-invalid'); valid = false;
    } else { orNumber.classList.remove('is-invalid'); }

    if (!paidBy.value.trim()) {
        paidBy.classList.add('is-invalid'); valid = false;
    } else { paidBy.classList.remove('is-invalid'); }

    if (!amount.value || parseFloat(amount.value) <= 0) {
        amount.classList.add('is-invalid'); valid = false;
    } else { amount.classList.remove('is-invalid'); }

    if (!date.value) {
        date.classList.add('is-invalid'); valid = false;
    } else { date.classList.remove('is-invalid'); }

    return valid;
}

/** Submits the payment to /api/add_payment.php */
async function submitPayment() {
    if (!validatePaymentForm()) return;

    const rentalId = document.getElementById('payRentalId').value;
    const deceasedId = document.getElementById('payDeceasedId').value;
    if (!rentalId || !deceasedId) {
        const errorEl = document.getElementById('payErrorMsg');
        document.getElementById('payErrorText').textContent =
            'Cannot save payment because rental details are missing for this plot.';
        errorEl.style.display = 'flex';
        return;
    }

    const btn = document.getElementById('submitPaymentBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    document.getElementById('paySuccessMsg').style.display = 'none';
    document.getElementById('payErrorMsg').style.display   = 'none';

    const payload = {
        plot_id  : document.getElementById('payPlotId').value,
        rental_id: parseInt(rentalId, 10),
        deceased_id: parseInt(deceasedId, 10),
        or_number: document.getElementById('payOrNumber').value.trim(),
        paid_by  : document.getElementById('payPaidBy').value.trim(),
        amount   : parseFloat(document.getElementById('payAmount').value),
        payment_date: document.getElementById('payDate').value,
    };

    try {
        const response = await fetch('/api/add_payment.php', {
            method     : 'POST',
            headers    : { 'Content-Type': 'application/json' },
            credentials: 'include',
            body       : JSON.stringify(payload),
        });

        const data = await response.json();

        if (data.success) {
            const successEl = document.getElementById('paySuccessMsg');
            document.getElementById('paySuccessText').textContent =
                data.message || 'Payment recorded successfully!';
            successEl.style.display = 'flex';

            /* Reload payment data in background then close modal */
            await loadPaymentData();
            renderCemeteryMap();

            setTimeout(() => {
                bootstrap.Modal.getInstance(
                    document.getElementById('paymentModal')
                ).hide();
            }, 1800);
        } else {
            const errorEl = document.getElementById('payErrorMsg');
            document.getElementById('payErrorText').textContent =
                data.message || 'Failed to record payment. Please try again.';
            errorEl.style.display = 'flex';

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Payment';
        }
    } catch (error) {
        console.error('Payment submission error:', error);
        const errorEl = document.getElementById('payErrorMsg');
        document.getElementById('payErrorText').textContent =
            'Network error. Please check your connection and try again.';
        errorEl.style.display = 'flex';

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Payment';
    }
}

function getPaymentTargetRecord(records) {
    if (!Array.isArray(records) || records.length === 0) return null;

    // Use a record with a rental first since payment API requires rental_id.
    const withRental = records.find(record => record.rental_id);
    return withRental || records[0];
}

function escapeJsString(value) {
    return String(value)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");
}

/* ══════════════════════════════════════════════════════ */

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

    if (searchTerm.length < 2) {
        hideSearchResults();
        clearHighlight();
        return;
    }

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
        clearHighlight();
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
    highlightPlotsFromResults(results);
}

function selectSearchResult(plotId, block, lot) {
    hideSearchResults();
    document.getElementById('searchInput').value = '';
    viewPlotDetails(plotId, block, lot, '');

    setTimeout(() => {
        highlightPlot(block, lot);
    }, 500);
}

function hideSearchResults() {
    document.getElementById('searchResults').classList.remove('active');
}

function clearHighlight() {
    document.querySelectorAll('.lot-box').forEach(box => {
        box.classList.remove('highlighted', 'blurred');
    });
}

function highlightPlot(block, lot) {
    clearHighlight();

    let foundMatch = false;
    const blockStr = String(block);
    const lotStr = String(lot);

    document.querySelectorAll('.lot-box').forEach(box => {
        const boxBlock = box.dataset.block || '';
        const boxLot = box.dataset.lot || '';
        if (boxBlock === blockStr && boxLot === lotStr) {
            box.classList.add('highlighted');
            box.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            foundMatch = true;
        } else {
            box.classList.add('blurred');
        }
    });

    if (!foundMatch) clearHighlight();
}

function highlightPlotsFromResults(results) {
    const matchedLocations = new Set(
        (results || []).map(record => `${String(record.block)}|${String(record.section)}|${String(record.lot)}`)
    );

    if (matchedLocations.size === 0) {
        clearHighlight();
        return;
    }

    let firstMatch = null;

    document.querySelectorAll('.lot-box').forEach(box => {
        const key = `${box.dataset.block || ''}|${box.dataset.section || ''}|${box.dataset.lot || ''}`;

        if (matchedLocations.has(key)) {
            box.classList.add('highlighted');
            box.classList.remove('blurred');
            if (!firstMatch) firstMatch = box;
        } else {
            box.classList.remove('highlighted');
            box.classList.add('blurred');
        }
    });

    if (firstMatch) {
        firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    }
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

/* ─── Click-outside closes search & clear highlight ─── */

document.addEventListener('click', e => {
    if (!e.target.closest('.search-box')) {
        hideSearchResults();
        clearHighlight();
    }
});

/* ─── Keyboard shortcuts ─── */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        clearHighlight();
        document.getElementById('searchInput').value = '';
        hideSearchResults();
    }
});

/* ─── Clear on new search ─── */
document.getElementById('searchInput').addEventListener('focus', () => {
    clearHighlight();
});

/* ─── Initialise ─── */

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(loadCemeteryMap, 500);
    window.addEventListener('resize', scaleMap);
});
</script>