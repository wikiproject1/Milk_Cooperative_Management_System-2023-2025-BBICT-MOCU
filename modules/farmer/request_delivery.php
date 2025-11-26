<?php
// Initialize the session
session_start();

// Check if the user is logged in and is a farmer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "farmer"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

$success_message = $error_message = "";

// Get farmer_id from session user id
$user_id = $_SESSION["id"];
$farmer_id = null;
$sql = "SELECT farmer_id FROM farmers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $farmer_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = trim($_POST["quantity"]);
    $delivery_date = trim($_POST["delivery_date"]);
    $shift = trim($_POST["shift"]);
    $quality_notes = trim($_POST["quality_notes"]);
    
    // Validate input
    if (empty($quantity) || empty($delivery_date) || empty($shift)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Insert delivery request
        $sql = "INSERT INTO milk_deliveries (farmer_id, quantity, delivery_date, shift, quality_notes, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sdsss", $farmer_id, $quantity, $delivery_date, $shift, $quality_notes);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Delivery request submitted successfully!";
            } else {
                $error_message = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Milk Delivery - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "../../includes/farmer_header.php"; ?>
    <div class="main-content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-truck"></i> Request Milk Delivery</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity (Liters)</label>
                                    <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shift" class="form-label">Shift</label>
                                    <select class="form-select" id="shift" name="shift" required>
                                        <option value="">Select Shift</option>
                                        <option value="morning">Morning</option>
                                        <option value="evening">Evening</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="quality_notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="quality_notes" name="quality_notes" rows="3"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit Request
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('delivery_date').min = new Date().toISOString().split('T')[0];
    </script>
    <?php if (!empty($success_message)): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: <?php echo json_encode($success_message); ?>
      });
    </script>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: <?php echo json_encode($error_message); ?>
      });
    </script>
    <?php endif; ?>
</body>
</html>