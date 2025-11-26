<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an industry user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "industry"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Get the logged-in industry user's ID
$user_id = $_SESSION["id"];

// Fetch industry details based on the user_id
$sql = "SELECT * FROM industries WHERE user_id = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $industry = mysqli_fetch_assoc($result);
        } else{
            // Industry not found for this user - this shouldn't happen if registration worked correctly
            echo "Error: Industry details not found.";
            exit;
        }
    } else{
        echo "Oops! Something went wrong fetching industry details.";
        exit;
    }
    mysqli_stmt_close($stmt);
}

// Fetch statistics
$stats = [
    'total_orders' => 0,
    'pending_orders' => 0,
    'last_delivery' => null,
    'total_payments' => 0
];

// Fetch payment history for this industry
$payments = [];

// Check if tables exist before querying
$tables_exist = true;
$result = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($result) == 0) {
    $tables_exist = false;
}
$result = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
if (mysqli_num_rows($result) == 0) {
    $tables_exist = false;
}

if ($tables_exist) {
    // Total orders
    $sql = "SELECT COUNT(*) as total FROM orders WHERE industry_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['total_orders'] = mysqli_fetch_assoc($result)['total'];
        mysqli_stmt_close($stmt);
    }

    // Pending orders
    $sql = "SELECT COUNT(*) as total FROM orders WHERE industry_id = ? AND status = 'pending'";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];
        mysqli_stmt_close($stmt);
    }

    // Last delivery
    $sql = "SELECT * FROM orders WHERE industry_id = ? AND status = 'delivered' ORDER BY delivery_date DESC LIMIT 1";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result)){
            $stats['last_delivery'] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    // Total payments
    $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE industry_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['total_payments'] = mysqli_fetch_assoc($result)['total'];
        mysqli_stmt_close($stmt);
    }

    // Fetch payment history
    $sql = "SELECT * FROM payments WHERE industry_id = ? ORDER BY payment_date DESC";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Show message that tables need to be created
    echo '<div class="alert alert-warning m-3">
        <h4 class="alert-heading">Setup Required</h4>
        <p>The required database tables have not been created yet. Please run the SQL commands from <code>database/industry_tables.sql</code> to create the necessary tables.</p>
        <hr>
        <p class="mb-0">Until then, the dashboard will show placeholder data.</p>
    </div>';
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industry Dashboard - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles for industry dashboard */
        .dashboard-header {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(33, 150, 243, 0.2);
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2196F3;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #2196F3;
        }
        .stat-label {
            color: #636E72;
            font-size: 1.1rem;
        }
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(33, 150, 243, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2196F3;
        }
        .activity-details {
            flex: 1;
        }
        .activity-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #2196F3;
        }
        .activity-time {
            color: #636E72;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include "../../includes/industry_header.php"; ?>
    
    <main>
        <div class="dashboard-header">
            <div class="container">
                <h1 class="display-4">Welcome, <?php echo htmlspecialchars($industry['company_name']); ?>!</h1>
                <p class="lead">Industry Dashboard Overview</p>
            </div>
        </div>

        <div class="container">
            <!-- Overview Statistics -->
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="fas fa-truck"></i></div>
                        <div class="stat-value">
                            <?php 
                            if($stats['last_delivery']) {
                                echo date('M d', strtotime($stats['last_delivery']['delivery_date']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Last Delivery</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card text-center">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['total_payments'], 2); ?> KES</div>
                        <div class="stat-label">Total Payments</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="recent-activity">
                        <h3 class="mb-4">Quick Actions</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <a href="place_order.php" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-plus-circle me-2"></i> Make New Order
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="make_payment.php" class="btn btn-success btn-lg w-100 mb-3">
                                    <i class="fas fa-money-bill-wave me-2"></i> Make Payment
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="print_statement.php" class="btn btn-info btn-lg w-100 mb-3">
                                    <i class="fas fa-file-invoice me-2"></i> Print Statement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="container mt-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-wallet"></i> Payments Made</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Notes</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($payments) > 0): ?>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
                                                <td>TZS <?php echo number_format($p['amount'],2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($p['notes']); ?></td>
                                                <td><?php echo htmlspecialchars($p['account_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($p['account_number'] ?? ''); ?></td>
                                                <td>
                                                    <?php if(isset($p['status']) && $p['status'] == 'paid'): ?>
                                                        <span class="badge bg-success">Paid</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Unpaid</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center">No payments made yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 