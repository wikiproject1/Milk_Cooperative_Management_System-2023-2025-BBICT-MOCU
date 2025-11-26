<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "industry"){
    header("location: ../auth/login.php");
    exit;
}
require_once "../../config/db.php";
$user_id = $_SESSION["id"];
$industry = null;
$sql = "SELECT * FROM industries WHERE user_id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($result) == 1){
        $industry = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}
$orders = [];
if ($industry) {
    $sql = "SELECT * FROM orders WHERE industry_id = ? ORDER BY order_date DESC";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $industry['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
include "../../includes/industry_header.php";
?>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-history"></i> Order History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order Date</th>
                            <th>Required Delivery Date</th>
                            <th>Quantity (Liters)</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($o['delivery_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($o['quantity']); ?></td>
                                    <td>
                                        <?php if(isset($o['status'])): ?>
                                            <span class="badge bg-<?php echo $o['status'] == 'delivered' ? 'success' : ($o['status'] == 'approved' ? 'info' : ($o['status'] == 'pending' ? 'warning text-dark' : 'secondary')); ?>">
                                                <?php echo ucfirst($o['status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($o['notes'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include "../../includes/footer.php"; ?> 