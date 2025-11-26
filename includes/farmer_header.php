<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get farmer's name for the user profile dropdown
$farmer_name = 'User'; // Default name
if (isset($_SESSION['user_id'])) {
    require_once "../../config/db.php";
    $farmer_id = $_SESSION['user_id'];
    $sql = "SELECT full_name FROM farmers WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $farmer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $farmer_name = htmlspecialchars($row['full_name']);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
<!-- Farmer Navigation Bar -->
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
    background: linear-gradient(135deg, #7b6cff, #6C63FF 80%);
    padding: 1.2rem 0 1.2rem 0;
    box-shadow: none !important;
    border-radius: 0 !important;
}
.navbar-brand, .nav-pill, .profile-pill {
    background: rgba(255,255,255,0.13) !important;
    color: #fff !important;
    border-radius: 16px !important;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.6rem 1.3rem !important;
    margin: 0 0.3rem;
    transition: background 0.2s, color 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none !important;
    box-shadow: none !important;
}
.navbar-brand {
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 0.7rem 2.2rem !important;
    margin-right: 1.5rem;
    text-decoration: none !important;
}
.nav-pill.active, .nav-pill:focus {
    background: #fff !important;
    color: #6C63FF !important;
    box-shadow: 0 2px 8px rgba(108,99,255,0.08);
    text-decoration: none !important;
}
.nav-pill:hover {
    background: rgba(255,255,255,0.22) !important;
    color: #fff !important;
    transform: translateY(-1px) scale(1.03);
    text-decoration: none !important;
}
.profile-pill {
    background: rgba(255,255,255,0.13) !important;
    color: #fff !important;
    border-radius: 16px !important;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 0.6rem 1.3rem !important;
    margin-left: 0.5rem;
    text-decoration: none !important;
}
.profile-pill:focus, .profile-pill.active {
    background: #fff !important;
    color: #6C63FF !important;
    text-decoration: none !important;
}
.dropdown-menu {
    border: none;
    box-shadow: 0 8px 32px rgba(108,99,255,0.10);
    border-radius: 18px;
    padding: 0.2rem 0.1rem;
    min-width: 200px;
    margin-top: 0.5rem !important;
    animation-duration: 0.2s;
}
.dropdown-item {
    padding: 0.6rem 1rem;
    border-radius: 10px;
    font-size: 1.08rem;
    font-weight: 500;
    color: #444;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    text-decoration: none !important;
}
.dropdown-item:hover, .dropdown-item:focus {
    background: rgba(108, 99, 255, 0.10);
    color: #6C63FF;
    transform: translateX(5px) scale(1.04);
    text-decoration: none !important;
}
.dropdown-divider {
    margin: 0.5rem 0;
}
.dropdown-username {
    font-weight: 700;
    color: #6C63FF;
    font-size: 1.1rem;
    padding: 0.7rem 1.2rem 0.3rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
@media (max-width: 991.98px) {
    .navbar-collapse {
        background: var(--primary-color, #6C63FF);
        padding: 1rem;
        border-radius: 10px;
        margin-top: 1rem;
    }
    .nav-pill {
        margin: 0.2rem 0;
    }
    .navbar-nav {
        gap: 0 !important;
    }
}
.navbar .container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
<!-- Optionally add animate.css for dropdown fadeIn -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<!-- Navigation -->
<nav class="navbar navbar-expand-md navbar-dark shadow-sm rounded-bottom sticky-top" style="z-index: 1050;">
    <div class="container-fluid px-0">
        <a class="navbar-brand fw-bold fs-3 py-2 px-3 rounded-3" href="/MCS/modules/farmer/dashboard.php">
            <i class="fas fa-cow"></i> MCS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-3 me-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'request_delivery.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/request_delivery.php">
                        <i class="fas fa-truck"></i> Request Delivery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'delivery_history.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/delivery_history.php">
                        <i class="fas fa-history"></i> Delivery History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'milk_balance.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/milk_balance.php">
                        <i class="fas fa-calculator"></i> Milk Balance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/payments.php">
                        <i class="fas fa-wallet"></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-pill <?php echo $current_page == 'print_statement.php' ? 'active' : ''; ?>" href="/MCS/modules/farmer/print_statement.php">
                        <i class="fas fa-print"></i> Print Statement
                    </a>
                </li>
                <!-- Profile Dropdown -->
                <li class="nav-item dropdown">
                    <button class="nav-pill dropdown-toggle <?php echo ($current_page == 'profile.php' || $current_page == 'change_password.php') ? 'active' : ''; ?>" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border:none; background:none;">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <ul class="dropdown-menu animate__animated animate__fadeIn" aria-labelledby="profileDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/farmer/profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/MCS/modules/farmer/change_password.php">
                                <i class="fas fa-key"></i> Change Password
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
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Improved dropdown logic: only one open at a time, all links remain clickable
document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
    dropdown.addEventListener('click', function (e) {
        if (window.innerWidth >= 992) {
            e.preventDefault();
            // Close all other open dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                if (menu !== dropdown.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            // Toggle this dropdown
            var menu = this.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.toggle('show');
            }
        }
    });
});
// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        if (!menu.parentElement.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
});
</script> 