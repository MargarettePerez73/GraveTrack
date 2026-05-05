<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GraveTrack - Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <img src="img/municipal_logo.png" alt="Municipality of Tuy" style="width: 100px; height: 100px;">
            </div>

            <!-- Header -->
            <div class="login-header">
                <h1>GraveTrack</h1>
                <p>Cemetery Vacancy & Payment Monitoring System</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)">
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="position-relative">
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <!-- Info -->
            <div class="mt-4 text-center" style="color: #64748b; font-size: 0.85rem;">
                <p class="mb-1">
                    <i class="fas fa-info-circle"></i> For Municipal Workers Only
                </p>
                <p class="mb-0">
                    <strong>Demo Credentials:</strong><br>
                    Treasurer: testdummy1 / 12345<br>
                    Engineer: engineer1 / 12345
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="globalLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="js/app.js"></script>

    <script>
        // Handle Login Form Submit
        async function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!username || !password) {
                showToast('error', 'Error', 'Please enter both username and password');
                return;
            }

            await login(username, password);
        }

        // Check if already logged in
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const result = await fetch('/api/auth.php', {
                    credentials: 'include'
                });
                const data = await result.json();

                if (data.success && data.authenticated) {
                    // Already logged in, redirect to dashboard
                    window.location.href = 'dashboard.php';
                }
            } catch (error) {
                // Not logged in, stay on login page
            }
        });
    </script>
</body>
</html>
