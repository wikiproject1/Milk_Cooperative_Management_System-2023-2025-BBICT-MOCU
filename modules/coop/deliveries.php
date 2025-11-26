<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delivery_id"])) {
    $delivery_id = intval($_POST["delivery_id"]);
    $action = $_POST["action"];
    if ($action === "approve") {
        $new_status = "approved";
        $_SESSION['delivery_action_message'] = "Delivery request approved successfully!";
        $_SESSION['delivery_action_type'] = "success";
    } elseif ($action === "reject") {
        $new_status = "rejected";
        $_SESSION['delivery_action_message'] = "Delivery request rejected.";
        $_SESSION['delivery_action_type'] = "info";
    }
    if (isset($new_status)) {
        $sql = "UPDATE milk_deliveries SET status = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $delivery_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    // Redirect to avoid form resubmission
    header("Location: deliveries.php");
    exit;
}

// Fetch all deliveries (not just pending)
$sql = "SELECT md.*, f.full_name FROM milk_deliveries md JOIN farmers f ON md.farmer_id = f.farmer_id ORDER BY md.delivery_date DESC";
$all_deliveries = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Milk Deliveries - Coop Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include "../../includes/header.php"; ?>
<?php if (isset($_SESSION['delivery_action_message'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: '<?php echo $_SESSION['delivery_action_type']; ?>',
    title: '<?php echo $_SESSION['delivery_action_message']; ?>',
    showConfirmButton: false,
    timer: 1800
});
</script>
<?php unset($_SESSION['delivery_action_message'], $_SESSION['delivery_action_type']); ?>
<?php endif; ?>
<div class="container mt-5">
    <h2 class="mb-4">All Milk Delivery Requests</h2>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Farmer</th>
                            <th>Quantity (L)</th>
                            <th>Delivery Date</th>
                            <th>Shift</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($all_deliveries && mysqli_num_rows($all_deliveries) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($all_deliveries)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($row['delivery_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['shift']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quality_notes']); ?></td>
                                    <td>
                                        <?php if($row['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif($row['status'] == 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif($row['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Unknown</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'pending'): ?>
                                            <form method="post" style="display:inline-block">
                                                <input type="hidden" name="delivery_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                            </form>
                                            <form method="post" style="display:inline-block">
                                                <input type="hidden" name="delivery_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">No action</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No delivery requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include "../../includes/footer.php"; ?>
</body>
</html> 