<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
include 'includes/header.php';
?>

<style>
    .dashboard-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    #welcomeText {
        font-size: 14px;
        color: #ffffff;
        margin-top: 5px;
    }
    .records-sidebar, .dashboard-sidebar {
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
        background: linear-gradient(135deg, #006eff, #00408f);
    }

    .sidebar-header h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: white;
    }

    .sidebar-header p {
        font-size: 13px;
        margin: 5px 0 0 0;
        opacity: 0.8;
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
        font-size: 11px;
        opacity: 0.8;
        margin-bottom: 5px;
    }

    .sidebar-stat-value {
        font-size: 24px;
        font-weight: 700;
    }

    .sidebar-stat-icon {
        float: right;
        font-size: 20px;
        opacity: 0.3;
    }

    .quick-action-btn {
        display: block;
        width: 100%;
        padding: 12px 15px;
        background: white;
        color: #1e3a8a;
        border: none;
        border-radius: 8px;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 13px;
        text-align: left;
        transition: all 0.3s;
        text-decoration: none;
    }

    .quick-action-btn:hover {
        background: #fbbf24;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        color: #1e3a8a;
    }

    .quick-action-btn i {
        margin-right: 10px;
        width: 20px;
    }

    .dashboard-main {
        flex: 1;
        overflow-y: auto;
        background: #f8fafc;
        padding: 30px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #1e3a8a;
        margin: 0 0 10px 0;
    }

    .page-header p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    .stat-card-modern {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        border-left: 4px solid;
        transition: all 0.3s;
    }

    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .stat-card-modern.vacant { border-left-color: #10b981; }
    .stat-card-modern.occupied { border-left-color: #ef4444; }
    .stat-card-modern.total { border-left-color: #3b82f6; }
    .stat-card-modern.paid { border-left-color: #10b981; }
    .stat-card-modern.unpaid { border-left-color: #f59e0b; }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stat-card-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-card-modern.vacant .stat-card-icon { background: #d1fae5; color: #10b981; }
    .stat-card-modern.occupied .stat-card-icon { background: #fee2e2; color: #ef4444; }
    .stat-card-modern.total .stat-card-icon { background: #dbeafe; color: #3b82f6; }
    .stat-card-modern.paid .stat-card-icon { background: #d1fae5; color: #10b981; }
    .stat-card-modern.unpaid .stat-card-icon { background: #fef3c7; color: #f59e0b; }

    .stat-card-value {
        font-size: 36px;
        font-weight: 800;
        color: #1e3a8a;
    }

    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .content-card-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f1f5f9;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
    }

    .content-card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .content-card-body {
        padding: 25px;
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 10px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 8px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .block-row {
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .block-row:last-child {
        border-bottom: none;
    }

    .block-name {
        font-weight: 700;
        color: #1e3a8a;
        font-size: 14px;
    }

    .block-stats {
        display: flex;
        gap: 15px;
        margin-top: 8px;
        font-size: 12px;
    }

    .block-stat-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .badge-modern {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-modern.vacant { background: #d1fae5; color: #047857; }
    .badge-modern.occupied { background: #fee2e2; color: #dc2626; }
</style>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-tachometer-alt"></i> Dashboard</h4>
            <p id="welcomeText">Welcome back!</p>
        </div>

        <div class="sidebar-section">
            <h6>Overview</h6>
            <div class="sidebar-stat">
                <div class="sidebar-stat-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="sidebar-stat-label">Total Plots</div>
                <div class="sidebar-stat-value" id="sidebarTotalPlots">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="sidebar-stat-label">Vacant Plots</div>
                <div class="sidebar-stat-value" id="sidebarVacant">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-icon"><i class="fas fa-users"></i></div>
                <div class="sidebar-stat-label">Occupied Plots</div>
                <div class="sidebar-stat-value" id="sidebarOccupied">0</div>
            </div>
        </div>

        <div class="sidebar-section" id="sidebarPaymentSection" style="display: none;">
            <h6>Payments</h6>
            <div class="sidebar-stat">
                <div class="sidebar-stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="sidebar-stat-label">Paid</div>
                <div class="sidebar-stat-value" id="sidebarPaid">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="sidebar-stat-label">Unpaid/Overdue</div>
                <div class="sidebar-stat-value" id="sidebarUnpaid">0</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Quick Actions</h6>
            <a href="adding_burial_records.php" class="quick-action-btn">
                <i class="fas fa-plus"></i> Add Burial Record
            </a>
            <a href="cemetery_map.php" class="quick-action-btn">
                <i class="fas fa-map"></i> Cemetery Map
            </a>
            <a href="burial_records.php" class="quick-action-btn">
                <i class="fas fa-book"></i> Browse Records
            </a>
            <a href="vacancy.php" class="quick-action-btn">
                <i class="fas fa-search"></i> View Vacancy
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
            <p>Monitor cemetery statistics and manage burial records</p>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card-modern vacant">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Vacant Plots</span>
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" id="totalVacant">0</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern occupied">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Occupied Plots</span>
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" id="totalOccupied">0</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-modern total">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Total Plots</span>
                        <div class="stat-card-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                    </div>
                    <div class="stat-card-value" id="totalPlots">0</div>
                </div>
            </div>
        </div>

        <!-- Payment Stats (Treasurer Only) -->
        <div id="paymentStats" style="display: none;">
            <h3 class="mb-3" style="color: #1e3a8a; font-weight: 700;">
                <i class="fas fa-money-bill-wave"></i> Payment Overview
            </h3>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card-modern paid">
                        <div class="stat-card-header">
                            <span class="stat-card-label">Paid Transactions</span>
                            <div class="stat-card-icon">
                                <i class="fas fa-check-double"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="totalPaid">0</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card-modern unpaid">
                        <div class="stat-card-header">
                            <span class="stat-card-label">Unpaid/Overdue</span>
                            <div class="stat-card-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="totalUnpaid">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plot Distribution -->
        <div class="content-card">
            <div class="content-card-header">
                <h5><i class="fas fa-chart-pie"></i> Plot Distribution by Block</h5>
            </div>
            <div class="content-card-body" id="blockDistribution">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2" style="color: #64748b;">Loading distribution data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    async function loadDashboard() {
        try {
            const response = await fetch('/api/get_vacancy_stats.php');
            const data = await response.json();

            if (data.success) {
                // Update main stat cards
                document.getElementById('totalVacant').textContent = data.stats.total_vacant || 0;
                document.getElementById('totalOccupied').textContent = data.stats.total_occupied || 0;
                document.getElementById('totalPlots').textContent = data.stats.total_plots || 0;

                // Update sidebar stats
                document.getElementById('sidebarTotalPlots').textContent = data.stats.total_plots || 0;
                document.getElementById('sidebarVacant').textContent = data.stats.total_vacant || 0;
                document.getElementById('sidebarOccupied').textContent = data.stats.total_occupied || 0;

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

                // Populate block distribution
                const container = document.getElementById('blockDistribution');
                let html = '';

                Object.keys(plotsByBlock).sort().forEach(block => {
                    const stats = plotsByBlock[block];
                    const vacancyRate = ((stats.vacant / stats.total) * 100).toFixed(1);

                    html += `
                        <div class="block-row">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <div class="block-name">Block ${block}</div>
                                    <div class="block-stats">
                                        <div class="block-stat-item">
                                            <i class="fas fa-map-marked-alt" style="color: #64748b;"></i>
                                            <span style="color: #64748b;">${stats.total} total</span>
                                        </div>
                                        <div class="block-stat-item">
                                            <span class="badge-modern vacant">${stats.vacant} vacant</span>
                                        </div>
                                        <div class="block-stat-item">
                                            <span class="badge-modern occupied">${stats.occupied} occupied</span>
                                        </div>
                                    </div>
                                    <div class="progress-bar-custom">
                                        <div class="progress-fill" style="width: ${vacancyRate}%"></div>
                                    </div>
                                </div>
                                <div style="text-align: right; margin-left: 20px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #10b981;">${vacancyRate}%</div>
                                    <div style="font-size: 11px; color: #94a3b8;">Vacant</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            }

            // Load payment stats if Treasurer
            if (currentUser && currentUser.role === 'Treasurer') {
                loadPaymentStats();
            }

            // Update welcome text
            if (currentUser) {
                document.getElementById('welcomeText').textContent = `Welcome, ${currentUser.username}!`;
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
                document.getElementById('paymentStats').style.display = 'block';
                document.getElementById('sidebarPaymentSection').style.display = 'block';

                let paid = 0, unpaid = 0;
                data.data.forEach(item => {
                    if (item.Status === 'Paid' || item.Status === 'Paid (was overdue)') paid++;
                    else unpaid++;
                });

                document.getElementById('totalPaid').textContent = paid;
                document.getElementById('totalUnpaid').textContent = unpaid;
                document.getElementById('sidebarPaid').textContent = paid;
                document.getElementById('sidebarUnpaid').textContent = unpaid;
            }
        } catch (error) {
            console.error('Error loading payment stats:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadDashboard();
        }, 500);
    });
</script>
