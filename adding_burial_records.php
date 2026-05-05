<?php
$pageTitle = 'Add Burial Record';
$currentPage = 'burial_records';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">
            <i class="fas fa-plus-circle"></i>
            Add New Burial Record
        </h1>
        <a href="burial_records.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Records
        </a>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit"></i> Burial Information Form
        </div>
        <div class="card-body">
            <form id="burialForm" onsubmit="handleSubmit(event)">
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
                            <label class="form-label">Select Plot <span class="text-danger">*</span></label>
                            <select class="form-control" id="plot_id" required onchange="showPlotInfo()">
                                <option value="">-- Select Vacant Plot --</option>
                            </select>
                            <small id="plotInfo" class="form-text text-muted"></small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Block</label>
                            <input type="text" class="form-control" id="plot_block" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" id="plot_section" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Lot</label>
                            <input type="text" class="form-control" id="plot_lot" readonly style="background-color: #f8f9fa;">
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

                    <!-- Buttons -->
                    <div class="col-12 mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Record
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm('burialForm')">
                                <i class="fas fa-eraser"></i> Clear Form
                            </button>
                            <a href="burial_records.php" class="btn btn-outline-secondary">
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
    let vacantPlots = [];

    async function loadVacantPlots() {
        try {
            const response = await fetch('/api/get_vacant_plots.php');
            const data = await response.json();

            if (data.success) {
                vacantPlots = data.data;
                const select = document.getElementById('plot_id');
                select.innerHTML = '<option value="">-- Select Vacant Plot --</option>';

                vacantPlots.forEach(plot => {
                    const option = document.createElement('option');
                    option.value = plot.plot_id;
                    option.textContent = plot.label + ` (${plot.type})`;
                    option.dataset.type = plot.type;
                    option.dataset.block = plot.block;
                    option.dataset.section = plot.section;
                    option.dataset.lot = plot.lot;
                    select.appendChild(option);
                });

                // Pre-select plot if parameters are in URL
                preselectPlotFromURL();
            }
        } catch (error) {
            console.error('Error loading vacant plots:', error);
        }
    }

    function preselectPlotFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const plotId = urlParams.get('plot_id');
        const block = urlParams.get('block');
        const lot = urlParams.get('lot');

        const select = document.getElementById('plot_id');

        // Priority 1: If plot_id is in URL
        if (plotId) {
            select.value = plotId;
            showPlotInfo();
            showToast('info', 'Plot Selected', `Plot ${plotId} has been auto-selected`);
        }
        // Priority 2: If block and lot are in URL
        else if (block && lot) {
            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                if (option.dataset.block === block && option.dataset.lot === lot) {
                    select.value = option.value;
                    showPlotInfo();
                    showToast('info', 'Plot Selected', `Block ${block}, Lot ${lot} has been pre-selected`);
                    break;
                }
            }
        }
    }

    function showPlotInfo() {
        const select = document.getElementById('plot_id');
        const plotId = select.value;
        const plotInfo = document.getElementById('plotInfo');

        if (plotId) {
            const selectedOption = select.options[select.selectedIndex];
            const type = selectedOption.dataset.type;
            const block = selectedOption.dataset.block;
            const section = selectedOption.dataset.section;
            const lot = selectedOption.dataset.lot;

            // Auto-fill the text fields
            document.getElementById('plot_block').value = block || '';
            document.getElementById('plot_section').value = section || '';
            document.getElementById('plot_lot').value = lot || '';

            plotInfo.textContent = `Plot Type: ${type} | Rental: ₱2,000 per 3 years`;
            plotInfo.style.color = '#10b981';
        } else {
            plotInfo.textContent = '';
            document.getElementById('plot_block').value = '';
            document.getElementById('plot_section').value = '';
            document.getElementById('plot_lot').value = '';
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();

        const formData = {
            full_name: document.getElementById('full_name').value.trim(),
            birth_date: document.getElementById('birth_date').value || null,
            date_of_death: document.getElementById('date_of_death').value,
            date_of_burial: document.getElementById('date_of_burial').value,
            gender: document.getElementById('gender').value || null,
            address: document.getElementById('address').value.trim() || null,
            plot_id: parseInt(document.getElementById('plot_id').value),
            burial_type: document.getElementById('burial_type').value,
            contact_person: document.getElementById('contact_person').value.trim() || null,
            contact_number: document.getElementById('contact_number').value.trim() || null
        };

        // Validate required fields
        if (!formData.full_name || !formData.date_of_death || !formData.date_of_burial || !formData.plot_id) {
            showToast('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        // Confirm before saving
        const result = await Swal.fire({
            title: 'Save Burial Record?',
            html: `
                <div style="text-align: left;">
                    <p><strong>Name:</strong> ${formData.full_name}</p>
                    <p><strong>Date of Death:</strong> ${formData.date_of_death}</p>
                    <p><strong>Plot:</strong> Block ${document.getElementById('plot_block').value}, Section ${document.getElementById('plot_section').value}, Lot ${document.getElementById('plot_lot').value}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a8a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, save it!'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/api/save_burial_record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Burial record saved successfully',
                        confirmButtonColor: '#1e3a8a'
                    }).then(() => {
                        window.location.href = 'burial_records.php';
                    });
                } else {
                    showToast('error', 'Error', data.message || 'Failed to save record');
                }
            } catch (error) {
                console.error('Error saving burial record:', error);
                showToast('error', 'Error', 'Failed to save record');
            }
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadVacantPlots();
        }, 500);

        // Set max date for date of death and burial to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_of_death').setAttribute('max', today);
        document.getElementById('date_of_burial').setAttribute('max', today);
    });
</script>
