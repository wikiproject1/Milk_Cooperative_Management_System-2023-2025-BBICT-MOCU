<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a farmer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "farmer"){
    header("location: ../auth/login.php");
    exit;
}

require_once "../../config/db.php";

// Get the farmer's table id and info using the logged-in user's id
$user_id = $_SESSION["id"];
$farmer = [];
$farmer_code = null;
$quota = 0;
$sql = "SELECT * FROM farmers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $farmer = mysqli_fetch_assoc($result);
    $farmer_code = $farmer['farmer_id'];
    $quota = $farmer['quota'];
    mysqli_stmt_close($stmt);
}

// Get total delivered (approved only)
$total_delivered = 0;
$sql = "SELECT COALESCE(SUM(quantity),0) FROM milk_deliveries WHERE farmer_id = ? AND status = 'approved'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $farmer_code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total_delivered);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
// Get total pending deliveries
$total_pending = 0;
$sql = "SELECT COALESCE(SUM(quantity),0) FROM milk_deliveries WHERE farmer_id = ? AND status = 'pending'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $farmer_code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total_pending);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
$balance = $quota - $total_delivered - $total_pending;
// Get all deliveries
$sql = "SELECT delivery_date, shift, quantity, status FROM milk_deliveries WHERE farmer_id = ? ORDER BY delivery_date DESC";
$deliveries = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $farmer_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
    }
    mysqli_stmt_close($stmt);
}
// Get all payments
$sql = "SELECT amount, payment_date, payment_method, notes FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC";
$payments = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $farmer_code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_stmt_close($stmt);
}
// Get all sales and total profit
$total_profit = 0;
$sales = [];
if (mysqli_query($conn, "SHOW TABLES LIKE 'sales'")) {
    $sql = "SELECT quantity, price_per_liter, total_profit, sale_date FROM sales WHERE farmer_id = ? ORDER BY sale_date DESC";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $farmer_code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $sales[] = $row;
            $total_profit += $row['total_profit'];
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Statement - MCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>
<?php include "../../includes/farmer_header.php"; ?>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-print"></i> Statement</h2>
                <button class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
            <div class="mb-4">
                <h5>Farmer Information</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($farmer['full_name']); ?></li>
                    <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($farmer['phone']); ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($farmer['email']); ?></li>
                    <li class="list-group-item"><strong>Address:</strong> <?php echo htmlspecialchars($farmer['address']); ?></li>
                </ul>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="alert alert-primary text-center">
                        <div class="fw-bold">Quota</div>
                        <div style="font-size:1.5rem;"><i class="fas fa-tint"></i> <?php echo number_format($quota,2); ?> L</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success text-center">
                        <div class="fw-bold">Delivered</div>
                        <div style="font-size:1.5rem;"><i class="fas fa-truck"></i> <?php echo number_format($total_delivered,2); ?> L</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning text-center">
                        <div class="fw-bold">Pending</div>
                        <div style="font-size:1.5rem;"><i class="fas fa-hourglass-half"></i> <?php echo number_format($total_pending,2); ?> L</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-info text-center">
                        <div class="fw-bold">Balance</div>
                        <div style="font-size:1.5rem;"><i class="fas fa-balance-scale"></i> <?php echo number_format($balance,2); ?> L</div>
                    </div>
                </div>
            </div>
            <h5 class="mt-4">Delivery History</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Quantity (L)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($deliveries) > 0): ?>
                            <?php foreach ($deliveries as $d): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($d['delivery_date'])); ?></td>
                                    <td><?php echo ucfirst($d['shift']); ?></td>
                                    <td><?php echo number_format($d['quantity'],2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $d['status'] == 'approved' ? 'success' : ($d['status'] == 'pending' ? 'warning text-dark' : 'secondary'); ?>">
                                            <?php echo ucfirst($d['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No deliveries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <h5 class="mt-4">Payments Received</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Notes</th>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No payments received yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <h5 class="mt-4">Sales Summary</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Quantity Sold (L)</th>
                            <th>Price/Liter</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sales) > 0): ?>
                            <?php foreach ($sales as $s): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($s['sale_date'])); ?></td>
                                    <td><?php echo number_format($s['quantity'],2); ?></td>
                                    <td><?php echo number_format($s['price_per_liter'],2); ?></td>
                                    <td><?php echo number_format($s['total_profit'],2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No sales yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total Profit:</th>
                            <th><?php echo number_format($total_profit,2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 