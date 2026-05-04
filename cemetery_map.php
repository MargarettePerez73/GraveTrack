<?php
$pageTitle = 'Cemetery Map';
$currentPage = 'cemetery_map';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <h1 class="page-title">
        <i class="fas fa-map"></i>
        Interactive Cemetery Map
    </h1>

    <!-- Filters -->
    <div class="search-filter-section">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label"><strong>Search by Deceased Name or Plot</strong></label>
                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Enter deceased name, block, or lot number..."
                    onkeyup="performSearch()"
                >
            </div>
            <div class="col-md-4">
                <label class="form-label"><strong>Filter by Phase</strong></label>
                <select class="form-control" id="phaseFilter" onchange="performSearch()">
                    <option value="">All Phases</option>
                    <option value="Phase 1">Phase 1 (Blocks A-I)</option>
                    <option value="Phase 2">Phase 2 (Blocks T-Z)</option>
                    <option value="Phase 3">Phase 3 (Block AA)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><strong>&nbsp;</strong></label>
                <button class="btn btn-secondary w-100" onclick="clearSearch()">
                    <i class="fas fa-redo"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex gap-4 align-items-center flex-wrap">
                <div><strong>Legend:</strong></div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 30px; height: 30px; background: #d1fae5; border: 2px solid #10b981; border-radius: 4px;"></div>
                    <span>Vacant</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 30px; height: 30px; background: #fee2e2; border: 2px solid #ef4444; border-radius: 4px;"></div>
                    <span>Occupied</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 30px; height: 30px; background: #fef3c7; border: 2px solid #f59e0b; border-radius: 4px;"></div>
                    <span>Reserved</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Phase Tabs -->
    <div class="phase-tabs" id="phaseTabs">
        <div class="phase-tab active" onclick="selectPhase('all')">All Phases</div>
        <div class="phase-tab" onclick="selectPhase('Phase 1')">Phase 1 (A-I)</div>
        <div class="phase-tab" onclick="selectPhase('Phase 2')">Phase 2 (T-Z)</div>
        <div class="phase-tab" onclick="selectPhase('Phase 3')">Phase 3 (AA)</div>
    </div>

    <!-- Cemetery Map Grid -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-th"></i> Cemetery Plot Map
            <span id="plotCount" class="badge bg-light text-dark ms-2">0 plots</span>
        </div>
        <div class="card-body">
            <div id="cemeteryMapContainer">
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
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Plot Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="plotModalContent">
                Loading...
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allPlots = [];
    let filteredPlots = [];
    let currentPhase = 'all';

    async function loadCemeteryMap() {
        try {
            const response = await fetch('/api/get_cemetery_map.php');
            const data = await response.json();

            if (data.success) {
                allPlots = data.plots;
                filteredPlots = allPlots;
                renderMap(filteredPlots);
            }
        } catch (error) {
            console.error('Error loading cemetery map:', error);
            document.getElementById('cemeteryMapContainer').innerHTML = '<p class="text-center text-danger">Error loading map</p>';
        }
    }

    function renderMap(plots) {
        const container = document.getElementById('cemeteryMapContainer');

        if (plots.length === 0) {
            container.innerHTML = '<p class="text-center">No plots found</p>';
            document.getElementById('plotCount').textContent = '0 plots';
            return;
        }

        // Group by block
        const plotsByBlock = {};
        plots.forEach(plot => {
            if (!plotsByBlock[plot.block]) {
                plotsByBlock[plot.block] = [];
            }
            plotsByBlock[plot.block].push(plot);
        });

        // Render blocks
        let html = '';
        Object.keys(plotsByBlock).sort().forEach(block => {
            html += `
                <div class="mb-4">
                    <h5 style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px;">
                        <i class="fas fa-map-marker-alt"></i> Block ${block}
                        <span class="badge bg-primary ms-2">${plotsByBlock[block].length} plots</span>
                    </h5>
                    <div class="cemetery-map">
            `;

            plotsByBlock[block].forEach(plot => {
                const statusClass = plot.status.toLowerCase();
                const deceased = plot.deceased_names ? plot.deceased_names.substring(0, 30) + (plot.deceased_names.length > 30 ? '...' : '') : '';

                html += `
                    <div class="plot-item ${statusClass}" onclick="viewPlotInfo(${plot.plot_id})" title="${deceased}">
                        <div class="plot-label">${plot.section}-${plot.lot}</div>
                        <small style="font-size: 0.65rem;">${plot.type.substring(0, 4)}</small>
                        ${plot.deceased_count > 0 ? `<small style="font-size: 0.6rem; color: #dc2626;">👤${plot.deceased_count}</small>` : ''}
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('plotCount').textContent = `${plots.length} plots`;
    }

    function selectPhase(phase) {
        currentPhase = phase;

        // Update active tab
        document.querySelectorAll('.phase-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.target.classList.add('active');

        // Filter plots
        if (phase === 'all') {
            filteredPlots = allPlots;
        } else {
            filteredPlots = allPlots.filter(p => p.phase === phase);
        }

        renderMap(filteredPlots);
    }

    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const phaseFilter = document.getElementById('phaseFilter').value;

        let filtered = allPlots;

        if (searchTerm) {
            filtered = filtered.filter(p => {
                return (p.deceased_names && p.deceased_names.toLowerCase().includes(searchTerm)) ||
                       p.block.toLowerCase().includes(searchTerm) ||
                       p.lot.toLowerCase().includes(searchTerm);
            });
        }

        if (phaseFilter) {
            filtered = filtered.filter(p => p.phase === phaseFilter);
        }

        filteredPlots = filtered;
        renderMap(filteredPlots);
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('phaseFilter').value = '';
        filteredPlots = allPlots;
        renderMap(filteredPlots);
    }

    async function viewPlotInfo(plotId) {
        try {
            const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('plotModalContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('plotModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadCemeteryMap();
        }, 500);
    });
</script>
