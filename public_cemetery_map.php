<?php
$pageTitle = 'Municipality of Tuy, Magahis Cemetery Map';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        .overdue-compact {
            padding: 0.75rem 1rem !important;
            margin-bottom: 1rem !important;
            font-size: 0.875rem;
        }
        .overdue-compact .btn {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #plotModal .modal-header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        #plotModal .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #plotModal .modal-title {
            font-weight: 800;
            font-size: 15px;
            letter-spacing: 0.2px;
        }
        .plot-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        @media (max-width: 576px) {
            .plot-meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .plot-mini-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 10px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
            min-width: 0;
        }
        .plot-mini-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
            display: block;
        }
        .plot-mini-value {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .plot-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 12px 0 8px;
        }
        .plot-section-title h6 {
            margin: 0;
            font-weight: 800;
            color: #1e3a8a;
            font-size: 13px;
            letter-spacing: 0.2px;
        }
        .deceased-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        @media (max-width: 768px) {
            .deceased-cards { grid-template-columns: 1fr; }
        }
        .deceased-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
        }
        .deceased-name {
            font-weight: 900;
            color: #0f172a;
            font-size: 13px;
            line-height: 1.2;
            margin: 0 0 6px;
        }
        .deceased-meta {
            font-size: 12px;
            color: #475569;
            margin: 0;
            line-height: 1.35;
        }
        .deceased-meta small { color: #64748b; }

        /* ── Simple Header ── */
        .simple-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 0.75rem 0;
            box-shadow: 0 2px 8px rgba(30,58,138,0.2);
        }
        .header-brand {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 12px;
        }
        .header-logo-wrap {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        }
        .header-logo-wrap img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .header-center {
            text-align: center;
        }
        .header-spacer {
            width: 56px;
            height: 56px;
        }
        .header-title {
            font-size: 17px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.3px;
        }
        .header-subtitle {
            font-size: 12px;
            color: rgba(255,255,255,0.65);
            margin: 0;
            font-weight: 500;
        }

        /* ── Simple Filter Bar (Top) ── */
        .filter-bar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .filter-bar .search-box {
            position: relative;
        }
        .filter-bar .search-box input {
            width: 100%;
            padding: 10px 42px 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.25s;
            background: #f8fafc;
        }
        .filter-bar .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
        }
        .filter-bar .search-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .filter-bar .aa-filter {
            min-width: 220px;
        }
        .filter-bar .section-select-wrap {
            position: relative;
        }
        .filter-bar .section-select-wrap::before {
            content: "\f3c5";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #2563eb;
            font-size: 12px;
            pointer-events: none;
        }
        .filter-bar .section-select-wrap::after {
            content: "\f107";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 12px;
            pointer-events: none;
        }
        .filter-bar .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 34px 10px 34px;
            font-size: 14px;
            cursor: pointer;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            font-weight: 600;
            color: #334155;
            appearance: none;
        }
        .filter-bar .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
        }

        /* Search Results Dropdown */
        .search-results {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 0;
            max-height: 380px;
            overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            z-index: 1000;
            display: none;
        }
        .search-results.active { display: block; }
        .search-result-item {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s;
        }
        .search-result-item:hover { background: #f1f5f9; }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-name  { font-weight: 700; color: #1e293b; font-size: 14px; }
        .search-result-location { font-size: 12px; color: #64748b; margin-top: 4px; }
        .search-result-dates { font-size: 12px; color: #475569; margin-top: 3px; }
        .no-results {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }

        /* ── Map Layout ── */
        .cemetery-layout {
            display: block;
            min-height: calc(100vh - 124px);
            /* Allow scaled map + phase labels to extend; avoid clipping */
            overflow: visible;
        }

        /* Legend */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #475569;
        }
        .legend-box {
            width: 26px;
            height: 16px;
            border-radius: 4px;
            border: 2px solid;
            flex-shrink: 0;
        }
        .legend-box.occupied { background: #ef4444; border-color: #dc2626; }
        .legend-box.vacant { background: #10b981; border-color: #059669; }

        /* Stats */
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .stat-label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 18px; font-weight: 800; color: #1e3a8a; }

        /* Map Container */
        .map-container {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: calc(100vh - 124px);
            /* Centering + overflow:hidden was cutting phase labels in half */
            overflow-x: auto;
            overflow-y: auto;
            background: #f1f5f9;
            padding: 24px 24px 40px;
        }

        .map-scaler {
            transform-origin: top center;
            display: inline-block;
            flex-shrink: 0;
        }

        .map-inner {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .all-blocks { display: flex; align-items: flex-end; gap: 0; }

        .phase-divider {
            width: 5px;
            background: #64748b;
            align-self: stretch;
            flex-shrink: 0;
            margin: 0 10px;
            border-radius: 3px;
        }

        .pair-gap { width: 16px; flex-shrink: 0; }

        .block-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 6px;
        }

        .block-label {
            font-size: 15px;
            font-weight: 800;
            color: #475569;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .plots-stack { display: flex; flex-direction: column; gap: 3px; }

        .lot-box {
            width: 46px;
            height: 22px;
            border-radius: 5px;
            border: 2px solid;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            color: white;
            transition: transform 0.15s, box-shadow 0.15s;
            position: relative;
        }
        .lot-box:hover {
            transform: scale(1.2);
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .lot-box.vacant { background: #10b981; border-color: #059669; }
        .lot-box.occupied { background: #ef4444; border-color: #dc2626; }

        .lot-box.highlighted {
            box-shadow: 0 0 0 4px #fbbf24, 0 0 20px rgba(251,191,36,0.6);
            transform: scale(1.25) !important;
            background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
            border-color: #d97706 !important;
            animation: pulse 1.5s infinite !important;
        }

        .lot-box.blurred {
            filter: blur(1.5px) opacity(0.4);
            transform: scale(0.9) !important;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 4px #fbbf24, 0 0 20px rgba(251,191,36,0.6); }
            50% { box-shadow: 0 0 0 6px #fbbf24, 0 0 30px rgba(251,191,36,0.8); }
        }

        .phase-labels-row {
            display: flex;
            width: 100%;
            margin-top: 22px;
            padding-top: 16px;
            padding-bottom: 8px;
            border-top: 2px solid #cbd5e1;
            font-size: 13px;
            font-weight: 800;
            color: #475569;
            letter-spacing: 1px;
            line-height: 1.35;
            flex-shrink: 0;
        }
        .phase-label-cell {
            text-align: center;
            text-transform: uppercase;
            flex: 1;
            min-height: 1.35em;
        }

        /* ── Simple Footer ── */
        .simple-footer {
            background: #1e293b;
            color: #94a3b8;
            padding: 1rem 0;
            text-align: center;
            font-size: 12px;
        }

        .footer-brand {
            font-weight: 700;
            color: white;
            font-size: 14px;
            margin-bottom: 4px;
        }

        /* Modal */
        .deceased-card.focused {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.22), 0 10px 24px rgba(15, 23, 42, 0.12);
            animation: focusPulse 1.2s ease-out 1;
        }
        @keyframes focusPulse {
            0%   { transform: translateY(0); }
            40%  { transform: translateY(-2px); }
            100% { transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-bar .row { flex-direction: column; gap: 10px; }
            .filter-bar .aa-filter { width: 100%; }
            .simple-header { padding: 12px 0; }
            .header-title { font-size: 15px; }
            .header-logo-wrap, .header-spacer {
                width: 46px;
                height: 46px;
            }
            .header-logo-wrap img {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>

    <!-- Simple Header -->
    <header class="simple-header">
        <div class="container">
            <div class="header-brand">
                <div class="header-logo-wrap">
                    <img src="img/municipal_logo.png" alt="Municipality of Tuy Logo">
                </div>
                <div class="header-center">
                    <h1 class="header-title">Municipality of Tuy, Magahis Cemetery Map</h1>
                    <p class="header-subtitle">Public Burial Records</p>
                </div>
                <div class="header-spacer" aria-hidden="true"></div>
            </div>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-lg-5 col-md-6">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name..." autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <div class="search-results" id="searchResults"></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 aa-filter">
                    <div class="section-select-wrap">
                        <select id="aaStaffSectionSelect" class="form-select">
                            <option value="1">AA Block — Section 1</option>
                            <option value="2">AA Block — Section 2</option>
                            <option value="3">AA Block — Section 3</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-md-2 d-none d-md-block">
                    <div class="text-secondary small">
                        <i class="fas fa-info-circle me-1"></i>
                        Click any plot to view details
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cemetery-layout">
        <!-- Map -->
        <main class="map-container" id="mapContainer">
            <div class="map-scaler" id="mapScaler">
                <div class="map-inner" id="mapInner">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading cemetery map...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Simple Footer -->
    <footer class="simple-footer">
        <div class="container">
            <div class="footer-brand">Municipality of Tuy, Magahis Cemetery</div>
            <p class="mb-0">&copy; 2026 Municipality of Tuy. All rights reserved.</p>
        </div>
    </footer>

    <!-- Plot Details Modal -->
    <div class="modal fade" id="plotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Plot Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="plotModalContent">Loading...</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let allPlots = [];
        let allDeceasedRecords = [];
        let searchTimeout = null;
        let currentAASection = '1';

        function changeAASection(section) {
            currentAASection = section;
            document.getElementById('aaStaffSectionSelect').value = section;
            renderCemeteryMap();
            scaleMap();
        }

        async function loadCemeteryMap() {
            try {
                const response = await fetch('/api/get_public_cemetery_map.php');
                const data = await response.json();
                if (data.success) {
                    allPlots = data.plots || [];
                    await loadAllDeceasedRecords();
                    renderCemeteryMap();
                    updateStatistics();
                }
            } catch (error) {
                console.error('Error loading map:', error);
                document.getElementById('mapInner').innerHTML =
                    '<p class="text-center text-danger p-4">Error loading map. Please refresh.</p>';
            }
        }

        async function loadAllDeceasedRecords() {
            try {
                allDeceasedRecords = [];
                let totalCount = 0;
                for (const plot of allPlots) {
                    if (plot.status === 'Occupied' && plot.deceased_count > 0) {
                        const response = await fetch(`/api/get_public_lot_details.php?plot_id=${plot.plot_id}`);
                        const data = await response.json();
                        if (data.success && data.deceased_records) {
                            data.deceased_records.forEach(record => {
                                allDeceasedRecords.push({
                                    ...record,
                                    plot_id: plot.plot_id,
                                    block: plot.block,
                                    section: plot.section,
                                    lot: plot.lot,
                                    phase: plot.phase,
                                });
                                totalCount++;
                            });
                        }
                    }
                }
                const totalDeceasedEl = document.getElementById('totalDeceased');
                if (totalDeceasedEl) totalDeceasedEl.textContent = totalCount;
            } catch (error) {
                console.error('Error loading deceased records:', error);
            }
        }

        function updateStatistics() {
            const vacant = allPlots.filter(p => p.status === 'Vacant').length;
            const occupied = allPlots.filter(p => p.status === 'Occupied').length;
            const totalEl = document.getElementById('totalPlots');
            const vacantEl = document.getElementById('vacantPlots');
            const occupiedEl = document.getElementById('occupiedPlots');
            if (totalEl) totalEl.textContent = allPlots.length;
            if (vacantEl) vacantEl.textContent = vacant;
            if (occupiedEl) occupiedEl.textContent = occupied;
        }

        function renderCemeteryMap() {
            const phases = { 'Phase 1': {}, 'Phase 2': {}, 'Phase 3': {} };
            allPlots.forEach(plot => {
                const phase = plot.phase || 'Phase 1';
                const block = plot.block;
                if (!phases[phase]) phases[phase] = {};
                if (!phases[phase][block]) phases[phase][block] = [];
                phases[phase][block].push(plot);
            });

            function findPlot(block, lot, phase) {
                const plots = phases[phase] ? phases[phase][block] : null;
                if (!plots) return null;
                return plots.find(p => parseInt(p.lot) === lot) || null;
            }

            let html = '<div class="all-blocks" id="allBlocks">';
            const aaSecPlots = allPlots.filter(p => p.block === 'AA' && p.section === currentAASection);
            if (aaSecPlots.length > 0 || currentAASection === '1') {
                html += renderBlockColumn('AA', 'Phase 3', (block, lot, phase) => {
                    return allPlots.find(p => p.block === block && parseInt(p.lot) === lot && p.section === currentAASection) || null;
                }, 20);
            }
            html += renderBlockColumn('', 'Phase 3', findPlot, 10);
            html += '<div class="phase-divider"></div>';

            const phase2Groups = [['Z','Y'], ['X','W'], ['V','U'], ['T']];
            phase2Groups.forEach((group, gi) => {
                if (gi > 0) html += '<div class="pair-gap"></div>';
                group.forEach(b => { html += renderBlockColumn(b, 'Phase 2', findPlot, 20); });
            });
            html += '<div class="phase-divider"></div>';

            const phase1Groups = [['I','H'], ['G','F'], ['E','D'], ['C','B'], ['A']];
            phase1Groups.forEach((group, gi) => {
                if (gi > 0) html += '<div class="pair-gap"></div>';
                group.forEach(b => { html += renderBlockColumn(b, 'Phase 1', findPlot, 20); });
            });
            html += '</div>';

            html += `
                <div class="phase-labels-row">
                    <div class="phase-label-cell">PHASE 3</div>
                    <div style="width:20px;"></div>
                    <div class="phase-label-cell">PHASE 2</div>
                    <div style="width:20px;"></div>
                    <div class="phase-label-cell">PHASE 1</div>
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
                const colorClass = plot ? (plot.status === 'Vacant' ? 'vacant' : 'occupied') : 'vacant';
                const displayBlock = blockName || 'Unnamed';
                const plotSection = plot ? plot.section : '';
                const deceasedNames = plot && plot.deceased_names ? String(plot.deceased_names) : '';
                const tooltipLine1 = `Block ${displayBlock}${plotSection ? ', Section ' + plotSection : ''}, Lot ${lot} (${phaseName})`;
                const tooltipLine2 = plot
                    ? (deceasedNames ? 'Buried: ' + deceasedNames : (plot.status === 'Vacant' ? 'Vacant' : 'Occupied'))
                    : 'Vacant';
                const tooltip = `${tooltipLine1} — ${tooltipLine2}`;
                html += `
                    <div class="lot-box ${colorClass}"
                         title="${tooltip}"
                         data-block="${displayBlock}"
                         data-section="${plotSection}"
                         data-lot="${lot}"
                         data-plotid="${plot ? plot.plot_id : ''}"
                         onclick="viewPlotDetails(${plot ? plot.plot_id : 'null'}, '${blockName}', ${lot}, '${phaseName}')">
                        ${lot}
                    </div>
                `;
            }
            html += '</div></div>';
            return html;
        }

        function scaleMap() {
            const outer = document.getElementById('mapContainer');
            const scaler = document.getElementById('mapScaler');
            const inner = document.getElementById('mapInner');
            if (!outer || !scaler || !inner) return;

            scaler.style.transform = 'scale(1)';

            const pad = 48;
            const scale = Math.min(
                (outer.clientWidth - pad) / Math.max(inner.scrollWidth, 1),
                (outer.clientHeight - pad) / Math.max(inner.scrollHeight, 1),
                1
            );

            scaler.style.transform = `scale(${scale})`;
        }

        async function viewPlotDetails(plotId, blockName, lotNumber, phaseName) {
            if (!plotId || plotId === 'null') {
                const displayBlock = blockName || 'Unnamed';
                document.getElementById('modalTitle').textContent = `Plot: Block ${displayBlock}, Lot ${lotNumber} (${phaseName})`;
                document.getElementById('plotModalContent').innerHTML = `
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Vacant Plot</h6>
                        <p class="mb-0">This plot is currently vacant and available for burial.</p>
                        <p class="mb-0"><small>Phase: ${phaseName} | Block: ${displayBlock} | Lot: ${lotNumber}</small></p>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('plotModal')).show();
                return;
            }
            try {
                const response = await fetch(`/api/get_public_lot_details.php?plot_id=${plotId}`);
                const data = await response.json();
                if (!data.success) throw new Error(data.message || 'Failed to load plot details');
                const plot = data.plot;
                const deceased = data.deceased_records || [];
                document.getElementById('modalTitle').textContent = `Plot: Block ${plot.block}, Section ${plot.section}, Lot ${plot.lot}`;
                let html = `
                    <div class="plot-mini-card">
                        <span class="plot-mini-label">Phase</span>
                        <div class="plot-mini-value">${plot.phase || phaseName}</div>
                    </div>
                    <div class="plot-mini-card">
                        <span class="plot-mini-label">Type</span>
                        <div class="plot-mini-value">${plot.type || 'N/A'}</div>
                    </div>
                    <div class="plot-mini-card">
                        <span class="plot-mini-label">Status</span>
                        <div class="plot-mini-value">${plot.status || 'N/A'}</div>
                    </div>
                `;
                if (deceased.length > 0) {
                    html += '<div class="plot-section-title"><h6><i class="fas fa-user me-2"></i>Deceased Records</h6></div>';
                    html += '<div class="deceased-cards">';
                    deceased.forEach(record => {
                        html += `
                            <div class="deceased-card">
                                <div class="deceased-name">${record.full_name || 'Unnamed'}</div>
                                <p class="deceased-meta">
                                    <strong>Date of Birth:</strong> ${formatDate(record.birth_date)}<br>
                                    <strong>Date of Death:</strong> ${formatDate(record.date_of_death)}
                                </p>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<p class="text-muted text-center">No burial records for this plot</p>';
                }
                document.getElementById('plotModalContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('plotModal')).show();
            } catch (error) {
                console.error('Error fetching plot details:', error);
                document.getElementById('plotModalContent').innerHTML = '<div class="alert alert-danger">Error loading plot details.</div>';
                new bootstrap.Modal(document.getElementById('plotModal')).show();
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function performSearch() {
            const term = document.getElementById('searchInput').value.trim().toLowerCase();
            const resultsEl = document.getElementById('searchResults');
            if (term.length < 2) {
                resultsEl.classList.remove('active');
                return;
            }
            const matches = allDeceasedRecords.filter(r => r.full_name.toLowerCase().includes(term)).slice(0, 15);
            if (matches.length === 0) {
                resultsEl.innerHTML = '<div class="no-results">No matching records found</div>';
                resultsEl.classList.add('active');
                return;
            }
            resultsEl.innerHTML = matches.map(r => `
                <div class="search-result-item" onclick="focusOnDeceased(${r.deceased_id})">
                    <div class="search-result-name">${escapeHtml(r.full_name)}</div>
                    <div class="search-result-location"><i class="fas fa-map-marker-alt"></i> Block ${r.block || 'N/A'}, Section ${r.section || 'N/A'}, Lot ${r.lot || 'N/A'}</div>
                    <div class="search-result-dates"><i class="fas fa-calendar"></i> Born: ${formatDate(r.birth_date)} | Died: ${formatDate(r.date_of_death)}</div>
                </div>
            `).join('');
            resultsEl.classList.add('active');
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        async function focusOnDeceased(deceasedId) {
            document.getElementById('searchResults').classList.remove('active');
            document.getElementById('searchInput').value = '';
            const record = allDeceasedRecords.find(r => r.deceased_id === deceasedId);
            if (!record) return;
            const plot = allPlots.find(p => p.plot_id === record.plot_id);
            if (!plot) return;
            viewPlotDetails(plot.plot_id, plot.block, plot.lot, plot.phase);
            const lotBox = document.querySelector(`.lot-box[data-plotid="${plot.plot_id}"]`);
            if (lotBox) {
                document.querySelectorAll('.lot-box').forEach(b => b.classList.remove('highlighted', 'blurred'));
                lotBox.classList.add('highlighted');
                setTimeout(() => lotBox.classList.remove('highlighted'), 3000);
            }
        }

        document.addEventListener('click', (e) => {
            const searchBox = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            if (searchBox && searchResults && !searchBox.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(performSearch, 200);
                });
            }
            const aaSelect = document.getElementById('aaStaffSectionSelect');
            if (aaSelect) {
                aaSelect.addEventListener('change', (e) => changeAASection(e.target.value));
            }
            window.addEventListener('resize', scaleMap);
            loadCemeteryMap();
        });
    </script>
</body>
</html>
