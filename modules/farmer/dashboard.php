<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a farmer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "farmer"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Get farmer's information
$farmer_id = $_SESSION["id"];
$sql = "SELECT * FROM farmers WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$farmer = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Get recent milk deliveries
$sql = "SELECT * FROM milk_deliveries WHERE farmer_id = ? ORDER BY delivery_date DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$recent_deliveries = mysqli_stmt_get_result($stmt);

// Get total milk delivered this month
$sql = "SELECT COALESCE(SUM(quantity), 0) as total FROM milk_deliveries 
        WHERE farmer_id = ? AND MONTH(delivery_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(delivery_date) = YEAR(CURRENT_DATE())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$monthly_total = mysqli_stmt_get_result($stmt)->fetch_assoc()['total'];

// Get total earnings this month
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
        WHERE farmer_id = ? AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$monthly_earnings = mysqli_stmt_get_result($stmt)->fetch_assoc()['total'];

// Get recent payments
$sql = "SELECT * FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$recent_payments = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --accent-color: #FF9800;
            --background-color: #F8F9FA;
            --card-bg: #FFFFFF;
            --text-primary: #2D3436;
            --text-secondary: #636E72;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #66BB6A);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.2);
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #7B61FF;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #7B61FF;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .activity-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .activity-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-primary);
        }

        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: var(--primary-color);
            color: white;
        }

        .activity-content {
            flex: 1;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-button {
            padding: 1rem;
            border-radius: 10px;
            background: var(--card-bg);
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
            color: var(--text-primary);
            text-decoration: none;
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            color: var(--primary-color);
        }

        .action-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #7B61FF;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <?php include "../../includes/farmer_header.php"; ?>
    
    <main>
        <div class="main-content">
            <div class="dashboard-header">
                <div class="container">
                    <h1 class="display-4">Welcome, <?php echo isset($farmer['full_name']) ? htmlspecialchars($farmer['full_name']) : 'Farmer'; ?>!</h1>
                    <p class="lead">Here's your farming dashboard overview.</p>
                </div>
            </div>

            <div class="container">
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-milk-can"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($monthly_total, 2); ?> L</div>
                            <div class="stat-label">Milk Delivered This Month</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-value">TSh <?php echo number_format($monthly_earnings, 2); ?></div>
                            <div class="stat-label">Earnings This Month</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-value"><?php echo mysqli_num_rows($recent_deliveries); ?></div>
                            <div class="stat-label">Recent Deliveries</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-value"><?php echo mysqli_num_rows($recent_payments); ?></div>
                            <div class="stat-label">Recent Payments</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <!-- Recent Deliveries -->
                    <div class="col-md-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h2 class="activity-title">Recent Deliveries</h2>
                                <a href="delivery_history.php" class="btn btn-outline-primary">View All</a>
                            </div>
                            <ul class="activity-list">
                                <?php while($delivery = mysqli_fetch_assoc($recent_deliveries)): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-milk-can"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?php echo number_format($delivery['quantity'], 2); ?> L delivered
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('M d, Y', strtotime($delivery['delivery_date'])); ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Payments -->
                    <div class="col-md-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h2 class="activity-title">Recent Payments</h2>
                                <a href="payments.php" class="btn btn-outline-primary">View All</a>
                            </div>
                            <ul class="activity-list">
                                <?php while($payment = mysqli_fetch_assoc($recent_payments)): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            Ksh <?php echo number_format($payment['amount'], 2); ?> received
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <h2 class="mb-4">Quick Actions</h2>
                        <div class="quick-actions">
                            <a href="request_delivery.php" class="action-button">
                                <div class="action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>New Delivery</div>
                            </a>
                            <a href="profile.php" class="action-button">
                                <div class="action-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>Update Profile</div>
                            </a>
                            <a href="delivery_history.php" class="action-button">
                                <div class="action-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>Delivery History</div>
                            </a>
                            <a href="payment_history.php" class="action-button">
                                <div class="action-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>Payment History</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 