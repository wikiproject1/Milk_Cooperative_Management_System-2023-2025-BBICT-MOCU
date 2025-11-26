<?php
require_once "../../config/db.php";

// Get the farmer's table id using the logged-in user's id
$user_id = $_SESSION["id"];
$farmer_id = null;
$sql = "SELECT * FROM farmers WHERE user_id = ?";
$farmer = null;
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $farmer = mysqli_fetch_assoc($result);
    $farmer_id = $farmer['id'] ?? null;
}

// Fetch deliveries
$delivered = $pending = 0;
$deliveries = [];
if ($farmer_id) {
    $sql = "SELECT * FROM deliveries WHERE farmer_id = ? ORDER BY delivery_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
        if ($row['status'] === 'approved') $delivered += $row['amount'];
        if ($row['status'] === 'pending') $pending += $row['amount'];
    }
}

// Fetch payments
$payments = [];
$total_payments = 0;
if ($farmer_id) {
    $sql = "SELECT * FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
        $total_payments += $row['amount'];
    }
}

// Fetch sales
$sales = [];
$total_sales = 0;
if ($farmer_id) {
    $sql = "SELECT * FROM sales WHERE farmer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $sales[] = $row;
        $total_sales += $row['amount'];
    }
}

$quota = $farmer['quota'] ?? 0;
$balance = $quota - $delivered - $pending - $total_sales;
if ($balance < 0) $balance = 0; 