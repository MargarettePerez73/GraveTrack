<?php
session_start();
$pageTitle = 'Payment Monitoring';
$currentPage = 'payment_monitoring';
include 'includes/header.php';


// Check if user is Treasurer
if ($_SESSION['role'] !== 'Treasurer') {
    header('Location: dashboard.php');
    exit;
}
?>

<style>
    .payment-layout {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }

    .records-sidebar, .payment-sidebar {
        width: 280px;
        background: white;
        border-right: 3px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 4px 0 12px rgba(0,0,0,0.05);
        color: #1e3a8a;
    }

    .sidebar-header {
        padding: 20px;
        background: linear-gradient(135deg, #006eff, #00408f);

    }

    .sidebar-header h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: white;
    }

    .sidebar-header .badge {
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        margin-top: 5px;
        display: inline-block;
    }

    .sidebar-section {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        
    }

    .sidebar-section h6 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        opacity: 0.8;
    }

    .sidebar-stat {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #3b82f6;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        backdrop-filter: blur(10px);
    }

    .sidebar-stat-label {
        font-size: 10px;
        opacity: 0.9;
        margin-bottom: 3px;
    }

    .sidebar-stat-value {
        font-size: 20px;
        font-weight: 700;
    }

    .filter-group {
        margin-bottom: 12px;
        
    }

    .filter-label {
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        opacity: 0.9;
    }

    .filter-input {
        width: 100%;
        padding: 8px 10px;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 2px solid #3b82f6;
        color: black;
        border-radius: 6px;
        font-size: 12px;
    }

    .filter-input::placeholder {
        color: rgba(0, 0, 0, 0.6);
    }

    .filter-input:focus {
        outline: none;
        background: linear-gradient(135deg, #dbeafe, #0061d8);
        border: 2px solid #3b82f6;
        
    }

    .filter-input option {
        background: linear-gradient(135deg, #004aac, #bfdbfe);
        color: black;
    }

    #statusfilter{
        color: black;
    }
    .payment-main {
        flex: 1;
        overflow-y: auto;
        background: #f8fafc;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 20px;
    }

    .page-header h1 {
        font-size: 26px;
        font-weight: 800;
        color: #1e3a8a;
        margin: 0;
    }

    .payment-attention-banner {
        display: none;
        margin-bottom: 20px;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 13px;
        border: 1px solid #fcd34d;
        background: #fffbeb;
        color: #78350f;
    }

    .payment-attention-banner.active {
        display: block;
    }

    .content-card-compact {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        height: calc(100vh - 220px);
        display: flex;
        flex-direction: column;
    }

    .content-card-header {
        padding: 12px 20px;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .content-card-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .content-card-body {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
    }

    .compact-table {
        width: 100%;
        font-size: 12px;
    }

    .compact-table thead th {
        background: #f8fafc;
        color: #1e3a8a;
        font-weight: 700;
        padding: 10px 8px;
        font-size: 11px;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .compact-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
    }

    .compact-table tbody tr.payment-row-clickable {
        cursor: pointer;
    }

    .compact-table tbody tr.payment-row-clickable:hover {
        background: #eff6ff;
    }

    .compact-table tbody tr:hover:not(.payment-row-clickable) {
        background: #f8fafc;
    }

    .compact-table tbody td {
        padding: 10px 8px;
        color: #334155;
    }

    .badge-compact {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
    }

    .record-count-badge {
        background: #dbeafe;
        color: #1e3a8a;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-clear {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 8px;
        border-radius: 6px;
        width: 100%;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-clear:hover {
        background: rgba(255,255,255,0.3);
    }

    /* ── Record Payment Modal ── */
    .rp-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        animation: rpFadeIn 0.2s ease;
    }

    .rp-overlay.active {
        display: flex;
    }

    @keyframes rpFadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes rpSlideUp {
        from { opacity: 0; transform: translateY(24px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .rp-card {
        background: white;
        border-radius: 14px;
        width: 480px;
        max-width: 95vw;
        box-shadow: 0 24px 60px rgba(0, 64, 143, 0.22);
        overflow: hidden;
        animation: rpSlideUp 0.25s ease;
    }

    .rp-card-header {
        padding: 20px 24px 16px;
        background: linear-gradient(135deg, #006eff, #00408f);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .rp-card-header-left h5 {
        margin: 0 0 4px;
        font-size: 17px;
        font-weight: 700;
        color: white;
    }

    .rp-card-header-left p {
        margin: 0;
        font-size: 12px;
        color: rgba(255,255,255,0.75);
    }

    .rp-plot-badge {
        background: rgba(255,255,255,0.15);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
        display: inline-block;
    }

    .rp-close-btn {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
        flex-shrink: 0;
    }

    .rp-close-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .rp-card-body {
        padding: 22px 24px;
    }

    .rp-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .rp-form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .rp-form-group.full-width {
        grid-column: 1 / -1;
    }

    .rp-form-group label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .rp-form-group label span.required {
        color: #ef4444;
        margin-left: 2px;
    }

    .rp-input {
        padding: 9px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 7px;
        font-size: 13px;
        color: #1e293b;
        background: #f8fafc;
        transition: border-color 0.15s, background 0.15s;
        width: 100%;
        box-sizing: border-box;
    }

    .rp-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .rp-input.error {
        border-color: #ef4444;
        background: #fff5f5;
    }

    .rp-input-prefix-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .rp-input-prefix {
        position: absolute;
        left: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        pointer-events: none;
    }

    .rp-input-prefix-wrap .rp-input {
        padding-left: 24px;
    }

    .rp-error-msg {
        font-size: 10px;
        color: #ef4444;
        display: none;
    }

    .rp-error-msg.show {
        display: block;
    }

    .rp-divider {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 18px 0 16px;
    }

    .rp-card-footer {
        padding: 0 24px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .rp-btn-cancel {
        padding: 9px 20px;
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .rp-btn-cancel:hover {
        background: #e2e8f0;
    }

    .rp-btn-submit {
        padding: 9px 22px;
        background: linear-gradient(135deg, #006eff, #00408f);
        color: white;
        border: none;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: opacity 0.15s, transform 0.1s;
    }

    .rp-btn-submit:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }

    .rp-btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .rp-btn-submit .spinner {
        width: 13px;
        height: 13px;
        border: 2px solid rgba(255,255,255,0.4);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
        display: none;
    }

    .rp-btn-submit.loading .spinner {
        display: block;
    }

    .rp-btn-submit.loading .btn-text {
        display: none;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .rp-success-banner {
        display: none;
        background: #dcfce7;
        border: 1.5px solid #86efac;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 13px;
        color: #15803d;
        font-weight: 600;
        align-items: center;
        gap: 8px;
    }

    .rp-success-banner.show {
        display: flex;
    }

    /* Payment history detail modal */
    .pd-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 1060;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }

    .pd-overlay.active {
        display: flex;
    }

    .pd-card {
        background: white;
        border-radius: 14px;
        width: 720px;
        max-width: 100%;
        max-height: 90vh;
        box-shadow: 0 24px 60px rgba(0, 64, 143, 0.22);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: rpSlideUp 0.25s ease;
    }

    .pd-card-header {
        padding: 18px 22px;
        background: linear-gradient(135deg, #006eff, #00408f);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-shrink: 0;
    }

    .pd-card-header h5 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 700;
        color: white;
    }

    .pd-card-header .pd-sub {
        margin: 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.5;
    }

    .pd-body {
        padding: 16px 22px 22px;
        overflow-y: auto;
        flex: 1;
    }

    .pd-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 16px;
        font-size: 12px;
        color: #475569;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    .pd-meta strong {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 2px;
    }

    .pd-table-wrap {
        overflow-x: auto;
    }

    .pd-table {
        width: 100%;
        font-size: 12px;
        border-collapse: collapse;
    }

    .pd-table th {
        text-align: left;
        padding: 8px 10px;
        background: #f8fafc;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
    }

    .pd-table td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .pd-empty {
        text-align: center;
        padding: 24px;
        color: #94a3b8;
        font-size: 13px;
    }

    .pd-loading {
        text-align: center;
        padding: 28px;
        color: #64748b;
    }
</style>

<div class="payment-layout">
    <!-- Sidebar -->
    <div class="payment-sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-money-bill-wave"></i> Payment Monitoring</h4>
            <span class="badge">Treasurer Only</span>
        </div>

        <div class="sidebar-section">
            <h6>Statistics</h6>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Paid</div>
                <div class="sidebar-stat-value" id="sidebarPaid">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Unpaid</div>
                <div class="sidebar-stat-value" id="sidebarUnpaid">0</div>
            </div>
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Overdue</div>
                <div class="sidebar-stat-value" id="sidebarOverdue">0</div>
            </div>
        </div>

        <div class="sidebar-section">
            <h6>Filters</h6>
            <div class="filter-group">
                <label class="filter-label">Search Deceased</label>
                <input type="text" class="filter-input" id="searchInput" placeholder="Type name..." onkeyup="filterPayments()">
            </div>

            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="filter-input" id="statusFilter" onchange="filterPayments()">
                    <option value="">All Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Due Soon">Due Soon</option>
                    <option value="Grace Period">Grace Period</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Amount Range</label>
                <select class="filter-input" id="amountFilter" onchange="filterPayments()">
                    <option value="">All Amounts</option>
                    <option value="0-1000">₱0 - ₱1,000</option>
                    <option value="1000-2000">₱1,000 - ₱2,000</option>
                    <option value="2000-5000">₱2,000 - ₱5,000</option>
                    <option value="5000+">₱5,000+</option>
                </select>
            </div>

            <button class="btn-clear" onclick="clearFilters()">
                <i class="fas fa-redo"></i> Clear Filters
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="payment-main">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Payment Transactions</h1>
        </div>

        <div class="payment-attention-banner" id="paymentAttentionBanner" role="status"></div>

        <!-- Payments Table -->
        <div class="content-card-compact">
            <div class="content-card-header">
                <h5><i class="fas fa-table"></i> Payment Records</h5>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="record-count-badge" id="recordCount">0 records</span>
                </div>
            </div>
            <div class="content-card-body">
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Deceased Name</th>
                            <th>Plot Location</th>
                            <th>Date of Transaction</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px;">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2" style="color: #64748b;">Loading payments...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     RECORD PAYMENT MODAL
     Triggered by: openRecordPaymentModal(plotId, plotLabel)
     from the plots modal's "Record Payment" button.
════════════════════════════════════════════ -->
<div class="rp-overlay" id="recordPaymentOverlay" onclick="handleOverlayClick(event)">
    <div class="rp-card" role="dialog" aria-modal="true" aria-labelledby="rpModalTitle">

        <!-- Header -->
        <div class="rp-card-header">
            <div class="rp-card-header-left">
                <h5 id="rpModalTitle"><i class="fas fa-receipt"></i> Record Payment</h5>
                <p>Enter the transaction details below</p>
                <span class="rp-plot-badge" id="rpPlotLabel">Plot —</span>
            </div>
            <button class="rp-close-btn" onclick="closeRecordPaymentModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="rp-card-body">

            <!-- Success banner (shown after save) -->
            <div class="rp-success-banner" id="rpSuccessBanner">
                <i class="fas fa-check-circle"></i>
                Payment recorded successfully!
            </div>

            <div class="rp-form-grid">

                <!-- Date -->
                <div class="rp-form-group">
                    <label for="rpDate">Date <span class="required">*</span></label>
                    <input type="date" class="rp-input" id="rpDate">
                    <span class="rp-error-msg" id="rpDateErr">Date is required.</span>
                </div>

                <!-- OR Number -->
                <div class="rp-form-group">
                    <label for="rpOrNumber">OR Number <span class="required">*</span></label>
                    <input type="text" class="rp-input" id="rpOrNumber" placeholder="e.g. OR-2024-001">
                    <span class="rp-error-msg" id="rpOrNumberErr">OR Number is required.</span>
                </div>

                <!-- Paid By -->
                <div class="rp-form-group full-width">
                    <label for="rpPaidBy">Paid By <span class="required">*</span></label>
                    <input type="text" class="rp-input" id="rpPaidBy" placeholder="Full name of payer">
                    <span class="rp-error-msg" id="rpPaidByErr">Paid By is required.</span>
                </div>

                <!-- Amount -->
                <div class="rp-form-group full-width">
                    <label for="rpAmount">Amount <span class="required">*</span></label>
                    <div class="rp-input-prefix-wrap">
                        <span class="rp-input-prefix">₱</span>
                        <input type="number" class="rp-input" id="rpAmount" placeholder="0.00" min="0" step="0.01">
                    </div>
                    <span class="rp-error-msg" id="rpAmountErr">Enter a valid amount.</span>
                </div>

            </div>
        </div>

        <hr class="rp-divider" style="margin: 0 24px;">

        <!-- Footer -->
        <div class="rp-card-footer">
            <button class="rp-btn-cancel" onclick="closeRecordPaymentModal()">Cancel</button>
            <button class="rp-btn-submit" id="rpSubmitBtn" onclick="submitRecordPayment()">
                <div class="spinner"></div>
                <span class="btn-text"><i class="fas fa-save"></i> Save Payment</span>
            </button>
        </div>

    </div>
</div>

<!-- Payment history (all transactions for selected deceased) -->
<div class="pd-overlay" id="paymentDetailsOverlay" onclick="handlePaymentDetailsOverlayClick(event)">
    <div class="pd-card" role="dialog" aria-modal="true" aria-labelledby="pdModalTitle" onclick="event.stopPropagation()">
        <div class="pd-card-header">
            <div>
                <h5 id="pdModalTitle"><i class="fas fa-history"></i> Payment history</h5>
                <p class="pd-sub" id="pdDeceasedLine"></p>
                <p class="pd-sub" id="pdPlotLine"></p>
                <p class="pd-sub" style="opacity:0.75;font-size:11px;">All recorded rental payments for this deceased (oldest first).</p>
            </div>
            <button class="rp-close-btn" type="button" onclick="closePaymentDetailsModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="pd-body">
            <div class="pd-meta" id="pdMeta">
                <div><strong>Contact person</strong><span id="pdContactPerson">—</span></div>
                <div><strong>Contact number</strong><span id="pdContactNumber">—</span></div>
            </div>
            <div id="pdContent" class="pd-loading">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 mb-0">Loading transactions…</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    let allPayments = [];
    let rpCurrentPlotId = null;
    let didAutoOpenDetails = false;

    function escapeHtml(str) {
        if (str == null || str === '') return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ─── Payment Table Logic ───────────────────────────────────────────────────

    async function loadPaymentData() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const plotId = urlParams.get('plot_id');
            const block = urlParams.get('block');
            const section = urlParams.get('section');
            const lot = urlParams.get('lot');
            const overdueFilter = urlParams.get('overdue') === 'true';
            const deceasedIdParam = urlParams.get('deceased_id');

            const response = await fetch('/api/get_payment_summary.php', { credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                allPayments = data.data.map(payment => ({
                    ...payment,
                    Status: normalizeRowStatus(
                        normalizePaymentStatus(payment.Status),
                        payment
                    )
                }));

                let filteredPayments = allPayments;
                if (overdueFilter) {
                    filteredPayments = filteredPayments.filter(p => p.Status === 'Overdue');
                }
                if (plotId || block || section || lot) {
                    const plotMatch = `${block || ''} - ${section || ''} - ${lot || ''}`.trim();
                    if (plotMatch) {
                        filteredPayments = filteredPayments.filter(p =>
                            p['Plot Location'].includes(plotMatch)
                        );
                    }
                }

                displayPayments(filteredPayments);
                updateStats(filteredPayments);
                bindPaymentRowClicks();
                autoOpenPaymentDetailsFromUrl(filteredPayments, deceasedIdParam, { plotId, block, section, lot, overdueFilter });

                if (overdueFilter || plotId || block || section || lot) {
                    const filterInfo = document.querySelector('.content-card-header h5');
                    if (filterInfo) {
                        filterInfo.innerHTML = `<i class="fas fa-filter text-warning"></i> ${overdueFilter ? 'Overdue ' : ''}${plotId ? 'Plot ' + plotId : ''}${block ? 'Block ' + block : ''} Payments`;
                    }
                }
            }
        } catch (error) {
            console.error('Error loading payment data:', error);
            document.getElementById('paymentsTableBody').innerHTML =
                '<tr><td colspan="8" class="text-center text-danger">Error loading payments</td></tr>';
        }
    }

    function autoOpenPaymentDetailsFromUrl(filteredPayments, deceasedIdParam, filters) {
        if (didAutoOpenDetails) return;

        // If an explicit deceased_id is provided, open directly.
        if (deceasedIdParam) {
            const id = parseInt(deceasedIdParam, 10);
            if (Number.isFinite(id) && id > 0) {
                didAutoOpenDetails = true;
                openPaymentDetailsModal(id);
                return;
            }
        }

        // If we came in with plot filters and it results in exactly one row, open it automatically.
        const cameFromPlotFilter = Boolean(filters && (filters.plotId || filters.block || filters.section || filters.lot));
        if (!cameFromPlotFilter) return;

        if (Array.isArray(filteredPayments) && filteredPayments.length === 1) {
            const only = filteredPayments[0];
            const rawId = only.deceased_id ?? only['deceased_id'];
            const id = parseInt(rawId, 10);
            if (Number.isFinite(id) && id > 0) {
                didAutoOpenDetails = true;
                openPaymentDetailsModal(id);
            }
        }
    }

    function updateAttentionBanner(payments) {
        const graceCount = payments.filter(p => p.Status === 'Grace Period').length;
        const overdueCount = payments.filter(p => isOverduePayment(p)).length;
        const el = document.getElementById('paymentAttentionBanner');

        if (graceCount === 0 && overdueCount === 0) {
            el.classList.remove('active');
            el.innerHTML = '';
            return;
        }

        el.innerHTML = `
            <strong><i class="fas fa-exclamation-triangle"></i> Payment warnings</strong>
            <p class="mb-0 mt-2"><strong>Grace period</strong> (still within 2 days after rental end, no penalty yet): <strong>${graceCount}</strong>
            &nbsp;·&nbsp; <strong>Overdue</strong> (past grace, penalties may apply): <strong>${overdueCount}</strong></p>`;
        el.classList.add('active');
    }

    function updateStats(payments) {
        const paid    = payments.filter(p => p.Status === 'Paid').length;
        const unpaid  = payments.filter(p => p.Status === 'Unpaid').length;
        const overdue = payments.filter(p => isOverduePayment(p)).length;

        document.getElementById('sidebarPaid').textContent    = paid;
        document.getElementById('sidebarUnpaid').textContent  = unpaid;
        document.getElementById('sidebarOverdue').textContent = overdue;

        // Warning should reflect the full payment dataset, not just current filters.
        updateAttentionBanner(allPayments);
    }

    function displayPayments(payments) {
        const tbody = document.getElementById('paymentsTableBody');
        tbody.innerHTML = '';

        if (payments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 30px; color: #94a3b8;">No payment records found</td></tr>';
            document.getElementById('recordCount').textContent = '0 records';
            return;
        }

        payments.forEach(payment => {
            const rawId = payment.deceased_id ?? payment['deceased_id'];
            const deceasedIdNum = parseInt(rawId, 10);
            const clickable = Number.isFinite(deceasedIdNum) && deceasedIdNum > 0;
            const rowClass = clickable ? 'payment-row-clickable' : '';
            const dataAttr = clickable ? `data-deceased-id="${deceasedIdNum}"` : '';
            const row = `
                <tr class="${rowClass}" ${dataAttr} ${clickable ? 'title="View all payments for this deceased" role="button" tabindex="0"' : ''}>
                    <td><strong>${escapeHtml(payment.transaction_id)}</strong></td>
                    <td><strong>${escapeHtml(payment['Deceased Name'])}</strong></td>
                    <td>${escapeHtml(payment['Plot Location'])}</td>
                    <td>${payment['Date of Transaction'] ? escapeHtml(formatDate(payment['Date of Transaction'])) : 'N/A'}</td>
                    <td>${escapeHtml(payment['Contact Person']) || 'N/A'}</td>
                    <td>${escapeHtml(payment['Contact Number']) || 'N/A'}</td>
                    <td>
                        <strong>${escapeHtml(formatCurrency(payment['Amount']))}</strong>
                        ${(() => {
                            const due = payment['Amount Due'];
                            const n = typeof due === 'number' ? due : parseFloat(String(due ?? '').replace(/,/g, ''));
                            if (!Number.isFinite(n) || n <= 0) return '';
                            return `<div class="text-muted mt-1" style="font-size:10px;">Balance: ${escapeHtml(formatCurrency(n))}</div>`;
                        })()}
                    </td>
                    <td>${getStatusBadge(payment.Status)}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('recordCount').textContent = `${payments.length} records`;
    }

    function filterPayments() {
        const urlParams     = new URLSearchParams(window.location.search);
        const plotId        = urlParams.get('plot_id');
        const block         = urlParams.get('block');
        const section       = urlParams.get('section');
        const lot           = urlParams.get('lot');
        const overdueFilter = urlParams.get('overdue') === 'true';

        const searchTerm  = document.getElementById('searchInput').value.toLowerCase();
        const status      = document.getElementById('statusFilter').value;
        const amountRange = document.getElementById('amountFilter').value;

        let filtered = allPayments;

        if (overdueFilter) {
            filtered = filtered.filter(p => isOverduePayment(p));
        }
        if (plotId || block || section || lot) {
            const plotMatch = `${block || ''} - ${section || ''} - ${lot || ''}`.trim();
            if (plotMatch) {
                filtered = filtered.filter(p => p['Plot Location'].includes(plotMatch));
            }
        }

        filtered = filtered.filter(payment => {
            const matchesSearch = payment['Deceased Name'].toLowerCase().includes(searchTerm);
            const matchesStatus = !status || (status === 'Overdue' ? isOverduePayment(payment) : payment.Status === status);

            let matchesAmount = true;
            if (amountRange) {
                const amount = parseFloat(payment['Amount'] || 0);
                if (amountRange === '0-1000')      matchesAmount = amount <= 1000;
                else if (amountRange === '1000-2000') matchesAmount = amount > 1000 && amount <= 2000;
                else if (amountRange === '2000-5000') matchesAmount = amount > 2000 && amount <= 5000;
                else if (amountRange === '5000+')  matchesAmount = amount > 5000;
            }

            return matchesSearch && matchesStatus && matchesAmount;
        });

        displayPayments(filtered);
        updateStats(filtered);
    }

    function clearFilters() {
        document.getElementById('searchInput').value  = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('amountFilter').value = '';

        const urlParams     = new URLSearchParams(window.location.search);
        const overdueFilter = urlParams.get('overdue') === 'true';

        let filtered = allPayments;
        if (overdueFilter) {
            filtered = filtered.filter(p => isOverduePayment(p));
        }

        displayPayments(filtered);
        updateStats(filtered);

        const filterInfo = document.querySelector('.content-card-header h5');
        if (filterInfo) {
            filterInfo.innerHTML = '<i class="fas fa-table"></i> Payment Records';
        }
    }

    function normalizePaymentStatus(status) {
        if (status === 'Overdue - Partial') return 'Overdue';
        return status;
    }

    function getAmountDueNumber(row) {
        const raw = row['Amount Due'];
        const n = typeof raw === 'number' ? raw : parseFloat(String(raw ?? '').replace(/,/g, ''));
        return Number.isFinite(n) ? n : null;
    }

    function isOverduePayment(row) {
        const due = getAmountDueNumber(row);
        if (due == null || due <= 0) return false;
        const end = row.rental_end || row['rental_end'];
        if (!end) return false;
        const endDate = new Date(end);
        if (Number.isNaN(endDate.getTime())) return false;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        endDate.setHours(0, 0, 0, 0);
        return endDate < today;
    }

    /** For table display: balances due so fully settled rentals read as Paid. */
    function normalizeRowStatus(status, row) {
        const base = normalizePaymentStatus(status);
        if (isOverduePayment(row)) {
            return 'Overdue';
        }
        const dueRaw = row['Amount Due'];
        let dueNum = typeof dueRaw === 'number' ? dueRaw : parseFloat(String(dueRaw ?? '').replace(/,/g, ''));
        if (!Number.isFinite(dueNum)) dueNum = null;
        if (dueNum != null && dueNum <= 0 && base !== 'Vacant') {
            return 'Paid';
        }
        return base;
    }

    function bindPaymentRowClicks() {
        const tbody = document.getElementById('paymentsTableBody');
        if (!tbody || tbody.dataset.bound === '1') return;
        tbody.dataset.bound = '1';
        tbody.addEventListener('click', function (e) {
            const row = e.target.closest('tr[data-deceased-id]');
            if (!row) return;
            const id = parseInt(row.getAttribute('data-deceased-id'), 10);
            if (Number.isFinite(id) && id > 0) openPaymentDetailsModal(id);
        });
        tbody.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const row = e.target.closest('tr[data-deceased-id]');
            if (!row) return;
            e.preventDefault();
            const id = parseInt(row.getAttribute('data-deceased-id'), 10);
            if (Number.isFinite(id) && id > 0) openPaymentDetailsModal(id);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindPaymentRowClicks();
        setTimeout(() => { loadPaymentData(); }, 500);
        // Set today's date as default for the record payment modal
        document.getElementById('rpDate').valueAsDate = new Date();
    });

    // ─── Payment history modal (all transactions per deceased) ─────────────────

    function setPaymentDetailsLoading() {
        const el = document.getElementById('pdContent');
        el.className = 'pd-loading';
        el.innerHTML = '<div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0">Loading transactions…</p>';
    }

    function closePaymentDetailsModal() {
        document.getElementById('paymentDetailsOverlay').classList.remove('active');
    }

    function handlePaymentDetailsOverlayClick(e) {
        if (e.target === document.getElementById('paymentDetailsOverlay')) {
            closePaymentDetailsModal();
        }
    }

    function renderPaymentHistoryRows(transactions) {
        if (!transactions.length) {
            return '<div class="pd-empty">No recorded payments yet for this deceased.</div>';
        }
        let rows = '';
        transactions.forEach((t, idx) => {
            const start = t.rental_start ? formatDate(t.rental_start) : '—';
            const end = t.rental_end ? formatDate(t.rental_end) : '—';
            const datePaid = t.payment_date ? formatDate(t.payment_date) : '—';
            const amt = formatCurrency(t.amount);
            const balRaw = t.balance_remaining;
            const balNum = typeof balRaw === 'number' ? balRaw : parseFloat(String(balRaw ?? ''));
            const balanceStr = Number.isFinite(balNum)
                ? formatCurrency(balNum)
                : '—';
            const dispStatus = t.display_status || t.status || 'Pending';
            const statusHtml = getStatusBadge(dispStatus);
            rows += `<tr>
                <td>${idx + 1}</td>
                <td>${escapeHtml(String(t.payment_id))}</td>
                <td>${escapeHtml(datePaid)}</td>
                <td><strong>${escapeHtml(amt)}</strong></td>
                <td>${escapeHtml(balanceStr)}</td>
                <td>${statusHtml}</td>
                <td>${escapeHtml(start)} – ${escapeHtml(end)}</td>
                <td>${escapeHtml(String(t.rental_id))}</td>
            </tr>`;
        });
        return `<div class="pd-table-wrap"><table class="pd-table">
            <thead><tr>
                <th>#</th>
                <th>Payment ID</th>
                <th>Date paid</th>
                <th>Amount</th>
                <th>Balance left</th>
                <th>Status</th>
                <th>Rental period</th>
                <th>Rental ID</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
    }

    async function openPaymentDetailsModal(deceasedId) {
        const overlay = document.getElementById('paymentDetailsOverlay');
        overlay.classList.add('active');
        document.getElementById('pdDeceasedLine').textContent = '';
        document.getElementById('pdPlotLine').textContent = '';
        document.getElementById('pdContactPerson').textContent = '—';
        document.getElementById('pdContactNumber').textContent = '—';
        setPaymentDetailsLoading();

        try {
            const res = await fetch(`/api/get_deceased_payment_history.php?deceased_id=${encodeURIComponent(deceasedId)}`, { credentials: 'include' });
            const data = await res.json();

            if (!data.success) {
                document.getElementById('pdContent').className = 'pd-empty';
                document.getElementById('pdContent').textContent = data.message || 'Could not load payment history.';
                return;
            }

            document.getElementById('pdDeceasedLine').textContent = data.deceased_name || '—';
            document.getElementById('pdPlotLine').textContent = data.plot_location ? `Plot: ${data.plot_location}` : '';
            document.getElementById('pdContactPerson').textContent = data.contact_person || '—';
            document.getElementById('pdContactNumber').textContent = data.contact_number || '—';

            const content = document.getElementById('pdContent');
            content.className = '';
            content.innerHTML = renderPaymentHistoryRows(data.transactions || []);
        } catch (err) {
            console.error(err);
            document.getElementById('pdContent').className = 'pd-empty';
            document.getElementById('pdContent').textContent = 'Error loading payment history.';
        }
    }

    // ─── Record Payment Modal Logic ────────────────────────────────────────────

    /**
     * Call this from the plots modal's "Record Payment" button:
     *   onclick="openRecordPaymentModal(plotId, 'Block A - Sec 2 - Lot 5')"
     *
     * @param {number|string} plotId    – the plot's primary key
     * @param {string}        plotLabel – human-readable plot identifier shown in the badge
     */
    function openRecordPaymentModal(plotId, plotLabel) {
        rpCurrentPlotId = plotId;

        // Reset form
        resetRecordPaymentForm();

        // Update header badge
        document.getElementById('rpPlotLabel').textContent = plotLabel || `Plot ${plotId}`;

        // Show overlay
        document.getElementById('recordPaymentOverlay').classList.add('active');
        document.getElementById('rpDate').focus();
    }

    function closeRecordPaymentModal() {
        document.getElementById('recordPaymentOverlay').classList.remove('active');
        rpCurrentPlotId = null;
    }

    // Close when clicking the dark backdrop (not the card itself)
    function handleOverlayClick(e) {
        if (e.target === document.getElementById('recordPaymentOverlay')) {
            closeRecordPaymentModal();
        }
    }

    function resetRecordPaymentForm() {
        const fields = ['rpDate', 'rpOrNumber', 'rpPaidBy', 'rpAmount'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            el.value = '';
            el.classList.remove('error');
        });
        ['rpDateErr', 'rpOrNumberErr', 'rpPaidByErr', 'rpAmountErr'].forEach(id => {
            document.getElementById(id).classList.remove('show');
        });
        document.getElementById('rpSuccessBanner').classList.remove('show');
        const btn = document.getElementById('rpSubmitBtn');
        btn.classList.remove('loading');
        btn.disabled = false;

        // Default date to today
        document.getElementById('rpDate').valueAsDate = new Date();
    }

    function validateRecordPaymentForm() {
        let valid = true;

        const date     = document.getElementById('rpDate').value.trim();
        const orNumber = document.getElementById('rpOrNumber').value.trim();
        const paidBy   = document.getElementById('rpPaidBy').value.trim();
        const amount   = document.getElementById('rpAmount').value;

        if (!date) {
            showRpError('rpDate', 'rpDateErr', 'Date is required.');
            valid = false;
        } else {
            clearRpError('rpDate', 'rpDateErr');
        }

        if (!orNumber) {
            showRpError('rpOrNumber', 'rpOrNumberErr', 'OR Number is required.');
            valid = false;
        } else {
            clearRpError('rpOrNumber', 'rpOrNumberErr');
        }

        if (!paidBy) {
            showRpError('rpPaidBy', 'rpPaidByErr', 'Paid By is required.');
            valid = false;
        } else {
            clearRpError('rpPaidBy', 'rpPaidByErr');
        }

        if (!amount || parseFloat(amount) <= 0) {
            showRpError('rpAmount', 'rpAmountErr', 'Enter a valid amount greater than 0.');
            valid = false;
        } else {
            clearRpError('rpAmount', 'rpAmountErr');
        }

        return valid;
    }

    function showRpError(inputId, errId, msg) {
        document.getElementById(inputId).classList.add('error');
        const errEl = document.getElementById(errId);
        errEl.textContent = msg;
        errEl.classList.add('show');
    }

    function clearRpError(inputId, errId) {
        document.getElementById(inputId).classList.remove('error');
        document.getElementById(errId).classList.remove('show');
    }

    async function submitRecordPayment() {
        if (!validateRecordPaymentForm()) return;

        const btn = document.getElementById('rpSubmitBtn');
        btn.classList.add('loading');
        btn.disabled = true;

        const payload = {
            plot_id:   rpCurrentPlotId,
            date:      document.getElementById('rpDate').value,
            or_number: document.getElementById('rpOrNumber').value.trim(),
            paid_by:   document.getElementById('rpPaidBy').value.trim(),
            amount:    parseFloat(document.getElementById('rpAmount').value)
        };

        try {
            const response = await fetch('/api/record_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('rpSuccessBanner').classList.add('show');
                btn.classList.remove('loading');
                btn.disabled = false;

                // Reload the payment table in the background
                loadPaymentData();

                // Auto-close after a short delay so user can see the success banner
                setTimeout(() => { closeRecordPaymentModal(); }, 1600);
            } else {
                btn.classList.remove('loading');
                btn.disabled = false;
                showToast('error', 'Error', result.message || 'Failed to record payment. Please try again.');
            }
        } catch (err) {
            console.error('Record payment error:', err);
            btn.classList.remove('loading');
            btn.disabled = false;
            showToast('error', 'Network Error', 'Could not connect. Please try again.');
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (document.getElementById('paymentDetailsOverlay').classList.contains('active')) {
            closePaymentDetailsModal();
        } else {
            closeRecordPaymentModal();
        }
    });
</script>
