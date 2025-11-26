<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once "../../config/db.php";

// Check if id parameter is set in URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get URL parameter
    $id =  trim($_GET["id"]);

    // Prepare a select statement
    $sql = "SELECT f.*, u.username 
            FROM farmers f 
            JOIN users u ON f.user_id = u.id 
            WHERE f.id = ?";

    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        // Set parameters
        $param_id = $id;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) == 1){
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use a while loop */
                $farmer = mysqli_fetch_assoc($result);

                // Retrieve individual field value
                $full_name = $farmer["full_name"];
                $username = $farmer["username"];
                $email = $farmer["email"];
                $phone = $farmer["phone"];
                $address = $farmer["address"];
                $quota = $farmer["quota"];
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                header("location: error.php"); // You might want to create an error page
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);

    // Close connection
    mysqli_close($conn);
} else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php"); // You might want to create an error page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Farmer - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Add specific styles for view page if needed */
        .detail-card {
            background-color: var(--card-bg, #fff);
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }
        .detail-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--background-color, #f8f9fa);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: var(--primary-color, #6C63FF);
            min-width: 180px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .detail-value {
            color: var(--text-primary, #2D3436);
            font-size: 1.1rem;
            flex-grow: 1;
        }
        .detail-icon {
            font-size: 1.3rem;
            color: var(--primary-color, #6C63FF);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color, #6C63FF), #8B7FFF);
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

        @media (max-width: 767.98px) {
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .detail-label {
                min-width: auto;
                margin-bottom: 0.3rem;
            }
            .detail-value {
                font-size: 1rem;
            }
            .detail-icon {
                font-size: 1.2rem;
            }
            .detail-card {
                padding: 1.5rem;
            }
            .detail-value {
                 word-wrap: break-word;
                 overflow-wrap: break-word;
                 width: 100%; /* Ensure the value takes full width to allow wrapping */
            }
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Farmer Details</h1>
            <p class="lead">Viewing information for <?php echo htmlspecialchars($full_name); ?></p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="detail-card">
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-user detail-icon"></i> Full Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-user-circle detail-icon"></i> Username:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-envelope detail-icon"></i> Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-phone detail-icon"></i> Phone:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($phone); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-map-marker-alt detail-icon"></i> Address:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($address); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label"><i class="fas fa-flask detail-icon"></i> Daily Quota (Liters):</span>
                        <span class="detail-value"><?php echo htmlspecialchars($quota); ?> L</span>
                    </div>
                    <p class="text-center mt-4 mb-0"><a href="farmers.php" class="btn btn-primary">Back to Farmers List</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
</body>
</html> 