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

// Define variables and initialize with empty values
$farmer_id = $delivery_date = $delivery_time = $shift = $quantity = $quality_score = $ph_level = $temperature = $fat_content = $quality_notes = "";
$farmer_id_err = $delivery_date_err = $delivery_time_err = $shift_err = $quantity_err = $quality_score_err = $ph_level_err = $temperature_err = $fat_content_err = $quality_notes_err = "";

// Handle farmer search (server-side, no AJAX)
$search_term = $_GET['farmer_search'] ?? '';
$farmers = [];
$sql_farmers = "SELECT f.farmer_id, f.full_name FROM users u JOIN farmers f ON u.id = f.user_id WHERE u.role = 'farmer'";
if (!empty($search_term)) {
    $sql_farmers .= " AND (f.full_name LIKE ? OR f.farmer_id LIKE ? OR f.phone LIKE ?)";
}
$sql_farmers .= " ORDER BY f.full_name";
if($stmt = mysqli_prepare($conn, $sql_farmers)){
    if (!empty($search_term)) {
        $like_term = "%$search_term%";
        mysqli_stmt_bind_param($stmt, "sss", $like_term, $like_term, $like_term);
    }
    mysqli_stmt_execute($stmt);
    $result_farmers = mysqli_stmt_get_result($stmt);
    while($row_farmers = mysqli_fetch_assoc($result_farmers)){
        $farmers[] = $row_farmers;
    }
    mysqli_stmt_close($stmt);
} else{
    echo "<div class='text-danger'>Error fetching farmers: " . mysqli_error($conn) . "</div>";
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate farmer ID
    if(empty(trim($_POST["farmer_id"]))){
        $farmer_id_err = "Please select a farmer.";
    } else{
        $farmer_id = trim($_POST["farmer_id"]);
    }

    // Validate delivery date (assuming date input type)
    if(empty(trim($_POST["delivery_date"]))){
        $delivery_date_err = "Please enter delivery date.";
    } else{
        $delivery_date = trim($_POST["delivery_date"]);
    }

     // Validate delivery time (assuming time input type)
    if(empty(trim($_POST["delivery_time"]))){
        $delivery_time_err = "Please enter delivery time.";
    } else{
        $delivery_time = trim($_POST["delivery_time"]);
    }

    // Validate shift
    if(empty(trim($_POST["shift"]))){
        $shift_err = "Please select a shift.";
    } else{
        $shift = trim($_POST["shift"]);
    }

    // Validate quantity
    if(empty(trim($_POST["quantity"]))){
        $quantity_err = "Please enter quantity.";
    } elseif(!is_numeric($_POST["quantity"]) || $_POST["quantity"] < 0){
        $quantity_err = "Quantity must be a positive number.";
    } else{
        $quantity = trim($_POST["quantity"]);
    }

    // Validate quality score (optional)
    $quality_score = trim($_POST["quality_score"]);
    if(!empty($quality_score) && (!is_numeric($quality_score) || $quality_score < 0 || $quality_score > 5)){
         $quality_score_err = "Quality score must be a number between 0 and 5.";
    }

    // Validate ph level (optional)
    $ph_level = trim($_POST["ph_level"]);
     if(!empty($ph_level) && (!is_numeric($ph_level) || $ph_level < 0)){
         $ph_level_err = "pH level must be a positive number.";
    }

    // Validate temperature (optional)
    $temperature = trim($_POST["temperature"]);
     if(!empty($temperature) && (!is_numeric($temperature) || $temperature < 0)){
         $temperature_err = "Temperature must be a positive number.";
    }

    // Validate fat content (optional)
    $fat_content = trim($_POST["fat_content"]);
     if(!empty($fat_content) && (!is_numeric($fat_content) || $fat_content < 0)){
         $fat_content_err = "Fat content must be a positive number.";
    }

    // Quality notes (optional)
    $quality_notes = trim($_POST["quality_notes"]);

    // Check input errors before inserting in database
    if(empty($farmer_id_err) && empty($delivery_date_err) && empty($delivery_time_err) && empty($shift_err) && empty($quantity_err) && empty($quality_score_err) && empty($ph_level_err) && empty($temperature_err) && empty($fat_content_err) && empty($quality_notes_err)){

        // Start transaction (optional but good practice for related inserts)
        // mysqli_begin_transaction($conn);

        try {
            // Prepare an insert statement for milk_deliveries
            $sql = "INSERT INTO milk_deliveries (farmer_id, delivery_date, delivery_time, shift, quantity, quality_score, ph_level, temperature, fat_content, quality_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        if($stmt = mysqli_prepare($conn, $sql)){
                            // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssssdsssss", $param_farmer_id, $param_delivery_date, $param_delivery_time, $param_shift, $param_quantity, $param_quality_score, $param_ph_level, $param_temperature, $param_fat_content, $param_quality_notes);
                            // Set parameters
                $param_farmer_id = $farmer_id; // Use the selected farmer_id directly
                            $param_delivery_date = $delivery_date;
                            $param_delivery_time = $delivery_time;
                            $param_shift = $shift;
                            $param_quantity = $quantity;
                $param_quality_score = (!empty($quality_score)) ? $quality_score : NULL;
                $param_ph_level = (!empty($ph_level)) ? $ph_level : NULL;
                $param_temperature = (!empty($temperature)) ? $temperature : NULL;
                $param_fat_content = (!empty($fat_content)) ? $fat_content : NULL;
                $param_quality_notes = (!empty($quality_notes)) ? $quality_notes : NULL;
                            // Attempt to execute the prepared statement
                            if(mysqli_stmt_execute($stmt)){
                    // Delivery recorded successfully
                    // mysqli_commit($conn); // Commit transaction if using
                    $_SESSION["record_delivery_success"] = "Milk delivery recorded successfully!";
                    header("location: record_delivery.php"); // Redirect to clear form
                                 exit();
                            } else{
                    // mysqli_rollback($conn); // Rollback transaction if using
                    $_SESSION["error"] = "Error recording delivery: " . mysqli_error($conn);
                    header("location: record_delivery.php"); // Redirect to show error
                    exit();
                            }
                            // Close statement
                            mysqli_stmt_close($stmt);
                        } else{
                // mysqli_rollback($conn); // Rollback transaction if using
                throw new Exception("Error preparing delivery insert statement: " . mysqli_error($conn));
            }

        } catch (Exception $e) {
            // mysqli_rollback($conn); // Rollback transaction if using
            $_SESSION["error"] = "Registration failed: " . $e->getMessage(); // Changed from Registration failed to reflect delivery recording
            header("location: record_delivery.php"); // Redirect back to the registration page
             exit();
        }
    }
    
     // Close connection (only if not redirected)
    if(!isset($_SESSION["record_delivery_success"]) && !isset($_SESSION["error"])) {
        mysqli_close($conn);
    }

}

// Close connection if script didn't exit
if (isset($conn) && !mysqli_real_escape_string($conn, '')) { // Check if connection is open
     mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Milk Delivery - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4CAF50;
            --accent-color: #FF6B6B;
            --background-color: #F8F9FA;
            --card-bg: #FFFFFF;
            --text-primary: #2D3436;
            --text-secondary: #636E72;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.2);
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #E9ECEF;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #8B7FFF);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .invalid-feedback {
            color: var(--accent-color);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #E9ECEF;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Record Milk Delivery</h1>
            <p class="lead">Enter details of a milk delivery from a farmer</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                    <?php if(isset($_SESSION["error"])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION["error"];
                                unset($_SESSION["error"]);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Farmer Search Box (not inside the form) -->
                    <form method="get" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="farmer_search" class="form-control" placeholder="Search by Farmer ID, Name, or Phone Number..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                    <?php if ($search_term && empty($farmers)): ?>
                        <div class="text-danger mb-3">No matches found.</div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-section">
                            <h3 class="section-title">Delivery Details</h3>
                            <div class="mb-3">
                                <label for="farmer_id" class="form-label">Farmer</label>
                                <select name="farmer_id" id="farmer_id" class="form-select <?php echo (!empty($farmer_id_err)) ? 'is-invalid' : ''; ?>">
                                    <option value="">-- Select Farmer --</option>
                                    <?php foreach ($farmers as $farmer): ?>
                                        <option value="<?php echo $farmer['farmer_id']; ?>" <?php echo ($farmer_id == $farmer['farmer_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($farmer['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="invalid-feedback"><?php echo $farmer_id_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label for="delivery_date" class="form-label">Date</label>
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control <?php echo (!empty($delivery_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $delivery_date; ?>" required>
                                <span class="invalid-feedback"><?php echo $delivery_date_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label for="delivery_time" class="form-label">Time</label>
                                <input type="time" name="delivery_time" id="delivery_time" class="form-control <?php echo (!empty($delivery_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $delivery_time; ?>" required>
                                <span class="invalid-feedback"><?php echo $delivery_time_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label for="shift" class="form-label">Shift</label>
                                <select name="shift" id="shift" class="form-select <?php echo (!empty($shift_err)) ? 'is-invalid' : ''; ?>" required>
                                    <option value="">-- Select Shift --</option>
                                    <option value="Morning" <?php echo ($shift == 'Morning') ? 'selected' : ''; ?>>Morning</option>
                                    <option value="Evening" <?php echo ($shift == 'Evening') ? 'selected' : ''; ?>>Evening</option>
                                </select>
                                <span class="invalid-feedback"><?php echo $shift_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity (Liters)</label>
                                <input type="number" step="0.01" name="quantity" id="quantity" class="form-control <?php echo (!empty($quantity_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity; ?>" required>
                                <span class="invalid-feedback"><?php echo $quantity_err; ?></span>
                            </div>
                        </div>

                         <div class="form-section">
                            <h3 class="section-title">Quality Control (Optional)</h3>
                             <div class="mb-3">
                                <label for="quality_score" class="form-label">Quality Score (0-5)</label>
                                <input type="number" step="0.1" name="quality_score" id="quality_score" class="form-control <?php echo (!empty($quality_score_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quality_score; ?>">
                                <span class="invalid-feedback"><?php echo $quality_score_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label for="ph_level" class="form-label">pH Level</label>
                                <input type="number" step="0.01" name="ph_level" id="ph_level" class="form-control <?php echo (!empty($ph_level_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ph_level; ?>">
                                <span class="invalid-feedback"><?php echo $ph_level_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label for="temperature" class="form-label">Temperature (°C)</label>
                                <input type="number" step="0.1" name="temperature" id="temperature" class="form-control <?php echo (!empty($temperature_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $temperature; ?>">
                                <span class="invalid-feedback"><?php echo $temperature_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label for="fat_content" class="form-label">Fat Content (%)</label>
                                <input type="number" step="0.01" name="fat_content" id="fat_content" class="form-control <?php echo (!empty($fat_content_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fat_content; ?>">
                                <span class="invalid-feedback"><?php echo $fat_content_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label for="quality_notes" class="form-label">Notes</label>
                                <textarea name="quality_notes" id="quality_notes" class="form-control <?php echo (!empty($quality_notes_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $quality_notes; ?></textarea>
                                <span class="invalid-feedback"><?php echo $quality_notes_err; ?></span>
                            </div>
                         </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Record Delivery</button>
                            <!-- Add a link back to dashboard or deliveries list -->
                            <!-- <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js"></script>
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->

     <?php if(isset($_SESSION['record_delivery_success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $_SESSION['record_delivery_success']; ?>',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['record_delivery_success']); ?>
    </script>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $_SESSION['error']; ?>',
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>


</body>
</html> 