<?php
require_once '../../config/db.php';
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
$result = ['account_name' => '', 'account_number' => ''];
if ($type === 'farmer') {
    $sql = "SELECT account_name, account_number FROM farmers WHERE farmer_id = ? LIMIT 1";
} else if ($type === 'industry') {
    $sql = "SELECT account_name, account_number FROM industries WHERE id = ? LIMIT 1";
} else {
    echo json_encode($result);
    exit;
}
if ($stmt = mysqli_prepare($conn, $sql)) {
    if ($type === 'farmer') {
        mysqli_stmt_bind_param($stmt, 's', $id);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $id);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $account_name, $account_number);
    if (mysqli_stmt_fetch($stmt)) {
        $result['account_name'] = $account_name;
        $result['account_number'] = $account_number;
    }
    mysqli_stmt_close($stmt);
}
echo json_encode($result); 