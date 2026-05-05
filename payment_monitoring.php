<?php
session_start();
$pageTitle = 'Payment Monitoring';
$currentPage = 'payment_monitoring';
include 'includes/header.php';


// Check if user is Treasurer
if ($_SESSION['role'] !== 'Treasurer') {
    header('Location: dashboard.php');
    exit;
}
?>

<style>
    .payment-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .records-sidebar, .payment-sidebar {
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
        padding: 20px;
        background: linear-gradient(135deg, #006eff, #00408f);

    }

    .sidebar-header h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: white;
    }

    .sidebar-header .badge {
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        margin-top: 5px;
        display: inline-block;
    }

    .sidebar-section {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        
    }

    .sidebar-section h6 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        opacity: 0.8;
    }

    .sidebar-stat {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #3b82f6;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        backdrop-filter: blur(10px);
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
        margin-bottom: 6px;
        display: block;
        opacity: 0.9;
    }

    .filter-input {
        width: 100%;
        padding: 8px 10px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #3b82f6;
        color: black;
        border-radius: 6px;
        font-size: 12px;
    }

    .filter-input::placeholder {
        color: rgba(0, 0, 0, 0.6);
    }

    .filter-input:focus {
        outline: none;
        background: linear-gradient(135deg, #dbeafe, #0061d8);
        border: 2px solid #3b82f6;
        
    }

    .filter-input option {
        background: linear-gradient(135deg, #004aac, #bfdbfe);
        color: black;
    }

    #statusfilter{
        color: black;
    }
    .payment-main {
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

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card-compact {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid;
    }

    .stat-card-compact.paid { border-left-color: #10b981; }
    .stat-card-compact.unpaid { border-left-color: #ef4444; }
    .stat-card-compact.overdue { border-left-color: #991b1b; }
    .stat-card-compact.total { border-left-color: #3b82f6; }

    .stat-card-compact h6 {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 8px 0;
    }

    .stat-card-compact .value {
        font-size: 24px;
        font-weight: 800;
        color: #1e3a8a;
    }

    .content-card-compact {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        height: calc(100vh - 300px);
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

    .badge-compact {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
    }

    .record-count-badge {
        background: #dbeafe;
        color: #1e3a8a;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-export {
        padding: 6px 14px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-clear {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 8px;
        border-radius: 6px;
        width: 100%;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-clear:hover {
        background: rgba(255,255,255,0.3);
    }
</style>

<div class="payment-layout">
    <!-- Sidebar -->
    <div class="payment-sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-money-bill-wave"></i> Payment Monitoring</h4>
            <span class="badge">Treasurer Only</span>
        </div>

        <div class="sidebar-section">
            <h6>Statistics</h6>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Paid</div>
                <div class="sidebar-stat-value" id="sidebarPaid">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Unpaid</div>
                <div class="sidebar-stat-value" id="sidebarUnpaid">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Overdue</div>
                <div class="sidebar-stat-value" id="sidebarOverdue">0</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Filters</h6>
            <div class="filter-group">
                <label class="filter-label">Search Deceased</label>
                <input type="text" class="filter-input" id="searchInput" placeholder="Type name..." onkeyup="filterPayments()">
            </div>

            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="filter-input" id="statusFilter" onchange="filterPayments()">
                    <option value="">All Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Pending">Pending</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Amount Range</label>
                <select class="filter-input" id="amountFilter" onchange="filterPayments()">
                    <option value="">All Amounts</option>
                    <option value="0-1000">₱0 - ₱1,000</option>
                    <option value="1000-2000">₱1,000 - ₱2,000</option>
                    <option value="2000-5000">₱2,000 - ₱5,000</option>
                    <option value="5000+">₱5,000+</option>
                </select>
            </div>

            <button class="btn-clear" onclick="clearFilters()">
                <i class="fas fa-redo"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="payment-main">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Payment Transactions</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card-compact paid">
                <h6>Paid</h6>
                <div class="value" id="paidCount">0</div>
            </div>
            <div class="stat-card-compact unpaid">
                <h6>Unpaid</h6>
                <div class="value" id="unpaidCount">0</div>
            </div>
            <div class="stat-card-compact overdue">
                <h6>Overdue</h6>
                <div class="value" id="overdueCount">0</div>
            </div>
            <div class="stat-card-compact total">
                <h6>Total</h6>
                <div class="value" id="totalCount">0</div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="content-card-compact">
            <div class="content-card-header">
                <h5><i class="fas fa-table"></i> Payment Records</h5>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="record-count-badge" id="recordCount">0 records</span>
                    <button class="btn-export" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            <div class="content-card-body">
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Deceased Name</th>
                            <th>Plot Location</th>
                            <th>Date of Transaction</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px;">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2" style="color: #64748b;">Loading payments...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allPayments = [];

    async function loadPaymentData() {
        try {
            const response = await fetch('/api/get_payment_summary.php', { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                allPayments = data.data;
                displayPayments(allPayments);
                updateStats(allPayments);
            }
        } catch (error) {
            console.error('Error loading payment data:', error);
            document.getElementById('paymentsTableBody').innerHTML =
                '<tr><td colspan="6" class="text-center text-danger">Error loading payments</td></tr>';
        }
    }

    function updateStats(payments) {
        const paid = payments.filter(p => p.Status === 'Paid').length;
        const unpaid = payments.filter(p => p.Status === 'Unpaid').length;
        const overdue = payments.filter(p => p.Status === 'Overdue').length;
        const pending = payments.filter(p => p.Status === 'Pending').length;

        document.getElementById('paidCount').textContent = paid;
        document.getElementById('unpaidCount').textContent = unpaid;
        document.getElementById('overdueCount').textContent = overdue;
        document.getElementById('totalCount').textContent = payments.length;

        document.getElementById('sidebarPaid').textContent = paid;
        document.getElementById('sidebarUnpaid').textContent = unpaid;
        document.getElementById('sidebarOverdue').textContent = overdue;
    }

    function displayPayments(payments) {
        const tbody = document.getElementById('paymentsTableBody');
        tbody.innerHTML = '';

        if (payments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">No payment records found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        payments.forEach(payment => {
            const row = `
                <tr>
                    <td><strong>${payment.transaction_id}</strong></td>
                    <td><strong>${payment['Deceased Name']}</strong></td>
                    <td>${payment['Plot Location']}</td>
                    <td>${payment['Date of Transaction'] ? formatDate(payment['Date of Transaction']) : 'N/A'}</td>
                    <td>${payment['Contact Person'] || 'N/A'}</td>
                    <td>${payment['Contact Number'] || 'N/A'}</td>
                    <td><strong>${formatCurrency(payment['Amount'])}</strong></td>
                    <td>${getStatusBadge(payment.Status)}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('recordCount').textContent = `${payments.length} records`;
    }

    function filterPayments() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const amountRange = document.getElementById('amountFilter').value;

        let filtered = allPayments.filter(payment => {
            const matchesSearch = payment['Deceased Name'].toLowerCase().includes(searchTerm);
            const matchesStatus = !status || payment.Status === status;

            let matchesAmount = true;
            if (amountRange) {
                const amount = parseFloat(payment['Total Amount']);
                if (amountRange === '0-1000') matchesAmount = amount <= 1000;
                else if (amountRange === '1000-2000') matchesAmount = amount > 1000 && amount <= 2000;
                else if (amountRange === '2000-5000') matchesAmount = amount > 2000 && amount <= 5000;
                else if (amountRange === '5000+') matchesAmount = amount > 5000;
            }

            return matchesSearch && matchesStatus && matchesAmount;
        });

        displayPayments(filtered);
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('amountFilter').value = '';
        displayPayments(allPayments);
    }

    function exportReport() {
        showToast('info', 'Export', 'Export functionality coming soon');
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadPaymentData();
        }, 500);
    });
</script>
