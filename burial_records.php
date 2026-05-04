<?php
$pageTitle = 'Burial Records';
$currentPage = 'burial_records';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">
            <i class="fas fa-book"></i>
            Burial Records
        </h1>
        <a href="adding_burial_records.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Record
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="search-filter-section">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label"><strong>Search</strong></label>
                <input
                    type="text"
                    class="form-control"
                    id="searchInput"
                    placeholder="Search by name, contact, or plot location..."
                    onkeyup="searchRecords()"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>Gender</strong></label>
                <select class="form-control" id="filterGender" onchange="searchRecords()">
                    <option value="">All</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><strong>&nbsp;</strong></label>
                <button class="btn btn-secondary w-100" onclick="clearSearch()">
                    <i class="fas fa-redo"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table"></i> All Burial Records</span>
            <span id="recordCount" class="badge bg-light text-dark">0 records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="recordsTable">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Date of Death</th>
                            <th>Date of Burial</th>
                            <th>Gender</th>
                            <th>Plot Location</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recordsTableBody">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                Loading records...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Record Details Modal -->
<div class="modal fade" id="recordDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user"></i> Burial Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recordDetailsContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allRecords = [];

    async function loadBurialRecords() {
        try {
            const response = await fetch('/api/get_plots.php');
            const data = await response.json();

            if (data.success) {
                // Get burial records view (we'll need to create this)
                // For now, we'll simulate by getting deceased from plots
                loadFromCemeteryMap();
            }
        } catch (error) {
            console.error('Error loading burial records:', error);
            document.getElementById('recordsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading records</td></tr>';
        }
    }

    async function loadFromCemeteryMap() {
        try {
            const response = await fetch('/api/get_cemetery_map.php');
            const data = await response.json();

            if (data.success) {
                // Filter only occupied plots
                const occupied = data.plots.filter(p => p.status === 'Occupied' && p.deceased_count > 0);

                // Create records array
                allRecords = [];
                occupied.forEach(plot => {
                    if (plot.deceased_names) {
                        const names = plot.deceased_names.split(', ');
                        names.forEach(name => {
                            allRecords.push({
                                full_name: name,
                                plot_location: `${plot.block} - ${plot.section} - ${plot.lot}`,
                                plot_id: plot.plot_id,
                                type: plot.type
                            });
                        });
                    }
                });

                displayRecords(allRecords);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function displayRecords(records) {
        const tbody = document.getElementById('recordsTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No records found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        records.forEach(record => {
            const row = `
                <tr>
                    <td><strong>${record.full_name || 'N/A'}</strong></td>
                    <td>${record.date_of_death ? formatDate(record.date_of_death) : 'N/A'}</td>
                    <td>${record.date_of_burial ? formatDate(record.date_of_burial) : 'N/A'}</td>
                    <td>${record.gender || 'N/A'}</td>
                    <td><span class="badge bg-primary">${record.plot_location}</span></td>
                    <td>${record.contact_person || 'N/A'}</td>
                    <td>${record.contact_number || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewPlotDetails(${record.plot_id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('recordCount').textContent = `${records.length} records`;
    }

    function searchRecords() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const genderFilter = document.getElementById('filterGender').value;

        let filtered = allRecords;

        if (searchTerm) {
            filtered = filtered.filter(r => {
                return (r.full_name && r.full_name.toLowerCase().includes(searchTerm)) ||
                       (r.contact_person && r.contact_person.toLowerCase().includes(searchTerm)) ||
                       (r.plot_location && r.plot_location.toLowerCase().includes(searchTerm));
            });
        }

        if (genderFilter) {
            filtered = filtered.filter(r => r.gender === genderFilter);
        }

        displayRecords(filtered);
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterGender').value = '';
        displayRecords(allRecords);
    }

    async function viewPlotDetails(plotId) {
        try {
            const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
            const data = await response.json();

            if (data.success) {
                document.getElementById('recordDetailsContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('recordDetailsModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error loading details:', error);
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadBurialRecords();
        }, 500);
    });
</script>
