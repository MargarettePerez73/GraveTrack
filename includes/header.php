<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>GraveTrack</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-monument"></i>
                GraveTrack
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'vacancy') ? 'active' : ''; ?>" href="vacancy.php">
                            <i class="fas fa-map-marked-alt"></i> Vacancy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'burial_records') ? 'active' : ''; ?>" href="burial_records.php">
                            <i class="fas fa-book"></i> Burial Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'cemetery_map') ? 'active' : ''; ?>" href="cemetery_map.php">
                            <i class="fas fa-map"></i> Cemetery Map
                        </a>
                    </li>
                    <li class="nav-item" id="paymentNav" style="display: none;">
                        <a class="nav-link <?php echo ($currentPage == 'payment_monitoring') ? 'active' : ''; ?>" href="payment_monitoring.php">
                            <i class="fas fa-money-bill-wave"></i> Payments
                        </a>
                    </li>
                </ul>

                <!-- Logout Button -->
                <button class="btn btn-logout ms-3" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <!-- Global Loading Spinner -->
    <div id="globalLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        // Show payment nav for Treasurer only
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (currentUser && currentUser.role === 'Treasurer') {
                    document.getElementById('paymentNav').style.display = 'block';
                }
            }, 500);
        });
    </script>
