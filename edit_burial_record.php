<?php
$pageTitle = 'Edit Burial Record';
$currentPage = 'burial_records';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Engineer') {
    header('Location: burial_records.php');
    exit;
}
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">
            <i class="fas fa-edit"></i>
            Edit Burial Record
        </h1>
        <div>
            <a href="burial_records.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Records
            </a>
            <button class="btn btn-danger" onclick="deleteRecord()">
                <i class="fas fa-trash"></i> Delete Record
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Loading record...</p>
    </div>

    <!-- Form Card -->
    <div class="card" id="editFormCard" style="display: none;">
        <div class="card-header">
            <i class="fas fa-user"></i> Burial Record Details
        </div>
        <div class="card-body">
            <form id="editBurialForm" onsubmit="handleUpdate(event)">
                <input type="hidden" id="deceased_id" name="deceased_id">
                <input type="hidden" id="current_plot_id" name="current_plot_id">

                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-12">
                        <h5 class="mb-3" style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px;">
                            <i class="fas fa-user"></i> Personal Information
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="birth_date">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select class="form-control" id="gender">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Burial Information -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3" style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px;">
                            <i class="fas fa-monument"></i> Burial Information
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Date of Death <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_of_death" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Date of Burial <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_of_burial" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Burial Type</label>
                            <select class="form-control" id="burial_type">
                                <option value="Ground">Ground</option>
                                <option value="Apartment">Apartment</option>
                                <option value="Mausoleum">Mausoleum</option>
                                <option value="Family Burial">Family Burial</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Current Plot Location</label>
                            <input type="text" class="form-control" id="plot_location_display" disabled>
                            <small class="text-muted">Plot cannot be changed after burial record is created</small>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3" style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px;">
                            <i class="fas fa-address-book"></i> Contact Information
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" placeholder="09XXXXXXXXX">
                        </div>
                    </div>

                    <!-- Payment Information (Treasurer Only) -->
                    <div class="col-12 mt-4" id="paymentSection" style="display: none;">
                        <h5 class="mb-3" style="color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px;">
                            <i class="fas fa-money-bill-wave"></i> Payment Information
                        </h5>
                        <div id="paymentInfo">
                            <p class="text-muted">Loading payment information...</p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Record
                            </button>
                            <a href="burial_records.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let deceasedId = null;

    async function loadRecord() {
        // Get deceased_id from URL
        const urlParams = new URLSearchParams(window.location.search);
        deceasedId = urlParams.get('id');

        if (!deceasedId) {
            showToast('error', 'Error', 'No record ID provided');
            setTimeout(() => window.location.href = 'burial_records.php', 2000);
            return;
        }

        try {
            // Get all plots to find the deceased
            const mapResponse = await fetch('/api/get_cemetery_map.php');
            const mapData = await mapResponse.json();

            let deceasedRecord = null;
            let plotInfo = null;

            // Find the deceased in occupied plots
            for (const plot of mapData.plots) {
                if (plot.status === 'Occupied' && plot.deceased_count > 0) {
                    const detailsResponse = await fetch(`/api/get_lot_details.php?plot_id=${plot.plot_id}`);
                    const detailsData = await detailsResponse.json();

                    if (detailsData.success && detailsData.deceased_records) {
                        const found = detailsData.deceased_records.find(d => d.deceased_id == deceasedId);
                        if (found) {
                            deceasedRecord = found;
                            plotInfo = detailsData.plot;
                            break;
                        }
                    }
                }
            }

            if (!deceasedRecord) {
                showToast('error', 'Error', 'Record not found');
                setTimeout(() => window.location.href = 'burial_records.php', 2000);
                return;
            }

            // Populate form
            document.getElementById('deceased_id').value = deceasedRecord.deceased_id;
            document.getElementById('current_plot_id').value = plotInfo.plot_id;
            document.getElementById('full_name').value = deceasedRecord.full_name || '';
            document.getElementById('birth_date').value = deceasedRecord.birth_date || '';
            document.getElementById('date_of_death').value = deceasedRecord.date_of_death || '';
            document.getElementById('date_of_burial').value = deceasedRecord.date_of_burial || '';
            document.getElementById('gender').value = deceasedRecord.gender || '';
            document.getElementById('address').value = deceasedRecord.address || '';
            document.getElementById('burial_type').value = deceasedRecord.burial_type || 'Ground';
            document.getElementById('contact_person').value = deceasedRecord.contact_person || '';
            document.getElementById('contact_number').value = deceasedRecord.contact_number || '';
            document.getElementById('plot_location_display').value =
                `Block ${plotInfo.block}, Section ${plotInfo.section}, Lot ${plotInfo.lot}`;

            // Load payment info if Treasurer
            if (currentUser && currentUser.role === 'Treasurer') {
                loadPaymentInfo(deceasedId);
            }

            // Show form
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('editFormCard').style.display = 'block';

        } catch (error) {
            console.error('Error loading record:', error);
            showToast('error', 'Error', 'Failed to load record');
            setTimeout(() => window.location.href = 'burial_records.php', 2000);
        }
    }

    async function loadPaymentInfo(deceasedId) {
        try {
            const response = await fetch(`/api/get_rental_details.php?deceased_id=${deceasedId}`, {
                credentials: 'include'
            });
            const data = await response.json();

            if (data.success && data.rentals) {
                document.getElementById('paymentSection').style.display = 'block';

                let html = '<div class="table-responsive">';
                html += '<table class="table table-sm table-bordered">';
                html += '<thead><tr><th>Rental Period</th><th>Amount</th><th>Status</th><th>Payment Date</th><th>Penalty</th></tr></thead>';
                html += '<tbody>';

                data.rentals.forEach(rental => {
                    const penaltyText = rental.penalty_applied ?
                        `₱${rental.penalty_amount.toFixed(2)}` : 'None';

                    html += `
                        <tr>
                            <td>${formatDate(rental.rental_start)} to ${formatDate(rental.rental_end)}</td>
                            <td><strong>${formatCurrency(rental.amount)}</strong></td>
                            <td>${getStatusBadge(rental.payment_status || rental.rental_status)}</td>
                            <td>${rental.payment_date ? formatDate(rental.payment_date) : 'N/A'}</td>
                            <td>${penaltyText}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';

                html += `
                    <div class="alert alert-info mt-3">
                        <strong>Summary:</strong><br>
                        Total Paid: ${formatCurrency(data.summary.total_paid)}<br>
                        Total Due: ${formatCurrency(data.summary.total_due)}<br>
                        Grand Total: ${formatCurrency(data.summary.total_amount)}
                    </div>
                `;

                document.getElementById('paymentInfo').innerHTML = html;
            }
        } catch (error) {
            console.error('Error loading payment info:', error);
        }
    }

    async function handleUpdate(event) {
        event.preventDefault();

        const formData = {
            deceased_id: parseInt(document.getElementById('deceased_id').value),
            full_name: document.getElementById('full_name').value.trim(),
            birth_date: document.getElementById('birth_date').value || null,
            date_of_death: document.getElementById('date_of_death').value,
            date_of_burial: document.getElementById('date_of_burial').value,
            gender: document.getElementById('gender').value || null,
            address: document.getElementById('address').value.trim() || null,
            burial_type: document.getElementById('burial_type').value,
            contact_person: document.getElementById('contact_person').value.trim() || null,
            contact_number: document.getElementById('contact_number').value.trim() || null
        };

        // Confirm before updating
        const result = await Swal.fire({
            title: 'Update Record?',
            html: `
                <div style="text-align: left;">
                    <p><strong>Name:</strong> ${formData.full_name}</p>
                    <p><strong>Date of Death:</strong> ${formData.date_of_death}</p>
                    <p>Are you sure you want to update this burial record?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, update it!'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/api/update_burial_record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Burial record updated successfully',
                        confirmButtonColor: '#1e3a8a'
                    }).then(() => {
                        window.location.href = 'burial_records.php';
                    });
                } else {
                    showToast('error', 'Error', data.message || 'Failed to update record');
                }
            } catch (error) {
                console.error('Error updating burial record:', error);
                showToast('error', 'Error', 'Failed to update record');
            }
        }
    }

    async function deleteRecord() {
        const result = await Swal.fire({
            title: 'Delete Record?',
            html: `
                <div style="text-align: left;">
                    <p><strong class="text-danger">WARNING:</strong> This action cannot be undone!</p>
                    <p>Deleting this record will:</p>
                    <ul>
                        <li>Remove all deceased information</li>
                        <li>Remove contact information</li>
                        <li>Remove payment/rental records</li>
                        <li>Mark the plot as Vacant (if no other deceased)</li>
                    </ul>
                    <p>Are you absolutely sure?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/api/delete_burial_record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ deceased_id: parseInt(deceasedId) })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Burial record has been deleted',
                        confirmButtonColor: '#1e3a8a'
                    }).then(() => {
                        window.location.href = 'burial_records.php';
                    });
                } else {
                    showToast('error', 'Error', data.message || 'Failed to delete record');
                }
            } catch (error) {
                console.error('Error deleting burial record:', error);
                showToast('error', 'Error', 'Failed to delete record');
            }
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadRecord();
        }, 500);

        // Set max date for date inputs to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_of_death').setAttribute('max', today);
        document.getElementById('date_of_burial').setAttribute('max', today);
    });
</script>
