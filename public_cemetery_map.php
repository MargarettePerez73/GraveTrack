<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cemetery Map - GraveTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        /* Header */
        .public-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .public-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .public-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .login-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            color: #1e3a8a;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Search Bar */
        .search-section {
            background: white;
            padding: 15px 20px;
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Map Container */
        .cemetery-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 150px);
            overflow: hidden;
        }

        .map-outer {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f5f5f5;
            padding: 20px;
        }

        .map-scaler {
            transform-origin: center center;
            display: inline-block;
        }

        .map-inner {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .all-blocks {
            display: flex;
            align-items: flex-end;
            gap: 0;
        }

        .phase-divider {
            width: 4px;
            background: #64748b;
            align-self: stretch;
            flex-shrink: 0;
            margin: 0 8px;
        }

        .pair-gap { width: 12px; flex-shrink: 0; }

        .block-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 4px;
        }

        .block-label {
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .plots-stack {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        /* Plot Box - Simple Grey */
        .lot-box {
            width: 50px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            transition: transform 0.1s, box-shadow 0.1s;
            position: relative;
            background: #e2e8f0;
            color: #475569;
        }

        .lot-box:hover {
            transform: scale(1.25);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 100;
            background: #cbd5e1;
        }

        .lot-box.occupied {
            background: #94a3b8;
            border-color: #64748b;
            color: white;
        }

        .phase-labels-row {
            display: flex;
            width: 100%;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 3px solid #1e3a8a;
            font-size: 14px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 1px;
        }

        .phase-label-cell {
            text-align: center;
            text-transform: uppercase;
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
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Click on any plot to view details
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="cemetery-page">
        <div class="map-outer" id="mapOuter">
            <div class="map-scaler" id="mapScaler">
                <div class="map-inner" id="mapInner">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Loading cemetery map...</p>
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
                const plotId = plot ? plot.plot_id : 'null';
                const tooltip = plot ?
                    `Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot}` :
                    `Block ${blockName || 'Unnamed'}, Lot ${lot}`;

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
            if (plotId === 'null' || !plotId) {
                const displayBlock = blockName || 'Unnamed';
                document.getElementById('modalTitle').textContent = `Plot: Block ${displayBlock}, Lot ${lotNumber}`;
                document.getElementById('plotModalContent').innerHTML = `
                    <div class="alert alert-info">
                        <p><strong>Vacant Plot</strong></p>
                        <p>This plot is currently vacant.</p>
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
                    document.getElementById('modalTitle').textContent =
                        `Plot: Block ${data.plot.block}, Section ${data.plot.section}, Lot ${data.plot.lot}`;

                    let content = '<div class="row mb-3">';
                    content += '<div class="col-md-12">';
                    content += `<p><strong>Type:</strong> ${data.plot.type}</p>`;
                    content += '</div></div>';

                    if (data.deceased_records && data.deceased_records.length > 0) {
                        content += '<hr><h6><strong>Deceased Information:</strong></h6>';
                        content += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                        content += '<thead><tr><th>Name</th><th>Date of Birth</th><th>Date of Death</th></tr></thead><tbody>';

                        data.deceased_records.forEach(record => {
                            content += `
                                <tr>
                                    <td><strong>${record.full_name}</strong></td>
                                    <td>${formatDate(record.birth_date)}</td>
                                    <td>${formatDate(record.date_of_death)}</td>
                                </tr>
                            `;
                        });

                        content += '</tbody></table></div>';
                    } else {
                        content += '<hr><p class="text-muted text-center">No records available</p>';
                    }

                    document.getElementById('plotModalContent').innerHTML = content;

                    const modal = new bootstrap.Modal(document.getElementById('plotModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error:', error);
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
                let html = '<div class="list-group">';
                results.forEach(record => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action"
                           onclick="viewPlotDetails(${record.plot_id}, '${record.block}', ${record.lot}, 'Phase ${record.phase}'); highlightPlot('${record.block}', ${record.lot}); return false;">
                            <strong>${record.full_name}</strong><br>
                            <small>Block ${record.block}, Section ${record.section}, Lot ${record.lot}</small><br>
                            <small class="text-muted">Born: ${formatDate(record.birth_date)} | Died: ${formatDate(record.date_of_death)}</small>
                        </a>
                    `;
                });
                html += '</div>';

                Swal.fire({
                    title: `Found ${results.length} Results`,
                    html: html,
                    width: '500px',
                    confirmButtonColor: '#1e3a8a'
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

            const availW = outer.clientWidth - 40;
            const availH = outer.clientHeight - 40;
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
