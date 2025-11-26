<?php
$role = $_SESSION["role"] ?? '';
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if ($role === 'admin'): ?>
                <!-- Admin Navigation -->
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/register_farmer.php">
                        <i class="fas fa-user-plus"></i> Register Farmer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/register_industry.php">
                        <i class="fas fa-industry"></i> Register Industry
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/view_farmers.php">
                        <i class="fas fa-users"></i> View Farmers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/view_industries.php">
                        <i class="fas fa-building"></i> View Industries
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/coop/manage_orders.php">
                        <i class="fas fa-clipboard-list"></i> Manage Orders
                    </a>
                </li>
            <?php elseif ($role === 'farmer'): ?>
                <!-- Farmer Navigation -->
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/farmer/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/farmer/delivery_history.php">
                        <i class="fas fa-history"></i> Delivery History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/farmer/milk_balance.php">
                        <i class="fas fa-balance-scale"></i> Milk Balance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/farmer/payment_history.php">
                        <i class="fas fa-money-bill-wave"></i> Payment History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/farmer/print_statement.php">
                        <i class="fas fa-print"></i> Print Statement
                    </a>
                </li>
            <?php elseif ($role === 'industry'): ?>
                <!-- Industry Navigation -->
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/make_order.php">
                        <i class="fas fa-shopping-cart"></i> Make Order
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/order_history.php">
                        <i class="fas fa-history"></i> Order History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/payment.php">
                        <i class="fas fa-money-bill-wave"></i> Make Payment
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/feedback.php">
                        <i class="fas fa-comment"></i> Feedback
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/MCS/modules/industry/print_statement.php">
                        <i class="fas fa-print"></i> Print Statement
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 