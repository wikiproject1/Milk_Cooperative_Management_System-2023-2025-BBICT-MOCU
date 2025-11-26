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

// Handle status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $action = $_POST['action'];
    $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : null;
    $admin_comment = isset($_POST['admin_comment']) ? trim($_POST['admin_comment']) : null;
    $new_status = '';
    $sql = '';
    switch($action) {
        case 'approve':
            $sql = "UPDATE orders SET status = 'Approved', admin_comment = ? WHERE id = ?";
            $new_status = 'Approved';
            break;
        case 'deliver':
            $sql = "UPDATE orders SET status = 'Delivered', delivery_date = ? WHERE id = ?";
            $new_status = 'Delivered';
            break;
        case 'cancel':
            $sql = "UPDATE orders SET status = 'Cancelled', admin_comment = ? WHERE id = ?";
            $new_status = 'Cancelled';
            break;
        case 'assign_date':
            $sql = "UPDATE orders SET delivery_date = ? WHERE id = ?";
            $new_status = 'Delivery Date Assigned';
            break;
    }
    if($sql) {
        if($stmt = mysqli_prepare($conn, $sql)) {
            if($action == 'deliver' || $action == 'assign_date') {
                mysqli_stmt_bind_param($stmt, "si", $delivery_date, $order_id);
            } elseif($action == 'approve' || $action == 'cancel') {
                mysqli_stmt_bind_param($stmt, "si", $admin_comment, $order_id);
            } else {
                mysqli_stmt_bind_param($stmt, "i", $order_id);
            }
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION["success"] = "Order status updated successfully!";
                // Optional: Send email after status update
                $email_sql = "SELECT i.email FROM orders o JOIN industries i ON o.industry_id = i.id WHERE o.id = ? LIMIT 1";
                if($email_stmt = mysqli_prepare($conn, $email_sql)){
                    mysqli_stmt_bind_param($email_stmt, "i", $order_id);
                    if(mysqli_stmt_execute($email_stmt)){
                        $email_result = mysqli_stmt_get_result($email_stmt);
                        if($email_row = mysqli_fetch_assoc($email_result)){
                            $recipient_email = $email_row['email'];
                            // mail($recipient_email, "Order Status Update", "Your order #$order_id status is now: $new_status");
                        }
                    }
                    mysqli_stmt_close($email_stmt);
                }
            } else {
                $_SESSION["error"] = "Error updating order status: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: view_orders.php");
    exit();
}

// Fetch orders data with industry details
$sql = "SELECT o.*, i.company_name, i.email FROM orders o JOIN industries i ON o.industry_id = i.id ORDER BY o.order_date DESC";
$orders = mysqli_query($conn, $sql);

// Fetch latest payment status for each industry (workaround for per-order payments)
$industry_payments = [];
$pay_res = mysqli_query($conn, "SELECT industry_id, status FROM payments WHERE industry_id IS NOT NULL ORDER BY payment_date DESC");
while($row = mysqli_fetch_assoc($pay_res)) {
    if (!isset($industry_payments[$row['industry_id']])) {
        $industry_payments[$row['industry_id']] = $row['status'];
    }
}

// Include header
include "../../includes/header.php";
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Industry Orders</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Industry</th>
                        <th>Order Date</th>
                        <th>Required Date</th>
                        <th>Quantity (L)</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                        <?php while($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                            <td><?php echo ($order['order_date'] == '0000-00-00 00:00:00' || !$order['order_date']) ? 'Not set' : $order['order_date']; ?></td>
                            <td><?php echo ($order['delivery_date'] == '0000-00-00' || !$order['delivery_date']) ? 'Not set' : $order['delivery_date']; ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td><?php echo $order['status']; ?></td>
                            <td><?php echo ($order['delivery_date'] == '0000-00-00' || !$order['delivery_date']) ? 'Not assigned' : $order['delivery_date']; ?></td>
                            <td>
                                <?php 
                                $pay_status = isset($industry_payments[$order['industry_id']]) ? $industry_payments[$order['industry_id']] : 'N/A';
                                if ($pay_status == 'paid') {
                                    echo '<span class="badge bg-success">Paid</span>';
                                } elseif ($pay_status == 'unpaid') {
                                    echo '<span class="badge bg-warning text-dark">Unpaid</span>';
                                } elseif ($pay_status == 'rejected') {
                                    echo '<span class="badge bg-danger">Rejected</span>';
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $order['id']; ?>">View Details</button>
                                    <?php if($order['status'] == 'Pending'): ?>
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $order['id']; ?>">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $order['id']; ?>">Reject</button>
                                    <?php endif; ?>
                                    <?php if($order['status'] == 'Approved'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignDateModal<?php echo $order['id']; ?>">Assign Date</button>
                                        <?php if($order['delivery_date']): ?>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Mark this order as delivered?');">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="action" value="deliver">
                                                <input type="hidden" name="delivery_date" value="<?php echo $order['delivery_date']; ?>">
                                                <button type="submit" class="btn btn-info btn-sm">Mark Delivered</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if($order['status'] == 'Delivered' || $order['status'] == 'Cancelled'): ?>
                                        <span class="text-muted">No actions</span>
                                    <?php endif; ?>
                                </div>
                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal<?php echo $order['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Approve Order</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <div class="mb-3">
                                                        <label class="form-label">Comment (optional)</label>
                                                        <textarea class="form-control" name="admin_comment" rows="3" placeholder="Enter comment for approval..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?php echo $order['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Order</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <div class="mb-3">
                                                        <label class="form-label">Comment (required)</label>
                                                        <textarea class="form-control" name="admin_comment" rows="3" placeholder="Enter reason for rejection..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Assign Date Modal -->
                                <div class="modal fade" id="assignDateModal<?php echo $order['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Assign Delivery Date</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="action" value="assign_date">
                                                    <div class="mb-3">
                                                        <label class="form-label">Delivery Date</label>
                                                        <input type="date" class="form-control" name="delivery_date" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Assign Date</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- View Details Modal -->
                                <div class="modal fade" id="detailsModal<?php echo $order['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Order Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
                                                <p><strong>Industry:</strong> <?php echo htmlspecialchars($order['company_name']); ?></p>
                                                <p><strong>Order Date:</strong> <?php echo ($order['order_date'] == '0000-00-00 00:00:00' || !$order['order_date']) ? 'Not set' : $order['order_date']; ?></p>
                                                <p><strong>Required Date:</strong> <?php echo ($order['delivery_date'] == '0000-00-00' || !$order['delivery_date']) ? 'Not set' : $order['delivery_date']; ?></p>
                                                <p><strong>Quantity (L):</strong> <?php echo $order['quantity']; ?></p>
                                                <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
                                                <p><strong>Delivery Date:</strong> <?php echo ($order['delivery_date'] == '0000-00-00' || !$order['delivery_date']) ? 'Not assigned' : $order['delivery_date']; ?></p>
                                                <p><strong>Payment Status:</strong> <?php 
                                                    $pay_status = isset($industry_payments[$order['industry_id']]) ? $industry_payments[$order['industry_id']] : 'N/A';
                                                    if ($pay_status == 'paid') {
                                                        echo '<span class=\'badge bg-success\'>Paid</span>';
                                                    } elseif ($pay_status == 'unpaid') {
                                                        echo '<span class=\'badge bg-warning text-dark\'>Unpaid</span>';
                                                    } elseif ($pay_status == 'rejected') {
                                                        echo '<span class=\'badge bg-danger\'>Rejected</span>';
                                                    } else {
                                                        echo '<span class=\'text-muted\'>N/A</span>';
                                                    }
                                                ?></p>
                                                <p><strong>Admin Comment:</strong>
                                                    <?php
                                                    if (isset($order['admin_comment']) && $order['admin_comment']) {
                                                        echo nl2br(htmlspecialchars($order['admin_comment']));
                                                    } else {
                                                        echo '<span class="text-muted">None</span>';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
