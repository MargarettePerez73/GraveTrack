<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cemetery Map - GraveTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
        }

        /* Header */
        .public-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #2563eb 100%);
            color: white;
            padding: 30px 20px;
            box-shadow: 0 8px 24px rgba(30, 58, 138, 0.3);
            position: relative;
            overflow: hidden;
        }

        .public-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .public-header h1 {
            margin: 0;
            font-size: 36px;
            font-weight: 800;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .public-header p {
            margin: 8px 0 0 0;
            opacity: 0.95;
            font-size: 16px;
            position: relative;
            z-index: 1;
            font-weight: 300;
        }

        .login-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            background: white;
            color: #1e3a8a;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 2;
        }

        .login-btn:hover {
            background: #fbbf24;
            color: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }

        /* Search Bar */
        .search-section {
            background: white;
            padding: 20px 30px;
            border-bottom: 3px solid #3b82f6;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .search-section .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 8px 0 0 8px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .search-section .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-section .btn {
            padding: 12px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .search-section .btn-primary {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 0;
        }

        .search-section .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af, #2563eb);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .search-section .btn-secondary {
            background: #64748b;
            border-radius: 0 8px 8px 0;
        }

        .search-section .btn-secondary:hover {
            background: #475569;
        }

        .info-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Map Container */
        .cemetery-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 220px);
            overflow: hidden;
        }

        .map-outer {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 30px;
        }

        .map-scaler {
            transform-origin: center center;
            display: inline-block;
        }

        .map-inner {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            border: 1px solid #e2e8f0;
        }

        .all-blocks {
            display: flex;
            align-items: flex-end;
            gap: 0;
        }

        .phase-divider {
            width: 5px;
            background: linear-gradient(to bottom, #3b82f6, #1e3a8a);
            align-self: stretch;
            flex-shrink: 0;
            margin: 0 10px;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2);
        }

        .pair-gap { width: 14px; flex-shrink: 0; }

        .block-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 5px;
        }

        .block-label {
            font-size: 15px;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 10px;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 6px 12px;
            border-radius: 6px;
            border: 2px solid #3b82f6;
        }

        .plots-stack {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* Plot Box - Enhanced Visual */
        .lot-box {
            width: 52px;
            height: 22px;
            border-radius: 6px;
            border: 2px solid #cbd5e1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            transition: all 0.2s ease;
            position: relative;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            color: #64748b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .lot-box:hover {
            transform: scale(1.3);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            z-index: 100;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-color: #3b82f6;
        }

        .lot-box.occupied {
            background: linear-gradient(135deg, #64748b, #475569);
            border-color: #334155;
            color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .lot-box.occupied:hover {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-color: #1e3a8a;
            box-shadow: 0 8px 24px rgba(30, 58, 138, 0.4);
        }

        .phase-labels-row {
            display: flex;
            width: 100%;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 4px solid;
            border-image: linear-gradient(to right, #1e3a8a, #3b82f6, #1e3a8a) 1;
            font-size: 15px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 1.5px;
        }

        .phase-label-cell {
            text-align: center;
            text-transform: uppercase;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #3b82f6;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        /* Modal Enhancements */
        .modal-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-body .table {
            margin-top: 15px;
        }

        .modal-body .table thead {
            background: #f1f5f9;
            color: #1e3a8a;
            font-weight: 700;
        }

        /* Legend Box */
        .legend-box {
            display: inline-flex;
            gap: 20px;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: 2px solid #e2e8f0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 14px;
            color: #334155;
        }

        .legend-color {
            width: 30px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid;
        }

        .legend-color.vacant {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-color: #cbd5e1;
        }

        .legend-color.occupied {
            background: linear-gradient(135deg, #64748b, #475569);
            border-color: #334155;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="public-header position-relative">
        <h1><i class="fas fa-map-marked-alt"></i> GraveTrack Cemetery Map</h1>
        <p>Public Cemetery Information & Location System</p>
        <a href="login.php" class="login-btn">
            <i class="fas fa-sign-in-alt"></i> Staff Login
        </a>
    </div>

    <!-- Search Bar -->
    <div class="search-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name...">
                        <button class="btn btn-primary" onclick="searchDeceased()">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button class="btn btn-secondary" onclick="clearSearch()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="info-badge">
                        <i class="fas fa-info-circle"></i> Click on any plot to view details
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="cemetery-page">
        <div class="map-outer" id="mapOuter">
            <div style="text-align: center; width: 100%;">
                <div class="legend-box" style="display: inline-flex;">
                    <div class="legend-item">
                        <div class="legend-color vacant"></div>
                        <span>Vacant Plot</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color occupied"></div>
                        <span>Occupied Plot</span>
                    </div>
                </div>
                <div class="map-scaler" id="mapScaler">
                    <div class="map-inner" id="mapInner">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Loading cemetery map...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plot Details Modal -->
    <div class="modal fade" id="plotModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Plot Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="plotModalContent">Loading...</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let allPlots = [];
        let allDeceasedRecords = [];
        const LOTS_PER_BLOCK = 20;

        async function loadCemeteryMap() {
            try {
                const response = await fetch('/api/get_cemetery_map.php');
                const data = await response.json();

                if (data.success) {
                    allPlots = data.plots;
                    await loadAllDeceasedRecords();
                    renderCemeteryMap();
                }
            } catch (error) {
                console.error('Error loading cemetery map:', error);
                document.getElementById('mapInner').innerHTML =
                    '<p class="text-center text-danger">Error loading map</p>';
            }
        }

        async function loadAllDeceasedRecords() {
            try {
                allDeceasedRecords = [];
                for (const plot of allPlots) {
                    if (plot.status === 'Occupied' && plot.deceased_count > 0) {
                        const response = await fetch(`/api/get_lot_details.php?plot_id=${plot.plot_id}`);
                        const data = await response.json();
                        if (data.success && data.deceased_records) {
                            data.deceased_records.forEach(record => {
                                allDeceasedRecords.push({
                                    ...record,
                                    plot_id: plot.plot_id,
                                    block: plot.block,
                                    section: plot.section,
                                    lot: plot.lot
                                });
                            });
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading deceased records:', error);
            }
        }

        function renderCemeteryMap() {
            const phases = {
                'Phase 1': {},
                'Phase 2': {},
                'Phase 3': {}
            };

            allPlots.forEach(plot => {
                const phase = plot.phase;
                const block = plot.block;

                if (!phases[phase]) phases[phase] = {};
                if (!phases[phase][block]) phases[phase][block] = [];

                phases[phase][block].push(plot);
            });

            function findPlot(block, lot, phase) {
                const plots = phases[phase] ? phases[phase][block] : null;
                if (!plots) return null;
                return plots.find(p => parseInt(p.lot) === lot);
            }

            let html = '<div class="all-blocks" id="allBlocks">';

            // PHASE 3
            html += renderBlockColumn('AA', 'Phase 3', findPlot, 20);
            html += renderBlockColumn('', 'Phase 3', findPlot, 10);

            html += '<div class="phase-divider"></div>';

            // PHASE 2
            const phase2Groups = [['Z', 'Y'], ['X', 'W'], ['V', 'U'], ['T']];
            phase2Groups.forEach((group, gi) => {
                if (gi > 0) html += '<div class="pair-gap"></div>';
                group.forEach(blockName => {
                    html += renderBlockColumn(blockName, 'Phase 2', findPlot, 20);
                });
            });

            html += '<div class="phase-divider"></div>';

            // PHASE 1
            const phase1Groups = [['I', 'H'], ['G', 'F'], ['E', 'D'], ['C', 'B'], ['A']];
            phase1Groups.forEach((group, gi) => {
                if (gi > 0) html += '<div class="pair-gap"></div>';
                group.forEach(blockName => {
                    html += renderBlockColumn(blockName, 'Phase 1', findPlot, 20);
                });
            });

            html += '</div>';

            html += `
                <div class="phase-labels-row">
                    <div class="phase-label-cell" style="flex: 1;">PHASE 3</div>
                    <div style="width: 20px;"></div>
                    <div class="phase-label-cell" style="flex: 3;">PHASE 2</div>
                    <div style="width: 20px;"></div>
                    <div class="phase-label-cell" style="flex: 3;">PHASE 1</div>
                </div>
            `;

            document.getElementById('mapInner').innerHTML = html;
            setTimeout(scaleMap, 100);
        }

        function renderBlockColumn(blockName, phaseName, findPlot, lotsCount = 20) {
            let html = '<div class="block-col">';
            html += `<div class="block-label">${blockName || '&nbsp;'}</div>`;
            html += '<div class="plots-stack">';

            for (let lot = lotsCount; lot >= 1; lot--) {
                const plot = findPlot(blockName, lot, phaseName);
                const isOccupied = plot && plot.status === 'Occupied';
                const plotId = plot ? plot.plot_id : null;
                const displayBlock = blockName || 'Unnamed';

                let tooltip = '';
                if (plot) {
                    tooltip = `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot}`;
                    if (plot.deceased_count > 0) {
                        tooltip += ` (${plot.deceased_count} deceased)`;
                    }
                } else {
                    tooltip = `Block ${displayBlock}, Lot ${lot} - Vacant`;
                }

                html += `
                    <div class="lot-box ${isOccupied ? 'occupied' : ''}"
                         title="${tooltip}"
                         onclick="viewPlotDetails(${plotId}, '${blockName}', ${lot}, '${phaseName}')">
                        ${lot}
                    </div>
                `;
            }

            html += '</div></div>';
            return html;
        }

        async function viewPlotDetails(plotId, blockName, lotNumber, phaseName) {
            if (!plotId || plotId === null) {
                const displayBlock = blockName || 'Unnamed';
                document.getElementById('modalTitle').innerHTML =
                    `<i class="fas fa-map-marker-alt"></i> Block ${displayBlock}, Lot ${lotNumber}`;
                document.getElementById('plotModalContent').innerHTML = `
                    <div class="alert alert-info" style="background: #dbeafe; border: 2px solid #3b82f6; color: #1e40af;">
                        <h6 style="margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Vacant Plot</h6>
                        <p style="margin-bottom: 0;">This plot is currently available and not occupied.</p>
                    </div>
                `;

                const modal = new bootstrap.Modal(document.getElementById('plotModal'));
                modal.show();
                return;
            }

            try {
                const response = await fetch(`/api/get_lot_details.php?plot_id=${plotId}`);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('modalTitle').innerHTML =
                        `<i class="fas fa-map-marker-alt"></i> Block ${data.plot.block}, Section ${data.plot.section}, Lot ${data.plot.lot}`;

                    let content = '<div class="row mb-3">';
                    content += '<div class="col-md-6">';
                    content += `<p><strong><i class="fas fa-monument"></i> Type:</strong> ${data.plot.type}</p>`;
                    content += '</div>';
                    content += '<div class="col-md-6">';
                    content += `<p><strong><i class="fas fa-info-circle"></i> Status:</strong> <span class="badge bg-secondary">Occupied</span></p>`;
                    content += '</div>';
                    content += '</div>';

                    if (data.deceased_records && data.deceased_records.length > 0) {
                        content += '<hr style="margin: 20px 0; border-top: 2px solid #e2e8f0;">';
                        content += '<h6 style="color: #1e3a8a; font-weight: 700; margin-bottom: 15px;"><i class="fas fa-user"></i> Deceased Information</h6>';
                        content += '<div class="table-responsive"><table class="table table-hover">';
                        content += '<thead><tr><th><i class="fas fa-user-circle"></i> Full Name</th><th><i class="fas fa-birthday-cake"></i> Date of Birth</th><th><i class="fas fa-cross"></i> Date of Death</th></tr></thead><tbody>';

                        data.deceased_records.forEach(record => {
                            content += `
                                <tr>
                                    <td><strong style="color: #1e3a8a;">${record.full_name}</strong></td>
                                    <td>${formatDate(record.birth_date)}</td>
                                    <td>${formatDate(record.date_of_death)}</td>
                                </tr>
                            `;
                        });

                        content += '</tbody></table></div>';

                        if (data.deceased_records.length > 1) {
                            content += `<p class="text-muted text-end" style="font-size: 12px; margin-top: 10px;"><i class="fas fa-users"></i> ${data.deceased_records.length} persons interred in this plot</p>`;
                        }
                    } else {
                        content += '<hr><div class="alert alert-secondary text-center" style="margin-top: 20px;"><i class="fas fa-info-circle"></i> No deceased records available for this plot</div>';
                    }

                    document.getElementById('plotModalContent').innerHTML = content;

                    const modal = new bootstrap.Modal(document.getElementById('plotModal'));
                    modal.show();
                } else {
                    console.error('Failed to load plot details:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load plot details',
                        confirmButtonColor: '#1e3a8a'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch plot information',
                    confirmButtonColor: '#1e3a8a'
                });
            }
        }

        function searchDeceased() {
            const searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();

            if (!searchTerm) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Search',
                    text: 'Please enter a name to search',
                    confirmButtonColor: '#1e3a8a'
                });
                return;
            }

            const results = allDeceasedRecords.filter(record =>
                record.full_name.toLowerCase().includes(searchTerm)
            );

            if (results.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Results',
                    text: 'No deceased found with that name',
                    confirmButtonColor: '#1e3a8a'
                });
                return;
            }

            if (results.length === 1) {
                viewPlotDetails(results[0].plot_id, results[0].block, results[0].lot, 'Phase ' + results[0].phase);
                highlightPlot(results[0].block, results[0].lot);
            } else {
                let html = '<div class="list-group" style="max-height: 400px; overflow-y: auto;">';
                results.forEach(record => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action"
                           style="border-left: 4px solid #3b82f6; margin-bottom: 8px; border-radius: 6px;"
                           onclick="viewPlotDetails(${record.plot_id}, '${record.block}', ${record.lot}, 'Phase ${record.phase}'); highlightPlot('${record.block}', ${record.lot}); return false;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-user-circle" style="font-size: 24px; color: #64748b;"></i>
                                <div style="flex: 1;">
                                    <strong style="color: #1e3a8a; font-size: 16px;">${record.full_name}</strong><br>
                                    <small style="color: #64748b;"><i class="fas fa-map-marker-alt"></i> Block ${record.block}, Section ${record.section}, Lot ${record.lot}</small><br>
                                    <small style="color: #94a3b8;"><i class="fas fa-calendar"></i> Born: ${formatDate(record.birth_date)} | <i class="fas fa-cross"></i> Died: ${formatDate(record.date_of_death)}</small>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #cbd5e1;"></i>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';

                Swal.fire({
                    title: `<i class="fas fa-search"></i> Found ${results.length} Results`,
                    html: html,
                    width: '600px',
                    confirmButtonColor: '#1e3a8a',
                    confirmButtonText: 'Close'
                });
            }
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            removeHighlight();
        }

        function highlightPlot(block, lot) {
            removeHighlight();
            const boxes = document.querySelectorAll('.lot-box');
            boxes.forEach(box => {
                const text = box.getAttribute('title');
                if (text && text.includes(`Block ${block}`) && text.includes(`Lot ${lot}`)) {
                    box.style.outline = '4px solid #fbbf24';
                    box.style.outlineOffset = '2px';
                    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }

        function removeHighlight() {
            const boxes = document.querySelectorAll('.lot-box');
            boxes.forEach(box => {
                box.style.outline = '';
                box.style.outlineOffset = '';
            });
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function scaleMap() {
            const outer = document.getElementById('mapOuter');
            const scaler = document.getElementById('mapScaler');
            const inner = document.getElementById('mapInner');

            if (!outer || !scaler || !inner) return;

            scaler.style.transform = 'scale(1)';

            const availW = outer.clientWidth - 60;
            const availH = outer.clientHeight - 120;
            const natW = inner.scrollWidth;
            const natH = inner.scrollHeight;

            const scale = Math.min(availW / natW, availH / natH, 1);
            scaler.style.transform = `scale(${scale})`;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadCemeteryMap();
            window.addEventListener('resize', scaleMap);

            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchDeceased();
                }
            });
        });
    </script>
</body>
</html>
