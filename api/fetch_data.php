<?php
// Initialize the session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Include config file
require_once "../config/db.php";

// Get the action
$action = $_POST['action'] ?? '';

// Handle different actions
switch($action) {
    case 'get_farmer':
        if(!isset($_POST['farmer_id'])) {
            die(json_encode(['success' => false, 'message' => 'Farmer ID is required']));
        }
        
        $farmer_id = $_POST['farmer_id'];
        
        // Get farmer details
        $sql = "SELECT f.*, u.username, 
                (SELECT COUNT(*) FROM milk_deliveries WHERE farmer_id = f.id) as total_deliveries,
                (SELECT COALESCE(SUM(quantity), 0) FROM milk_deliveries WHERE farmer_id = f.id) as total_milk
                FROM farmers f 
                JOIN users u ON f.user_id = u.id 
                WHERE f.id = ?";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $farmer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($farmer = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $farmer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Farmer not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?> 