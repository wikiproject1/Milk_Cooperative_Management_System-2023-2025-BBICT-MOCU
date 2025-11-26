<?php
// Initialize the session
session_start();

// Check if the user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Include config file
require_once "../config/db.php";

// Get the action
$action = $_POST['action'] ?? '';

// Handle different actions
switch($action) {
    case 'update_farmer':
        if(!isset($_POST['farmer_id']) || !isset($_POST['full_name']) || !isset($_POST['phone']) || 
           !isset($_POST['address']) || !isset($_POST['quota'])) {
            die(json_encode(['success' => false, 'message' => 'All fields are required']));
        }
        
        $farmer_id = $_POST['farmer_id'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $quota = floatval($_POST['quota']);
        
        // Validate input
        if(empty($full_name) || empty($phone) || empty($address) || $quota <= 0) {
            die(json_encode(['success' => false, 'message' => 'Invalid input data']));
        }
        
        // Update farmer
        $sql = "UPDATE farmers SET full_name = ?, phone = ?, address = ?, quota = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdi", $full_name, $phone, $address, $quota, $farmer_id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Farmer updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating farmer']);
        }
        break;
        
    case 'delete_farmer':
        if(!isset($_POST['farmer_id'])) {
            die(json_encode(['success' => false, 'message' => 'Farmer ID is required']));
        }
        
        $farmer_id = $_POST['farmer_id'];
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Get user_id and farmer_id for the farmer
            $sql = "SELECT user_id, farmer_id FROM farmers WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $farmer_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if($farmer = mysqli_fetch_assoc($result)) {
                $user_id = $farmer['user_id'];
                $farmer_code = $farmer['farmer_id'];
                
                // Delete related milk_deliveries
                $sql = "DELETE FROM milk_deliveries WHERE farmer_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $farmer_code);
                mysqli_stmt_execute($stmt);
                
                // Delete related payments
                $sql = "DELETE FROM payments WHERE farmer_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $farmer_code);
                mysqli_stmt_execute($stmt);
                
                // Delete farmer
                $sql = "DELETE FROM farmers WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $farmer_id);
                mysqli_stmt_execute($stmt);
                
                // Delete user
                $sql = "DELETE FROM users WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                
                mysqli_commit($conn);
                echo json_encode(['success' => true, 'message' => 'Farmer deleted successfully']);
            } else {
                throw new Exception('Farmer not found');
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error deleting farmer: ' . $e->getMessage()]);
        }
        break;
        
    case 'delete_industry':
        if(!isset($_POST['industry_id'])) {
            die(json_encode(['success' => false, 'message' => 'Industry ID is required']));
        }
        $industry_id = $_POST['industry_id'];
        // Start transaction
        mysqli_begin_transaction($conn);
        try {
            // Get user_id for the industry
            $sql = "SELECT user_id FROM industries WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $industry_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($industry = mysqli_fetch_assoc($result)) {
                $user_id = $industry['user_id'];
                // Delete related payments
                $sql = "DELETE FROM payments WHERE industry_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $industry_id);
                mysqli_stmt_execute($stmt);
                // Delete industry
                $sql = "DELETE FROM industries WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $industry_id);
                mysqli_stmt_execute($stmt);
                // Delete user
                if ($user_id) {
                    $sql = "DELETE FROM users WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_commit($conn);
                echo json_encode(['success' => true, 'message' => 'Industry deleted successfully']);
            } else {
                throw new Exception('Industry not found');
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error deleting industry: ' . $e->getMessage()]);
        }
        break;
        
    case 'update_industry':
        if(!isset($_POST['industry_id']) || !isset($_POST['company_name']) || !isset($_POST['contact_person']) || !isset($_POST['phone']) || !isset($_POST['email']) || !isset($_POST['address'])) {
            die(json_encode(['success' => false, 'message' => 'All fields are required']));
        }
        $industry_id = intval($_POST['industry_id']);
        $company_name = trim($_POST['company_name']);
        $contact_person = trim($_POST['contact_person']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $industry_type = trim($_POST['industry_type'] ?? '');
        // Validate input
        if(empty($company_name) || empty($contact_person) || empty($phone) || empty($email) || empty($address)) {
            die(json_encode(['success' => false, 'message' => 'Invalid input data']));
        }
        $sql = "UPDATE industries SET company_name = ?, contact_person = ?, phone = ?, email = ?, address = ?, industry_type = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $company_name, $contact_person, $phone, $email, $address, $industry_type, $industry_id);
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Industry updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating industry']);
        }
        break;
        
    case 'update_farmer_profile':
        // Only allow farmers to update their own profile
        if ($_SESSION['role'] !== 'farmer') {
            die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
        }
        $farmer_id = intval($_POST['farmer_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if (!$farmer_id || empty($full_name) || empty($phone) || empty($email) || empty($address)) {
            die(json_encode(['success' => false, 'message' => 'All fields are required']));
        }
        // Ensure the farmer_id belongs to the logged-in user
        $sql = "SELECT id FROM farmers WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $farmer_id, $_SESSION['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!mysqli_fetch_assoc($result)) {
            die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
        }
        // Update farmer profile
        $sql = "UPDATE farmers SET full_name = ?, phone = ?, email = ?, address = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $phone, $email, $address, $farmer_id);
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating profile']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?> 