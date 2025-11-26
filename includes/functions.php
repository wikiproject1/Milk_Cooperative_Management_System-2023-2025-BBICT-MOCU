/**
 * Get farmer information by user ID
 */
function getFarmerByUserId($user_id) {
    global $conn;
    $sql = "SELECT * FROM farmers WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Get all milk deliveries for a farmer
 */
function getFarmerDeliveries($farmer_id) {
    global $conn;
    $sql = "SELECT * FROM milk_deliveries WHERE farmer_id = ? ORDER BY delivery_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $deliveries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $deliveries[] = $row;
    }
    return $deliveries;
}

/**
 * Get all payments for a farmer
 */
function getFarmerPayments($farmer_id) {
    global $conn;
    $sql = "SELECT * FROM payments WHERE farmer_id = ? ORDER BY payment_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $payments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    return $payments;
}

/**
 * Calculate total milk delivered for a farmer
 */
function calculateTotalMilkDelivered($farmer_id) {
    global $conn;
    $sql = "SELECT SUM(quantity) as total FROM milk_deliveries WHERE farmer_id = ? AND status = 'approved'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

/**
 * Calculate total payments for a farmer
 */
function calculateTotalPayments($farmer_id) {
    global $conn;
    $sql = "SELECT 
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending
            FROM payments 
            WHERE farmer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
} 