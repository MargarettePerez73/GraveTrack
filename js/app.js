/**
 * GraveTrack Cemetery Management System
 * Main JavaScript File - AJAX API Calls & Common Functions
 */

// API Base URL Configuration
const API_BASE_URL = '/api/';

// Current User Session
let currentUser = null;

/**
 * Initialize Application
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in (for protected pages)
    if (!window.location.pathname.includes('login.php') && !window.location.pathname.includes('index.php')) {
        checkSession();
    }
});

/**
 * API Call Helper Function
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include'
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        showLoader();
        const response = await fetch(API_BASE_URL + endpoint, options);
        const result = await response.json();
        hideLoader();

        if (!response.ok) {
            throw new Error(result.message || 'API request failed');
        }

        return result;
    } catch (error) {
        hideLoader();
        console.error('API Error:', error);
        showToast('error', 'Error', error.message || 'Something went wrong');
        throw error;
    }
}

async function login(username, password) {
    try {
        const result = await apiCall('auth.php', 'POST', { username, password });
        if (result.success) {
            showToast('success', 'Welcome!', `Logged in as ${result.user.role}`);
            currentUser = result.user;
            setTimeout(() => { window.location.href = 'dashboard.php'; }, 1000);
        }
        return result;
    } catch (error) {
        showToast('error', 'Login Failed', error.message);
        return { success: false };
    }
}

async function checkSession() {
    try {
        const result = await apiCall('auth.php', 'GET');
        if (result.success && result.authenticated) {
            currentUser = result.user;
            updateUserInfo();
        } else {
            window.location.href = 'login.php';
        }
        return result;
    } catch (error) {
        window.location.href = 'login.php';
        return { success: false };
    }
}

async function logout() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1e3a8a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, logout'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await apiCall('auth.php', 'DELETE');
                showToast('success', 'Logged Out', 'See you soon!');
                setTimeout(() => { window.location.href = 'login.php'; }, 1000);
            } catch (error) {
                showToast('error', 'Error', 'Failed to logout');
            }
        }
    });
}

function updateUserInfo() {
    const userInfoElement = document.getElementById('userInfo');
    const userRoleElement = document.getElementById('userRole');
    if (currentUser && userInfoElement) userInfoElement.textContent = currentUser.username;
    if (currentUser && userRoleElement) userRoleElement.textContent = currentUser.role;
}

function showToast(icon, title, text) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    Toast.fire({ icon, title, text });
}

function showLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) loader.style.display = 'flex';
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) loader.style.display = 'none';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getStatusBadge(status) {
    const badges = {
        'Vacant': '<span class="badge badge-vacant">Vacant</span>',
        'Occupied': '<span class="badge badge-occupied">Occupied</span>',
        'Reserved': '<span class="badge badge-reserved">Reserved</span>',
        'Paid': '<span class="badge badge-paid">Paid</span>',
        'Paid (was overdue)': '<span class="badge badge-paid" title="Fully paid after being overdue">Paid (was overdue)</span>',
        'Unpaid': '<span class="badge badge-unpaid">Unpaid</span>',
        'Pending': '<span class="badge badge-pending">Pending</span>',
        'Overdue': '<span class="badge badge-overdue">Overdue</span>'
    };
    return badges[status] || `<span class="badge">${status}</span>`;
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
