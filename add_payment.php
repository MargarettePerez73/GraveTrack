<?php
$pageTitle = 'Add Payment Transaction';
$currentPage = 'payment_monitoring';
include 'includes/header.php';

// Check if user is Treasurer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Treasurer') {
    header('Location: dashboard.php');
    exit;
}

$deceased_id = isset($_GET['deceased_id']) ? intval($_GET['deceased_id']) : 0;
$plot_id = isset($_GET['plot_id']) ? intval($_GET['plot_id']) : 0;
?>

<style>
    .payment-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .payment-sidebar {
        width: 300px;
        background: linear-gradient(180deg, #059669 0%, #10b981 100%);
        color: white;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 4px 0 12px rgba(0,0,0,0.1);
    }

    .sidebar-header {
        padding: 25px 20px;
        border-bottom: 2px solid rgba(255,255,255,0.1);
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

    .info-item {
        background: rgba(255,255,255,0.1);
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        backdrop-filter: blur(10px);
    }

    .info-label {
        font-size: 11px;
        opacity: 0.8;
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
    }

    .payment-main {
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

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .form-card-header {
        padding: 20px 25px;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 2px solid #e2e8f0;
    }

    .form-card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .form-card-body {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-submit {
        padding: 14px 30px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
    }

    .btn-cancel {
        padding: 14px 30px;
        background: #64748b;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancel:hover {
        background: #475569;
        color: white;
    }

    .required {
        color: #ef4444;
    }

    .rental-info {
        background: #dbeafe;
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .rental-info h6 {
        color: #1e40af;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .rental-info p {
        color: #1e3a8a;
        margin: 5px 0;
        font-size: 13px;
    }
</style>

<div class="payment-layout">
    <!-- Sidebar -->
    <div class="payment-sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-money-bill-wave"></i> Add Payment</h4>
            <p>Record payment transaction</p>
        </div>

        <div class="sidebar-section">
            <h6>Plot Information</h6>
            <div class="info-item">
                <div class="info-label">Plot ID</div>
                <div class="info-value" id="sidebarPlotId">Loading...</div>
            </div>
            <div class="info-item">
                <div class="info-label">Plot Location</div>
                <div class="info-value" id="sidebarPlotLocation">Loading...</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Deceased Information</h6>
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value" id="sidebarDeceasedName">Loading...</div>
            </div>
            <div class="info-item">
                <div class="info-label">Date of Death</div>
                <div class="info-value" id="sidebarDateOfDeath">Loading...</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Payment Guide</h6>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;">
                <p style="font-size: 12px; margin: 0 0 10px 0; opacity: 0.9;">
                    <strong>Standard Rate:</strong><br>
                    ₱2,000 per 3-year rental period
                </p>
                <p style="font-size: 12px; margin: 0; opacity: 0.9;">
                    <strong>Late penalty:</strong><br>
                    2-day grace after rental end, then 25% on the renewal (e.g. ₱500 on ₱2,000)
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="payment-main">
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> Add Payment Transaction</h1>
            <p>Record a new payment for burial plot rental</p>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading payment information...</p>
        </div>

        <!-- Payment Form -->
        <div id="paymentForm" style="display: none;">
            <!-- Deceased Info Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <h5><i class="fas fa-user"></i> Deceased & Plot Details</h5>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Deceased Name:</strong> <span id="deceasedName"></span></p>
                            <p><strong>Plot Location:</strong> <span id="plotLocation"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date of Death:</strong> <span id="dateOfDeath"></span></p>
                            <p><strong>Contact Person:</strong> <span id="contactPerson"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rental Information -->
            <div class="rental-info" id="rentalInfo">
                <h6><i class="fas fa-info-circle"></i> Current Rental Status</h6>
                <div id="rentalDetails">Loading rental information...</div>
            </div>

            <!-- Payment Details Form -->
            <form id="addPaymentForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="deceased_id" name="deceased_id" value="<?php echo $deceased_id; ?>">
                <input type="hidden" id="plot_id" name="plot_id" value="<?php echo $plot_id; ?>">
                <input type="hidden" id="rental_id" name="rental_id">

                <div class="form-card">
                    <div class="form-card-header">
                        <h5><i class="fas fa-money-check-alt"></i> Payment Details</h5>
                    </div>
                    <div class="form-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Rental Period <span class="required">*</span></label>
                                    <select class="form-control" id="rental_period" required onchange="updateAmount()">
                                        <option value="">Select rental period</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Payment Date <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="payment_date" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Amount (₱) <span class="required">*</span></label>
                                    <input type="number" class="form-control" id="amount" step="0.01" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Payment Method <span class="required">*</span></label>
                                    <select class="form-control" id="payment_method" required>
                                        <option value="">Select method</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Check">Check</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="GCash">GCash</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Reference Number</label>
                                    <input type="text" class="form-control" id="reference_number" placeholder="Optional">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" rows="3" placeholder="Additional notes (optional)"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save"></i> Save Payment
                                    </button>
                                    <a href="cemetery_map.php" class="btn-cancel">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    const deceasedId = <?php echo $deceased_id; ?>;
    const plotId = <?php echo $plot_id; ?>;

    async function loadPaymentInfo() {
        try {
            // Load deceased and plot info
            const plotResponse = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
            const plotData = await plotResponse.json();

            if (plotData.success) {
                const deceased = plotData.deceased_records.find(d => d.deceased_id === deceasedId);
                const plot = plotData.plot;

                if (deceased) {
                    // Update main content
                    document.getElementById('deceasedName').textContent = deceased.full_name;
                    document.getElementById('plotLocation').textContent = `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot}`;
                    document.getElementById('dateOfDeath').textContent = formatDate(deceased.date_of_death);
                    document.getElementById('contactPerson').textContent = deceased.contact_person || 'N/A';

                    // Update sidebar
                    document.getElementById('sidebarPlotId').textContent = plot.plot_id;
                    document.getElementById('sidebarPlotLocation').textContent = `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot}`;
                    document.getElementById('sidebarDeceasedName').textContent = deceased.full_name;
                    document.getElementById('sidebarDateOfDeath').textContent = formatDate(deceased.date_of_death);
                }
            }

            // Load rental information
            const rentalResponse = await fetch(`/api/get_rental_details.php?deceased_id=${deceasedId}`, {
                credentials: 'include'
            });
            const rentalData = await rentalResponse.json();

            if (rentalData.success && rentalData.rentals) {
                displayRentalInfo(rentalData.rentals);
                populateRentalPeriods(rentalData.rentals);
            }

            // Set default payment date to today
            document.getElementById('payment_date').valueAsDate = new Date();

            // Show form
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('paymentForm').style.display = 'block';

        } catch (error) {
            console.error('Error loading payment info:', error);
            showToast('error', 'Error', 'Failed to load payment information');
        }
    }

    function displayRentalInfo(rentals) {
        let html = '';
        let unpaidFound = false;

        rentals.forEach(rental => {
            const status = rental.payment_status || rental.rental_status;
            if (status !== 'Paid') {
                unpaidFound = true;
                const penaltyText = rental.penalty_applied ?
                    `<span style="color: #dc2626;">+ ₱${rental.penalty_amount.toFixed(2)} penalty</span>` : '';

                html += `
                    <p><strong>Period:</strong> ${formatDate(rental.rental_start)} to ${formatDate(rental.rental_end)}</p>
                    <p><strong>Amount Due:</strong> ₱${rental.amount.toFixed(2)} ${penaltyText}</p>
                    <p><strong>Status:</strong> <span style="color: #dc2626; font-weight: 600;">${status}</span></p>
                `;
            }
        });

        if (!unpaidFound) {
            html = '<p style="color: #10b981; font-weight: 600;"><i class="fas fa-check-circle"></i> All rental periods are paid</p>';
        }

        document.getElementById('rentalDetails').innerHTML = html;
    }

    function populateRentalPeriods(rentals) {
        const select = document.getElementById('rental_period');
        select.innerHTML = '<option value="">Select rental period</option>';

        rentals.forEach(rental => {
            const status = rental.payment_status || rental.rental_status;
            if (status !== 'Paid') {
                const option = document.createElement('option');
                option.value = rental.rental_id;
                option.textContent = `${formatDate(rental.rental_start)} to ${formatDate(rental.rental_end)} - ₱${rental.amount.toFixed(2)}`;
                option.dataset.amount = rental.amount;
                option.dataset.penalty = rental.penalty_applied ? rental.penalty_amount : 0;
                select.appendChild(option);
            }
        });
    }

    function updateAmount() {
        const select = document.getElementById('rental_period');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption.value) {
            const amount = parseFloat(selectedOption.dataset.amount);
            const penalty = parseFloat(selectedOption.dataset.penalty || 0);
            const total = amount + penalty;

            document.getElementById('amount').value = total.toFixed(2);
            document.getElementById('rental_id').value = selectedOption.value;
        } else {
            document.getElementById('amount').value = '';
            document.getElementById('rental_id').value = '';
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();

        const formData = {
            rental_id: parseInt(document.getElementById('rental_id').value),
            deceased_id: parseInt(deceasedId),
            payment_date: document.getElementById('payment_date').value,
            amount: parseFloat(document.getElementById('amount').value),
            payment_method: document.getElementById('payment_method').value,
            reference_number: document.getElementById('reference_number').value.trim() || null,
            notes: document.getElementById('notes').value.trim() || null
        };

        // Confirm before saving
        const result = await Swal.fire({
            title: 'Confirm Payment?',
            html: `
                <div style="text-align: left;">
                    <p><strong>Amount:</strong> ₱${formData.amount.toFixed(2)}</p>
                    <p><strong>Payment Method:</strong> ${formData.payment_method}</p>
                    <p><strong>Payment Date:</strong> ${formatDate(formData.payment_date)}</p>
                    <p>Are you sure you want to record this payment?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, save it!'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/api/add_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment transaction recorded successfully',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location.href = 'payment_monitoring.php';
                    });
                } else {
                    showToast('error', 'Error', data.message || 'Failed to record payment');
                }
            } catch (error) {
                console.error('Error recording payment:', error);
                showToast('error', 'Error', 'Failed to record payment');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadPaymentInfo();
    });
</script>
