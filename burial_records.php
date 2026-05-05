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

<?php include 'includes/footer.php'; ?>

<script>
    let allRecords = [];

    async function loadBurialRecords() {
        try {
            const response = await fetch('/api/get_cemetery_map.php');
            const data = await response.json();

            if (data.success) {
                // Get all occupied plots
                const occupiedPlots = data.plots.filter(p => p.status === 'Occupied' && p.deceased_count > 0);

                // Load details for each occupied plot
                allRecords = [];
                for (const plot of occupiedPlots) {
                    const detailsResponse = await fetch(`/api/get_lot_details.php?plot_id=${plot.plot_id}`);
                    const detailsData = await detailsResponse.json();

                    if (detailsData.success && detailsData.deceased_records) {
                        detailsData.deceased_records.forEach(record => {
                            allRecords.push({
                                deceased_id: record.deceased_id,
                                full_name: record.full_name,
                                date_of_death: record.date_of_death,
                                date_of_burial: record.date_of_burial,
                                gender: record.gender,
                                plot_location: `${plot.block} - ${plot.section} - ${plot.lot}`,
                                plot_id: plot.plot_id,
                                contact_person: record.contact_person,
                                contact_number: record.contact_number,
                                address: record.address,
                                burial_type: record.burial_type
                            });
                        });
                    }
                }

                displayRecords(allRecords);
            }
        } catch (error) {
            console.error('Error loading burial records:', error);
            document.getElementById('recordsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading records</td></tr>';
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
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info" onclick="viewPlotDetails(${record.plot_id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary" onclick="editRecord(${record.deceased_id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
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
                let content = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Block:</strong> ${data.plot.block}</p>
                            <p><strong>Section:</strong> ${data.plot.section}</p>
                            <p><strong>Lot:</strong> ${data.plot.lot}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong> ${data.plot.type}</p>
                            <p><strong>Status:</strong> ${getStatusBadge(data.plot.status)}</p>
                        </div>
                    </div>
                    <hr>
                    <h6><strong>Deceased Records:</strong></h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date of Death</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                if (data.deceased_records && data.deceased_records.length > 0) {
                    data.deceased_records.forEach(record => {
                        content += `
                            <tr>
                                <td><strong>${record.full_name}</strong></td>
                                <td>${formatDate(record.date_of_death)}</td>
                                <td>${record.contact_person || 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editRecord(${record.deceased_id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                content += `
                            </tbody>
                        </table>
                    </div>
                `;

                Swal.fire({
                    title: 'Plot Details',
                    html: content,
                    width: 800,
                    confirmButtonColor: '#1e3a8a'
                });
            }
        } catch (error) {
            console.error('Error loading details:', error);
        }
    }

    function editRecord(deceasedId) {
        window.location.href = `edit_burial_record.php?id=${deceasedId}`;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadBurialRecords();
        }, 500);
    });
</script>
