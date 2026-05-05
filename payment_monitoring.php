<?php
$pageTitle = 'Payment Monitoring';
$currentPage = 'payment_monitoring';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <h1 class="page-title">
        <i class="fas fa-money-bill-wave"></i>
        Payment Monitoring
        <span class="badge bg-warning text-dark ms-2">Treasurer Only</span>
    </h1>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card paid">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-number" id="paidCount">0</div>
                <div class="stat-label">Paid</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card unpaid">
                <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-number" id="unpaidCount">0</div>
                <div class="stat-label">Unpaid</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #991b1b, #dc2626);">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number" id="overdueCount">0</div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-list"></i></div>
                <div class="stat-number" id="totalCount">0</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="search-filter-section">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label"><strong>Search by Deceased Name</strong></label>
                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Enter deceased name..."
                    onkeyup="filterPayments()"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Status Filter</strong></label>
                <select class="form-control" id="statusFilter" onchange="filterPayments()">
                    <option value="">All Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Pending">Pending</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Amount Range</strong></label>
                <select class="form-control" id="amountFilter" onchange="filterPayments()">
                    <option value="">All Amounts</option>
                    <option value="0-1000">₱0 - ₱1,000</option>
                    <option value="1000-2000">₱1,000 - ₱2,000</option>
                    <option value="2000-5000">₱2,000 - ₱5,000</option>
                    <option value="5000+">₱5,000+</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><strong>&nbsp;</strong></label>
                <button class="btn btn-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-redo"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table"></i> Payment Transactions</span>
            <div>
                <span id="recordCount" class="badge bg-light text-dark me-2">0 records</span>
                <button class="btn btn-sm btn-success" onclick="exportReport()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Deceased Name</th>
                            <th>Plot Location</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Transaction Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                Loading payments...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentModalContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="processPaymentBtn" onclick="showProcessPayment()">
                    <i class="fas fa-money-check"></i> Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allPayments = [];
    let currentDeceasedId = null;

    async function loadPayments() {
        try {
            const response = await fetch('/api/get_payment_summary.php', { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                allPayments = data.data;
                displayPayments(allPayments);
                updateStats(allPayments);
            } else {
                // Check if unauthorized
                if (response.status === 401) {
                    showToast('error', 'Unauthorized', 'Please login as Treasurer');
                    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                }
            }
        } catch (error) {
            console.error('Error loading payments:', error);
            document.getElementById('paymentsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading payments</td></tr>';
        }
    }

    function displayPayments(payments) {
        const tbody = document.getElementById('paymentsTableBody');
        tbody.innerHTML = '';

        if (payments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No payment records found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        payments.forEach(payment => {
            const row = `
                <tr>
                    <td><strong>${payment['Deceased Name']}</strong></td>
                    <td><span class="badge bg-primary">${payment['Plot Location']}</span></td>
                    <td>${payment['Contact Person']}</td>
                    <td>${payment['Contact Number']}</td>
                    <td>${formatDate(payment['Date of Transaction'])}</td>
                    <td><strong>${formatCurrency(payment['Amount'])}</strong></td>
                    <td>${getStatusBadge(payment['Status'])}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewPaymentDetails('${payment['Deceased Name']}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('recordCount').textContent = `${payments.length} records`;
    }

    function updateStats(payments) {
        let paid = 0, unpaid = 0, overdue = 0;

        payments.forEach(p => {
            if (p.Status === 'Paid') paid++;
            else if (p.Status === 'Overdue') overdue++;
            else unpaid++;
        });

        document.getElementById('paidCount').textContent = paid;
        document.getElementById('unpaidCount').textContent = unpaid;
        document.getElementById('overdueCount').textContent = overdue;
        document.getElementById('totalCount').textContent = payments.length;
    }

    function filterPayments() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const amountFilter = document.getElementById('amountFilter').value;

        let filtered = allPayments;

        if (searchTerm) {
            filtered = filtered.filter(p =>
                p['Deceased Name'].toLowerCase().includes(searchTerm) ||
                p['Contact Person'].toLowerCase().includes(searchTerm)
            );
        }

        if (statusFilter) {
            filtered = filtered.filter(p => p.Status === statusFilter);
        }

        if (amountFilter) {
            filtered = filtered.filter(p => {
                const amount = parseFloat(p.Amount);
                if (amountFilter === '0-1000') return amount >= 0 && amount <= 1000;
                if (amountFilter === '1000-2000') return amount > 1000 && amount <= 2000;
                if (amountFilter === '2000-5000') return amount > 2000 && amount <= 5000;
                if (amountFilter === '5000+') return amount > 5000;
                return true;
            });
        }

        displayPayments(filtered);
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('amountFilter').value = '';
        displayPayments(allPayments);
    }

    async function viewPaymentDetails(deceasedName) {
        try {
            const response = await fetch('/api/get_deceased_transactions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ deceased_name: deceasedName })
            });

            const data = await response.json();

            if (data.success && data.data.length > 0) {
                const transaction = data.data[0];
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Deceased Name:</strong> ${transaction.deceased_name}</p>
                            <p><strong>Plot Location:</strong> ${transaction.plot_location}</p>
                            <p><strong>Contact Person:</strong> ${transaction.contact_person || 'N/A'}</p>
                            <p><strong>Contact Number:</strong> ${transaction.contact_number || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Transaction Date:</strong> ${formatDate(transaction.transaction_date)}</p>
                            <p><strong>Amount:</strong> ${formatCurrency(transaction.amount)}</p>
                            <p><strong>Payment Status:</strong> ${getStatusBadge(transaction.payment_status || 'Unpaid')}</p>
                            <p><strong>Rental Period:</strong> ${transaction.rental_start ? formatDate(transaction.rental_start) : 'N/A'} - ${transaction.rental_end ? formatDate(transaction.rental_end) : 'N/A'}</p>
                        </div>
                    </div>
                    <hr>
                    <h6>Payment Rule:</h6>
                    <ul>
                        <li>Standard rental: ₱2,000 per 3 years</li>
                        <li>25% penalty if overdue</li>
                        <li>New rental period created upon payment</li>
                    </ul>
                `;

                document.getElementById('paymentModalContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function showProcessPayment() {
        Swal.fire({
            title: 'Process Payment',
            text: 'Payment processing feature requires rental ID. This is a demo.',
            icon: 'info',
            confirmButtonColor: '#1e3a8a'
        });
    }

    function exportReport() {
        showToast('info', 'Export', 'Generating payment report...');
        // Export to CSV functionality could be added here
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadPayments();
        }, 500);
    });
</script>
