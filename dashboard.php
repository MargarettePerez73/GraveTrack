<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard
    </h1>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card vacant">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number" id="totalVacant">0</div>
                <div class="stat-label">Vacant Plots</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card occupied">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number" id="totalOccupied">0</div>
                <div class="stat-label">Occupied Plots</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="stat-number" id="totalPlots">0</div>
                <div class="stat-label">Total Plots</div>
            </div>
        </div>
    </div>

    <!-- Payment Stats (Treasurer Only) -->
    <div id="paymentStats" style="display: none;">
        <h3 class="mb-3"><i class="fas fa-money-bill-wave"></i> Payment Overview</h3>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card paid">
                    <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                    <div class="stat-number" id="totalPaid">0</div>
                    <div class="stat-label">Paid Transactions</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card unpaid">
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-number" id="totalUnpaid">0</div>
                    <div class="stat-label">Unpaid/Overdue</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="adding_burial_records.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Burial Record
                        </a>
                        <a href="vacancy.php" class="btn btn-success">
                            <i class="fas fa-search"></i> View Vacancy Status
                        </a>
                        <a href="burial_records.php" class="btn btn-info">
                            <i class="fas fa-book"></i> Browse Records
                        </a>
                        <a href="cemetery_map.php" class="btn btn-secondary">
                            <i class="fas fa-map"></i> Cemetery Map
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plot Distribution -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Plot Distribution by Block
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Block</th>
                                    <th>Total</th>
                                    <th>Vacant</th>
                                    <th>Occupied</th>
                                    <th>Vacancy Rate</th>
                                </tr>
                            </thead>
                            <tbody id="blockDistribution">
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                        Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Load Dashboard Data
    async function loadDashboard() {
        try {
            // Get vacancy stats
            const response = await fetch('/api/get_vacancy_stats.php');
            const data = await response.json();

            if (data.success) {
                // Update stat cards
                document.getElementById('totalVacant').textContent = data.stats.total_vacant || 0;
                document.getElementById('totalOccupied').textContent = data.stats.total_occupied || 0;
                document.getElementById('totalPlots').textContent = data.stats.total_plots || 0;

                // Group plots by block
                const plotsByBlock = {};
                data.plots.forEach(plot => {
                    if (!plotsByBlock[plot.block]) {
                        plotsByBlock[plot.block] = { total: 0, vacant: 0, occupied: 0 };
                    }
                    plotsByBlock[plot.block].total++;
                    if (plot.status === 'Vacant') plotsByBlock[plot.block].vacant++;
                    if (plot.status === 'Occupied') plotsByBlock[plot.block].occupied++;
                });

                // Populate block distribution table
                const tbody = document.getElementById('blockDistribution');
                tbody.innerHTML = '';

                Object.keys(plotsByBlock).sort().forEach(block => {
                    const stats = plotsByBlock[block];
                    const vacancyRate = ((stats.vacant / stats.total) * 100).toFixed(1);

                    const row = `
                        <tr>
                            <td><strong>Block ${block}</strong></td>
                            <td>${stats.total}</td>
                            <td><span class="badge badge-vacant">${stats.vacant}</span></td>
                            <td><span class="badge badge-occupied">${stats.occupied}</span></td>
                            <td>${vacancyRate}%</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }

            // Load payment stats if Treasurer
            if (currentUser && currentUser.role === 'Treasurer') {
                loadPaymentStats();
            }
        } catch (error) {
            console.error('Error loading dashboard:', error);
        }
    }

    async function loadPaymentStats() {
        try {
            const response = await fetch('/api/get_payment_summary.php', { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                // Show payment stats section
                document.getElementById('paymentStats').style.display = 'block';

                // Count paid and unpaid
                let paid = 0, unpaid = 0;
                data.data.forEach(item => {
                    if (item.Status === 'Paid') paid++;
                    else unpaid++;
                });

                document.getElementById('totalPaid').textContent = paid;
                document.getElementById('totalUnpaid').textContent = unpaid;
            }
        } catch (error) {
            console.error('Error loading payment stats:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadDashboard();
        }, 500);
    });
</script>
