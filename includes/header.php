<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Cooperative System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <!-- <link href="/MCS/assets/css/style.css" rel="stylesheet"> -->
    <style>
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box;
    }
    body {
        padding-top: 90px;
        background: var(--background-color, #F8F9FA);
    }
    .navbar {
        margin-top: 0 !important;
        background: linear-gradient(135deg, #7b6cff, #6C63FF 80%);
        padding: 1.2rem 0.5rem;
        box-shadow: 0 4px 24px rgba(108,99,255,0.10);
        border-radius: 0 0 18px 18px;
    }
    .navbar-brand {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 1px;
        background: rgba(255,255,255,0.12) !important;
        color: #fff !important;
        transition: background 0.2s;
    }
    .navbar-brand:hover {
        background: rgba(255,255,255,0.22) !important;
        color: #fff !important;
    }
    .nav-link {
        color: rgba(255,255,255,0.95) !important;
        font-size: 1.08rem;
        font-weight: 500;
        border-radius: 10px;
        padding: 0.7rem 1.1rem !important;
        margin: 0 0.2rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .nav-link.active, .nav-link:focus {
        background: rgba(255,255,255,0.22) !important;
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(108,99,255,0.08);
    }
    .nav-link:hover {
        background: rgba(255,255,255,0.16) !important;
        color: #fff !important;
        transform: translateY(-1px) scale(1.03);
    }
    .dropdown-menu {
        border: none;
        box-shadow: 0 8px 32px rgba(108,99,255,0.10);
        border-radius: 14px;
        padding: 0.5rem 0.2rem;
        min-width: 210px;
        margin-top: 0.5rem !important;
        animation-duration: 0.2s;
    }
    .dropdown-item {
        padding: 0.8rem 1.2rem;
        border-radius: 8px;
        font-size: 1.05rem;
        font-weight: 500;
        color: #444;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .dropdown-item:hover, .dropdown-item:focus {
        background: rgba(108, 99, 255, 0.10);
        color: #6C63FF;
        transform: translateX(5px) scale(1.04);
    }
    .dropdown-item i {
        width: 22px;
        text-align: center;
        margin-right: 0.7rem;
        font-size: 1.1rem;
    }
    .dropdown-divider {
        margin: 0.5rem 0;
    }
    .navbar-nav .dropdown-menu-end {
        right: 0;
        left: auto;
    }
    .navbar-nav .dropdown-toggle::after {
        margin-left: 0.4em;
    }
    .navbar-nav .dropdown-toggle[aria-expanded="true"] {
        background: rgba(255,255,255,0.18) !important;
    }
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: var(--primary-color, #6C63FF);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        .nav-link {
            margin: 0.2rem 0;
        }
        .navbar-nav {
            gap: 0 !important;
        }
    }
    </style>
    <!-- Optionally add animate.css for dropdown fadeIn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm rounded-bottom sticky-top" style="z-index: 1050;">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-3 py-2 px-3 rounded-3" href="/MCS/modules/coop/dashboard.php" style="background:rgba(255,255,255,0.12);">
                <i class="fas fa-cow"></i> MCS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-3 me-auto align-items-lg-center gap-lg-1">
                    <!-- Dashboard Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="#" id="dashboardDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="dashboardDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard Home
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Farmers Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo ($current_page == 'farmers.php' || $current_page == 'register_farmer.php') ? 'active' : ''; ?>" href="#" id="farmersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users"></i> Farmers
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="farmersDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/farmers.php">
                                    <i class="fas fa-list"></i> View All Farmers
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/register_farmer.php">
                                    <i class="fas fa-user-plus"></i> Register Farmer
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Industries Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo ($current_page == 'industries.php' || $current_page == 'register_industry.php') ? 'active' : ''; ?>" href="#" id="industriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-industry"></i> Industries
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="industriesDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/industries.php">
                                    <i class="fas fa-list"></i> View All Industries
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/register_industry.php">
                                    <i class="fas fa-building"></i> Register Industry
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Deliveries Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo ($current_page == 'record_delivery.php' || $current_page == 'view_deliveries.php' || $current_page == 'delivery_reports.php') ? 'active' : ''; ?>" href="#" id="deliveriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-truck"></i> Deliveries
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="deliveriesDropdown">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/manage_deliveries.php">
                                        <i class="fas fa-tasks"></i> Manage Deliveries
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/record_delivery.php">
                                    <i class="fas fa-plus-circle"></i> Record New Delivery
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/view_deliveries.php">
                                    <i class="fas fa-history"></i> Delivery History
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/delivery_reports.php">
                                    <i class="fas fa-chart-line"></i> Delivery Reports
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Orders Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo ($current_page == 'view_orders.php' || $current_page == 'order_status.php') ? 'active' : ''; ?>" href="#" id="ordersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="ordersDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/view_orders.php">
                                    <i class="fas fa-list"></i> View All Orders
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/order_status.php">
                                    <i class="fas fa-tasks"></i> Order Status
                                </a>
                            </li>
                        </ul>
                    </li>
                   

                    <!-- Reports Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="reportsDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/reports.php">
                                    <i class="fas fa-chart-bar"></i> Generate Reports
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <!-- User Profile Dropdown -->
                <ul class="navbar-nav ms-lg-2">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white bg-opacity-10" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <span class="fw-semibold text-capitalize"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/coop/settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 text-danger fw-bold" href="/MCS/modules/auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content should start here, no sidebar or grid structure -->
</body>
</html> 