<?php
require_once "../../config/db.php";

// Fetch all pending deliveries with farmer info
$sql = "SELECT d.*, f.full_name FROM deliveries d JOIN farmers f ON d.farmer_id = f.id WHERE d.status = 'pending' ORDER BY d.delivery_date DESC";
$result = mysqli_query($conn, $sql);
$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'], $_POST['delivery_id'])) {
    $delivery_id = intval($_POST['delivery_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    $sql = "UPDATE deliveries SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $action, $delivery_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>Swal.fire('Success', 'Delivery $action successfully!', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Error', 'Failed to update delivery.', 'error');</script>";
    }
} 