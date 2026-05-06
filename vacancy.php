<?php
$pageTitle = 'Vacancy Monitoring';
$currentPage = 'vacancy';
include 'includes/header.php';
?>

<style>
    .vacancy-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .vacancy-sidebar {
        width: 280px;
        background: white;
        border-right: 3px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 4px 0 12px rgba(0,0,0,0.05);
        color: #1e3a8a;
    }

    .sidebar-header {
        padding: 25px 20px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
    }

    .sidebar-header h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: #1e3a8a;
    }

    .sidebar-section {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-section h6 {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        opacity: 0.7;
    }

    .sidebar-stat {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #3b82f6;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .sidebar-stat:hover {
        background: rgba(255,255,255,0.15);
        transform: translateX(5px);
    }

    .sidebar-stat-label {
        font-size: 10px;
        opacity: 0.9;
        margin-bottom: 3px;
    }

    .sidebar-stat-value {
        font-size: 20px;
        font-weight: 700;
    }

    .filter-group {
        margin-bottom: 12px;
    }

    .filter-label {
        font-size: 11px;
        font-weight: 600;
        color: #1e3a8a;
        margin-bottom: 6px;
        display: block;
    }

    .filter-input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        color: #1e3a8a;
        transition: all 0.3s;
    }

    .filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .vacancy-main {
        flex: 1;
        overflow-y: auto;
        background: #f8fafc;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 26px;
        font-weight: 800;
        color: #1e3a8a;
        margin: 0;
    }

    .content-card-compact {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }

    .content-card-header {
        padding: 12px 20px;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .content-card-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .content-card-body {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
    }

    .compact-table {
        width: 100%;
        font-size: 12px;
    }

    .compact-table thead th {
        background: #f8fafc;
        color: #1e3a8a;
        font-weight: 700;
        padding: 10px 8px;
        font-size: 11px;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .compact-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
    }

    .compact-table tbody tr:hover {
        background: #f8fafc;
    }

    .compact-table tbody td {
        padding: 10px 8px;
        color: #334155;
    }

    .record-count-badge {
        background: #dbeafe;
        color: #1e3a8a;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-view {
        padding: 5px 12px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-clear {
        width: 100%;
        padding: 12px;
        background: #64748b;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        margin-top: 10px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-clear:hover {
        background: #475569;
        transform: translateY(-2px);
    }

    /* ── Plot Details Modal (compact cards) ── */
    #plotDetailsModal .modal-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: #fff;
        border-radius: 0.375rem 0.375rem 0 0;
    }
    #plotDetailsModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    #plotDetailsModal .modal-title {
        font-weight: 800;
        font-size: 15px;
    }
    .vplot-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    @media (max-width: 576px) {
        .vplot-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .vplot-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        min-width: 0;
    }
    .vplot-label {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        margin-bottom: 4px;
        display: block;
    }
    .vplot-value {
        font-size: 13px;
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .vdeceased-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    @media (max-width: 768px) {
        .vdeceased-grid { grid-template-columns: 1fr; }
    }
    .vdeceased-name {
        font-size: 13px;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 6px;
        line-height: 1.2;
    }
    .vdeceased-meta {
        font-size: 12px;
        color: #475569;
        margin: 0;
        line-height: 1.35;
    }
    .vdeceased-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
        gap: 8px;
        flex-wrap: wrap;
    }
    .vdeceased-actions .btn {
        padding: 0.35rem 0.55rem;
        font-size: 0.78rem;
        font-weight: 800;
    }
</style>

<div class="vacancy-layout">
    <!-- Sidebar -->
    <div class="vacancy-sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-map-marked-alt"></i> Vacancy Monitoring</h4>
        </div>

        <div class="sidebar-section">
            <h6>Statistics</h6>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Vacant Plots</div>
                <div class="sidebar-stat-value" id="sidebarVacant">0</div>
            </div>
            <div class="sidebar-stat">  
                <div class="sidebar-stat-label">Occupied Plots</div>
                <div class="sidebar-stat-value" id="sidebarOccupied">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Total Plots</div>
                <div class="sidebar-stat-value" id="sidebarTotal">0</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Filters</h6>
            <div class="filter-group">
                <label class="filter-label">Block</label>
                <select class="filter-input" id="filterBlock" onchange="applyFilters()">
                    <option value="">All Blocks</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Type</label>
                <select class="filter-input" id="filterType" onchange="applyFilters()">
                    <option value="">All Types</option>
                    <option value="Single">Single</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Mausoleum">Mausoleum</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="filter-input" id="filterStatus" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option value="Vacant">Vacant</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Reserved">Reserved</option>
                </select>
            </div>

            <button class="btn-clear" onclick="clearFilters()">
                <i class="fas fa-redo"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="vacancy-main">
        <div class="page-header">
            <h1><i class="fas fa-chart-bar"></i> Plot Availability</h1>
        </div>

        <!-- Plots Table -->
        <div class="content-card-compact">
            <div class="content-card-header">
                <h5><i class="fas fa-table"></i> Plot Listing</h5>
                <span class="record-count-badge" id="recordCount">0 records</span>
            </div>
            <div class="content-card-body">
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>Plot ID</th>
                            <th>Block</th>
                            <th>Section</th>
                            <th>Lot</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="plotsTableBody">
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px;">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2" style="color: #64748b;">Loading plots...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Plot Details Modal -->
<div class="modal fade" id="plotDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Plot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="plotDetailsContent">Loading...</div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allPlots = [];
    let blocks = [];

    async function loadVacancyData() {
        try {
            const response = await fetch('/api/get_vacancy_stats.php');
            const data = await response.json();

            if (data.success) {
                allPlots = data.plots;
                blocks = data.blocks;

                document.getElementById('sidebarVacant').textContent = data.stats.total_vacant;
                document.getElementById('sidebarOccupied').textContent = data.stats.total_occupied;
                document.getElementById('sidebarTotal').textContent = data.stats.total_plots;

                // Populate block filter - sort alphabetically and set default to A
                const blockFilter = document.getElementById('filterBlock');
                
                // Sort blocks alphabetically
                const sortedBlocks = blocks.sort((a, b) => a.localeCompare(b));
                
                // Clear existing options except the initial A
                blockFilter.innerHTML = '<option value="A">Block A</option>';
                
                // Add sorted blocks (skip A to avoid duplicate with initial option)
                sortedBlocks.forEach(block => {
                    if (block !== 'A') {
                        const option = document.createElement('option');
                        option.value = block;
                        option.textContent = `Block ${block}`;
                        blockFilter.appendChild(option);
                    }
                });
                
                // Set default to A
                blockFilter.value = 'A';
                
                // Auto-apply default filter
                applyFilters();
            }
        } catch (error) {
            console.error('Error loading vacancy data:', error);
            document.getElementById('plotsTableBody').innerHTML =
                '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
        }
    }

    function displayPlots(plots) {
        const tbody = document.getElementById('plotsTableBody');
        tbody.innerHTML = '';

        if (plots.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 30px; color: #94a3b8;">No plots found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        plots.forEach(plot => {
            const row = `
                <tr>
                    <td>${plot.plot_id}</td>
                    <td><strong>Block ${plot.block}</strong></td>
                    <td>${plot.section}</td>
                    <td>${plot.lot}</td>
                    <td>${plot.type}</td>
                    <td>${getStatusBadge(plot.status)}</td>
                    <td>${formatDate(plot.date_added)}</td>
                    <td>
                        <button class="btn-view" onclick="viewPlotDetails(${plot.plot_id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('recordCount').textContent = `${plots.length} records`;
    }

    function applyFilters() {
        const block = document.getElementById('filterBlock').value;
        const type = document.getElementById('filterType').value;
        const status = document.getElementById('filterStatus').value;

        let filtered = allPlots;

        if (block) {
            filtered = filtered.filter(p => p.block === block);
        }
        if (type) {
            filtered = filtered.filter(p => p.type === type);
        }
        if (status) {
            filtered = filtered.filter(p => p.status === status);
        }

        displayPlots(filtered);
    }

    function clearFilters() {
        document.getElementById('filterBlock').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        displayPlots(allPlots);
    }

    async function viewPlotDetails(plotId) {
        try {
            const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
            const data = await response.json();

            if (data.success) {
                const plot = data.plot || {};
                const deceased = Array.isArray(data.deceased_records) ? data.deceased_records : [];

                const statusText = plot.status || 'Unknown';
                const statusBadge = getStatusBadge(statusText);

                let html = `
                    <div class="vplot-meta-grid">
                        <div class="vplot-card">
                            <span class="vplot-label">Block</span>
                            <div class="vplot-value">${plot.block ?? 'N/A'}</div>
                        </div>
                        <div class="vplot-card">
                            <span class="vplot-label">Section</span>
                            <div class="vplot-value">${plot.section ?? 'N/A'}</div>
                        </div>
                        <div class="vplot-card">
                            <span class="vplot-label">Lot</span>
                            <div class="vplot-value">${plot.lot ?? 'N/A'}</div>
                        </div>
                        <div class="vplot-card">
                            <span class="vplot-label">Type</span>
                            <div class="vplot-value">${plot.type ?? 'N/A'}</div>
                        </div>
                        <div class="vplot-card">
                            <span class="vplot-label">Status</span>
                            <div class="vplot-value">${stripHtml(statusBadge)}</div>
                        </div>
                        <div class="vplot-card">
                            <span class="vplot-label">Date Added</span>
                            <div class="vplot-value">${formatDate(plot.date_added)}</div>
                        </div>
                    </div>
                `;

                if (deceased.length > 0) {
                    html += `
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0" style="font-weight:900;color:#1e3a8a;">
                                <i class="fas fa-user me-2"></i>Deceased Records
                            </h6>
                            <span class="badge bg-primary">${deceased.length}</span>
                        </div>
                        <div class="vdeceased-grid">
                    `;

                    deceased.forEach(r => {
                        const contact = r.contact_person ? `${r.contact_person}${r.contact_number ? ` (${r.contact_number})` : ''}` : 'N/A';
                        html += `
                            <div class="vplot-card">
                                <div class="vdeceased-name">${r.full_name || 'Unnamed'}</div>
                                <p class="vdeceased-meta">
                                    <strong>Died:</strong> ${formatDate(r.date_of_death)}<br>
                                    <strong>Buried:</strong> ${formatDate(r.date_of_burial)}<br>
                                    <small><strong>Contact:</strong> ${contact}</small>
                                </p>
                                <div class="vdeceased-actions">
                                    <a class="btn btn-outline-primary btn-sm"
                                       href="burial_records.php?deceased_id=${r.deceased_id}">
                                        <i class="fas fa-external-link-alt me-1"></i> See more
                                    </a>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                } else {
                    html += `
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No burial records found for this plot.
                        </div>
                    `;
                }

                document.getElementById('plotDetailsContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('plotDetailsModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error loading plot details:', error);
        }
    }

    function stripHtml(html) {
        const div = document.createElement('div');
        div.innerHTML = html || '';
        return div.textContent || div.innerText || '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadVacancyData();
        }, 500);
    });
</script>
