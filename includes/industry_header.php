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
    <title>Industry Panel - Milk Cooperative System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
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
        background: linear-gradient(135deg, #FF9800, #FFA726);
        padding: 1.2rem 0.5rem;
        box-shadow: 0 4px 24px rgba(255,152,0,0.15);
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
        box-shadow: 0 2px 8px rgba(255,152,0,0.08);
    }
    .nav-link:hover {
        background: rgba(255,255,255,0.16) !important;
        color: #fff !important;
        transform: translateY(-1px) scale(1.03);
    }
    .dropdown-menu {
        border: none;
        box-shadow: 0 8px 32px rgba(255,152,0,0.10);
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
        background: rgba(255, 152, 0, 0.10);
        color: #FF9800;
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
            background: var(--primary-color, #FF9800);
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
            <a class="navbar-brand fw-bold fs-3 py-2 px-3 rounded-3" href="/MCS/modules/industry/dashboard.php">
                <i class="fas fa-industry"></i> Industry Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-3 me-auto align-items-lg-center gap-lg-1">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="/MCS/modules/industry/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>

                    <!-- Orders Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo (strpos($current_page, 'order') !== false) ? 'active' : ''; ?>" href="#" id="ordersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="ordersDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/place_order.php">
                                    <i class="fas fa-plus-circle"></i> Make New Order
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/order_history.php">
                                    <i class="fas fa-history"></i> Order History
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Payments Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1 <?php echo (strpos($current_page, 'payment') !== false) ? 'active' : ''; ?>" href="#" id="paymentsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-money-bill-wave"></i> Payments
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="paymentsDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/make_payment.php">
                                    <i class="fas fa-plus-circle"></i> Make Payment
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/payment_history.php">
                                    <i class="fas fa-history"></i> Payment History
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Feedback -->
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1 <?php echo $current_page == 'delivery_feedback.php' ? 'active' : ''; ?>" href="/MCS/modules/industry/delivery_feedback.php">
                            <i class="fas fa-comment-alt"></i> Delivery Feedback
                        </a>
                    </li>

                    <!-- Statement -->
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1 <?php echo $current_page == 'print_statement.php' ? 'active' : ''; ?>" href="/MCS/modules/industry/print_statement.php">
                            <i class="fas fa-file-invoice"></i> Print Statement
                        </a>
                    </li>
                </ul>

                <!-- Profile Dropdown -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/profile.php">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/industry/change_password.php">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 