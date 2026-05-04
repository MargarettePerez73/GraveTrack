<?php
$pageTitle = 'Vacancy Monitoring';
$currentPage = 'vacancy';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <h1 class="page-title">
        <i class="fas fa-map-marked-alt"></i>
        Vacancy Monitoring
    </h1>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card vacant">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number" id="vacantCount">0</div>
                <div class="stat-label">Vacant Plots</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card occupied">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number" id="occupiedCount">0</div>
                <div class="stat-label">Occupied Plots</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-th"></i></div>
                <div class="stat-number" id="totalCount">0</div>
                <div class="stat-label">Total Plots</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="search-filter-section">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><strong>Block</strong></label>
                <select class="form-control" id="filterBlock" onchange="applyFilters()">
                    <option value="">All Blocks</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Type</strong></label>
                <select class="form-control" id="filterType" onchange="applyFilters()">
                    <option value="">All Types</option>
                    <option value="Single">Single</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Mausoleum">Mausoleum</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Status</strong></label>
                <select class="form-control" id="filterStatus" onchange="applyFilters()">
                    <option value="">All Status</option>
                    <option value="Vacant">Vacant</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Reserved">Reserved</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>&nbsp;</strong></label>
                <button class="btn btn-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-redo"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Plots Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table"></i> Plot Listing</span>
            <span id="recordCount" class="badge bg-light text-dark">0 records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="plotsTable">
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
                            <td colspan="8" class="text-center">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                Loading plots...
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
            <div class="modal-body" id="plotDetailsContent">
                Loading...
            </div>
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

                // Update stats
                document.getElementById('vacantCount').textContent = data.stats.total_vacant;
                document.getElementById('occupiedCount').textContent = data.stats.total_occupied;
                document.getElementById('totalCount').textContent = data.stats.total_plots;

                // Populate block filter
                const blockFilter = document.getElementById('filterBlock');
                blocks.forEach(block => {
                    const option = document.createElement('option');
                    option.value = block;
                    option.textContent = `Block ${block}`;
                    blockFilter.appendChild(option);
                });

                // Display plots
                displayPlots(allPlots);
            }
        } catch (error) {
            console.error('Error loading vacancy data:', error);
            document.getElementById('plotsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
        }
    }

    function displayPlots(plots) {
        const tbody = document.getElementById('plotsTableBody');
        tbody.innerHTML = '';

        if (plots.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No plots found</td></tr>';
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
                        <button class="btn btn-sm btn-primary" onclick="viewPlotDetails(${plot.plot_id})">
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
                document.getElementById('plotDetailsContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('plotDetailsModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error loading plot details:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadVacancyData();
        }, 500);
    });
</script>
