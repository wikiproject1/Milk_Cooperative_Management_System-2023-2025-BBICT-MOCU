<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a farmer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "farmer"){
    header("location: ../auth/login.php");
    exit;
}

require_once "../../config/db.php";

// Get the farmer's farmer_id using the logged-in user's id
$user_id = $_SESSION["id"];
$farmer_id = null;
$sql = "SELECT farmer_id FROM farmers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $farmer_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Fetch payments for this farmer
$payments = [];
if ($farmer_id) {
    $sql = "SELECT * FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Milk Cooperative System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include "../../includes/farmer_header.php"; ?>
<div class="main-content">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-wallet"></i> Payments Received</h4>
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
                                <tr><td colspan="7" class="text-center">No payments received yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 