<?php
$pageTitle = 'Burial Records';
$currentPage = 'burial_records';
include 'includes/header.php';
?>

<style>
    .records-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .records-sidebar {
        width: 280px;
        background: white;
        border-right: 3px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 4px 0 12px rgba(0,0,0,0.05);
    }

    .sidebar-section {
        padding: 20px;
        border-bottom: 2px solid #f1f5f9;
    }

    .sidebar-section h6 {
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 15px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group {
        margin-bottom: 15px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 8px;
        display: block;
    }

    .filter-input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.3s;
    }

    .filter-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .stat-box {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        border: 2px solid #3b82f6;
    }

    .stat-box-label {
        font-size: 11px;
        color: #1e40af;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stat-box-value {
        font-size: 28px;
        font-weight: 800;
        color: #1e3a8a;
        margin-top: 5px;
    }

    .action-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
        margin-bottom: 10px;
    }

    .action-btn:hover {
        background: linear-gradient(135deg, #1e40af, #2563eb);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        color: white;
    }

    .clear-btn {
        background: #64748b;
    }

    .clear-btn:hover {
        background: #475569;
    }

    .records-main {
        flex: 1;
        overflow-y: auto;
        background: #f8fafc;
        padding: 30px;
    }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #1e3a8a;
        margin: 0;
    }

    .add-record-btn {
        padding: 12px 24px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .add-record-btn:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .records-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .records-card-header {
        padding: 20px 25px;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .records-card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .record-count-badge {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e3a8a;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        border: 2px solid #3b82f6;
    }

    .table-wrapper {
        padding: 25px;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table thead th {
        background: #f8fafc;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        border-bottom: 2px solid #e2e8f0;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .custom-table tbody tr {
        transition: all 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .custom-table tbody td {
        padding: 15px 12px;
        font-size: 13px;
        color: #334155;
    }

    .name-cell {
        font-weight: 700;
        color: #1e3a8a;
    }

    .edit-btn {
        padding: 6px 14px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-block;
    }

    .edit-btn:hover {
        background: linear-gradient(135deg, #2563eb, #1e40af);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }
</style>

<div class="records-layout">
    <!-- Sidebar -->
    <div class="records-sidebar">
        <div class="sidebar-section">
            <h6><i class="fas fa-chart-bar"></i> Statistics</h6>
            <div class="stat-box">
                <div class="stat-box-label">Total Records</div>
                <div class="stat-box-value" id="totalRecords">0</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-filter"></i> Filters</h6>

            <div class="filter-group">
                <label class="filter-label">Search by Name</label>
                <input
                    type="text"
                    class="filter-input"
                    id="searchInput"
                    placeholder="Type to search..."
                    onkeyup="searchRecords()"
                >
            </div>

            <div class="filter-group">
                <label class="filter-label">Gender</label>
                <select class="filter-input" id="filterGender" onchange="searchRecords()">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Burial Type</label>
                <select class="filter-input" id="filterBurialType" onchange="searchRecords()">
                    <option value="">All Types</option>
                    <option value="Ground">Ground</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Mausoleum">Mausoleum</option>
                    <option value="Family Burial">Family Burial</option>
                </select>
            </div>

            <button class="action-btn clear-btn" onclick="clearSearch()">
                <i class="fas fa-redo"></i> Clear Filters
            </button>
        </div>

        <div class="sidebar-section">
            <h6><i class="fas fa-bolt"></i> Actions</h6>
            <a href="adding_burial_records.php" class="action-btn">
                <i class="fas fa-plus"></i> Add New Record
            </a>
            <a href="cemetery_map.php" class="action-btn">
                <i class="fas fa-map"></i> View Cemetery Map
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="records-main">
        <div class="page-header">
            <h1><i class="fas fa-book"></i> Burial Records</h1>
            <a href="adding_burial_records.php" class="add-record-btn">
                <i class="fas fa-plus"></i> Add New Record
            </a>
        </div>

        <div class="records-card">
            <div class="records-card-header">
                <h5><i class="fas fa-table"></i> All Burial Records</h5>
                <span class="record-count-badge" id="recordCount">0 records</span>
            </div>
            <div class="table-wrapper">
                <div style="overflow-x: auto;">
                    <table class="custom-table">
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
                                <td colspan="8" class="text-center" style="padding: 40px;">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-3" style="color: #64748b;">Loading burial records...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                const occupiedPlots = data.plots.filter(p => p.status === 'Occupied' && p.deceased_count > 0);

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
                document.getElementById('totalRecords').textContent = allRecords.length;
            }
        } catch (error) {
            console.error('Error loading burial records:', error);
            document.getElementById('recordsTableBody').innerHTML =
                '<tr><td colspan="8" class="text-center text-danger">Error loading records</td></tr>';
        }
    }

    function displayRecords(records) {
        const tbody = document.getElementById('recordsTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 40px; color: #94a3b8;">No records found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        records.forEach(record => {
            const row = `
                <tr>
                    <td class="name-cell">${record.full_name}</td>
                    <td>${formatDate(record.date_of_death)}</td>
                    <td>${formatDate(record.date_of_burial)}</td>
                    <td>${record.gender || 'N/A'}</td>
                    <td><strong>${record.plot_location}</strong></td>
                    <td>${record.contact_person || 'N/A'}</td>
                    <td>${record.contact_number || 'N/A'}</td>
                    <td>
                        <a href="edit_burial_record.php?id=${record.deceased_id}" class="edit-btn">
                            <i class="fas fa-edit"></i> Edit
                        </a>
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
        const burialTypeFilter = document.getElementById('filterBurialType').value;

        const filtered = allRecords.filter(record => {
            const matchesSearch =
                record.full_name.toLowerCase().includes(searchTerm) ||
                (record.contact_person && record.contact_person.toLowerCase().includes(searchTerm)) ||
                record.plot_location.toLowerCase().includes(searchTerm);

            const matchesGender = !genderFilter || record.gender === genderFilter;
            const matchesBurialType = !burialTypeFilter || record.burial_type === burialTypeFilter;

            return matchesSearch && matchesGender && matchesBurialType;
        });

        displayRecords(filtered);
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterGender').value = '';
        document.getElementById('filterBurialType').value = '';
        displayRecords(allRecords);
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            loadBurialRecords();
        }, 500);
    });
</script>
