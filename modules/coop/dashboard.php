<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Get statistics
$stats = array();

// Total farmers
$sql = "SELECT COUNT(*) as total FROM farmers";
$result = mysqli_query($conn, $sql);
$stats['farmers'] = mysqli_fetch_assoc($result)['total'];

// Total industries
$sql = "SELECT COUNT(*) as total FROM industries";
$result = mysqli_query($conn, $sql);
$stats['industries'] = mysqli_fetch_assoc($result)['total'];

// Today's milk deliveries
$sql = "SELECT COUNT(*) as total FROM milk_deliveries WHERE DATE(delivery_date) = CURDATE()";
$result = mysqli_query($conn, $sql);
$stats['deliveries'] = mysqli_fetch_assoc($result)['total'];

// Total milk quantity today
$sql = "SELECT COALESCE(SUM(quantity), 0) as total FROM milk_deliveries WHERE DATE(delivery_date) = CURDATE()";
$result = mysqli_query($conn, $sql);
$stats['quantity'] = mysqli_fetch_assoc($result)['total'];

// Pending orders
$sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$result = mysqli_query($conn, $sql);
$stats['orders'] = mysqli_fetch_assoc($result)['total'];

// Monthly milk collection
$sql = "SELECT COALESCE(SUM(quantity), 0) as total FROM milk_deliveries 
        WHERE MONTH(delivery_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(delivery_date) = YEAR(CURRENT_DATE())";
$result = mysqli_query($conn, $sql);
$stats['monthly_collection'] = mysqli_fetch_assoc($result)['total'];

// Monthly payments
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
        WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
$result = mysqli_query($conn, $sql);
$stats['monthly_payments'] = mysqli_fetch_assoc($result)['total'];

// Average milk quality score
$sql = "SELECT COALESCE(AVG(quality_score), 0) as avg_score FROM milk_deliveries 
        WHERE MONTH(delivery_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(delivery_date) = YEAR(CURRENT_DATE())";
$result = mysqli_query($conn, $sql);
$stats['avg_quality'] = round(mysqli_fetch_assoc($result)['avg_score'], 2);

// Recent deliveries with quality scores
$sql = "SELECT md.*, f.full_name as farmer_name 
        FROM milk_deliveries md 
        JOIN farmers f ON md.farmer_id = f.id 
        ORDER BY md.delivery_date DESC LIMIT 5";
$recent_deliveries = mysqli_query($conn, $sql);

// Recent orders with status
$sql = "SELECT o.*, i.company_name as industry_name 
        FROM orders o 
        JOIN industries i ON o.industry_id = i.id 
        ORDER BY o.order_date DESC LIMIT 5";
$recent_orders = mysqli_query($conn, $sql);

// Recent payments
$sql = "SELECT p.*, f.full_name as farmer_name 
        FROM payments p 
        JOIN farmers f ON p.farmer_id = f.id 
        ORDER BY p.payment_date DESC LIMIT 5";
$recent_payments = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coop Cente Dashboard - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4CAF50;
            --accent-color: #FF6B6B;
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
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2);
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            transition: 0.5s;
        }

        .stat-card:hover::before {
            transform: translateX(100%);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .recent-activity {
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
            transition: background-color 0.3s ease;
        }

        .activity-item:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
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
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>
    
    <main>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <p class="lead">Here's what's happening in your cooperative station today.</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['farmers']); ?></div>
                    <div class="stat-label">Total Farmers</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['industries']); ?></div>
                    <div class="stat-label">Partner Industries</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-milk-can"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['monthly_collection'], 2); ?> L</div>
                    <div class="stat-label">Monthly Collection</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">TZS <?php echo number_format($stats['monthly_payments'], 2); ?></div>
                    <div class="stat-label">Monthly Payments</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Deliveries -->
            <div class="col-md-6">
                <div class="recent-activity">
                    <div class="activity-header">
                        <h2 class="activity-title">Recent Deliveries</h2>
                        <a href="deliveries.php" class="btn btn-outline-primary">View All</a>
                    </div>
                    <ul class="activity-list">
                        <?php while($delivery = mysqli_fetch_assoc($recent_deliveries)): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-milk-can"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?php echo htmlspecialchars($delivery['farmer_name']); ?> - 
                                    <?php echo number_format($delivery['quantity'], 2); ?> L
                                    <?php if(isset($delivery['quality_score'])): ?>
                                    <span class="badge bg-<?php echo $delivery['quality_score'] >= 8 ? 'success' : ($delivery['quality_score'] >= 6 ? 'warning' : 'danger'); ?>">
                                        Quality: <?php echo $delivery['quality_score']; ?>/10
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M d, Y H:i', strtotime($delivery['delivery_date'])); ?>
                                </div>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-md-6">
                <div class="recent-activity">
                    <div class="activity-header">
                        <h2 class="activity-title">Recent Orders</h2>
                        <a href="orders.php" class="btn btn-outline-primary">View All</a>
                    </div>
                    <ul class="activity-list">
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?php echo htmlspecialchars($order['industry_name']); ?> - 
                                    <?php echo number_format($order['quantity'], 2); ?> L
                                    <span class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
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
                    <a href="register_farmer.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>Register Farmer</div>
                    </a>
                    <a href="register_industry.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div>Register Industry</div>
                    </a>
                    <a href="http://localhost/MCS/modules/coop/record_delivery.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div>Record Delivery</div>
                    </a>
                    <a href="payments.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>Process Payment</div>
                    </a>
                    <a href="reports.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>View Reports</div>
                    </a>
                    <a href="quality_control.php" class="action-button">
                        <div class="action-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div>Quality Control</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </main>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->
</body>
</html> 