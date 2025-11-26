<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a cooperative user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

require_once "../../config/db.php";

// Fetch summary statistics
$orders_count = 0;
$deliveries_count = 0;
$delivered_liters = 0;

$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders");
if($row = mysqli_fetch_assoc($res)) $orders_count = $row['cnt'];
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt, COALESCE(SUM(quantity),0) as total_liters FROM milk_deliveries");
if($row = mysqli_fetch_assoc($res)) {
    $deliveries_count = $row['cnt'];
    $delivered_liters = $row['total_liters'];
}

// Fetch recent orders
$recent_orders = mysqli_query($conn, "SELECT o.*, i.company_name FROM orders o JOIN industries i ON o.industry_id = i.id ORDER BY o.order_date DESC LIMIT 10");
// Fetch recent deliveries
$recent_deliveries = mysqli_query($conn, "SELECT d.*, f.full_name FROM milk_deliveries d JOIN farmers f ON d.farmer_id = f.id ORDER BY d.delivery_date DESC LIMIT 10");

include "../../includes/header.php";
?>
<div class="container py-4">
    <h1 class="mb-4">Cooperative Reports</h1>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="display-6"><?php echo $orders_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Deliveries</h5>
                    <p class="display-6"><?php echo $deliveries_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Liters Delivered</h5>
                    <p class="display-6"><?php echo number_format($delivered_liters,2); ?> L</p>
                </div>
            </div>
        </div>
    </div>
    <h3>Recent Orders</h3>
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Industry</th>
                    <th>Order Date</th>
                    <th>Required Date</th>
                    <th>Quantity (L)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['required_date']; ?></td>
                    <td><?php echo $order['quantity_liters']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <h3>Recent Deliveries</h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Delivery ID</th>
                    <th>Farmer</th>
                    <th>Delivery Date</th>
                    <th>Quantity (L)</th>
                    <th>Quality Score</th>
                </tr>
            </thead>
            <tbody>
                <?php while($delivery = mysqli_fetch_assoc($recent_deliveries)): ?>
                <tr>
                    <td><?php echo $delivery['id']; ?></td>
                    <td><?php echo htmlspecialchars($delivery['full_name']); ?></td>
                    <td><?php echo $delivery['delivery_date']; ?></td>
                    <td><?php echo $delivery['quantity']; ?></td>
                    <td><?php echo $delivery['quality_score']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "../../includes/footer.php"; ?> 