<?php
require_once "../../config/db.php";

// Get the farmer's table id using the logged-in user's id
$user_id = $_SESSION["id"];
$farmer_id = null;
$sql = "SELECT id FROM farmers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $farmer_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... existing code ...
    if ($farmer_id) {
        $sql = "INSERT INTO deliveries (farmer_id, delivery_date, shift, amount, quality_notes, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issds", $farmer_id, $delivery_date, $shift, $amount, $quality_notes);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>Swal.fire('Success', 'Delivery request submitted!', 'success');</script>";
        } else {
            echo "<script>Swal.fire('Error', 'Failed to submit delivery request.', 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error', 'Farmer not found.', 'error');</script>";
    }
}
// ... existing code ... 