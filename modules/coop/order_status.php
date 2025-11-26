<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a cooperative user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Fetch order statistics
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_orders,
    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders
FROM orders";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch recent orders with status changes
$recent_sql = "SELECT o.*, i.company_name 
               FROM orders o 
               JOIN industries i ON o.industry_id = i.id 
               ORDER BY o.order_date DESC 
               LIMIT 10";
$recent_result = mysqli_query($conn, $recent_sql);

// Handle Approve/Reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"], $_POST["action"])) {
    $order_id = intval($_POST["order_id"]);
    $action = $_POST["action"] === "approve" ? "Approved" : "Cancelled";
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $action, $order_id);
    mysqli_stmt_execute($stmt);
    // Optionally, add a success message or redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            padding-top: 80px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2);
        }

        .stats-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .status-pending { color: #F57F17; }
        .status-approved { color: #2E7D32; }
        .status-delivered { color: #1565C0; }
        .status-cancelled { color: #C62828; }

        .recent-orders {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .status-badge {
            padding: 0.5em 1em;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.85em;
        }

        .status-pending-bg {
            background-color: #FFE082;
            color: #F57F17;
        }

        .status-approved-bg {
            background-color: #A5D6A7;
            color: #2E7D32;
        }

        .status-delivered-bg {
            background-color: #90CAF9;
            color: #1565C0;
        }

        .status-cancelled-bg {
            background-color: #EF9A9A;
            color: #C62828;
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Order Status</h1>
            <p class="lead">Monitor and track order status</p>
        </div>
    </div>

    <main class="container py-4">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stats-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon status-pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['pending_orders']; ?></div>
                    <div class="stats-label">Pending Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon status-approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['approved_orders']; ?></div>
                    <div class="stats-label">Approved Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon status-delivered">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stats-number"><?php echo $stats['delivered_orders']; ?></div>
                    <div class="stats-label">Delivered Orders</div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h3 class="mb-4">Recent Order Updates</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Industry</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($recent_result) > 0) {
                            while($row = mysqli_fetch_assoc($recent_result)) {
                                $status_class = 'status-' . strtolower($row['status']) . '-bg';
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . $row["company_name"] . "</td>";
                                echo "<td>" . $row["quantity"] . " L</td>";
                                echo "<td><span class='status-badge " . $status_class . "'>" . $row["status"] . "</span></td>";
                                echo "<td>" . date('M d, Y H:i', strtotime($row["order_date"])) . "</td>";
                                echo "<td>";
                                if (strtolower($row["status"]) == "pending") {
                                    echo "<form method='post' style='display:inline;'>
                                            <input type='hidden' name='order_id' value='" . $row["id"] . "'>
                                            <button type='submit' name='action' value='approve' class='btn btn-success btn-sm'>Approve</button>
                                          </form> ";
                                    echo "<form method='post' style='display:inline; margin-left:5px;'>
                                            <input type='hidden' name='order_id' value='" . $row["id"] . "'>
                                            <button type='submit' name='action' value='reject' class='btn btn-danger btn-sm'>Reject</button>
                                          </form>";
                                } else {
                                    echo "<span class='text-muted'>No actions</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No recent orders found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include "../../includes/footer.php"; ?>
</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?> 