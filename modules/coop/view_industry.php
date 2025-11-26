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

// Check if industry ID is provided in the URL
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    // Redirect to industries list if no ID is provided
    header("location: industries.php");
    exit;
}

// Prepare a select statement to get industry details and the creator's username
$sql = "SELECT i.*, u.username as created_by_username 
        FROM industries i 
        LEFT JOIN users u ON i.created_by = u.id
        WHERE i.id = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "i", $param_id);
    
    // Set parameters
    $param_id = trim($_GET["id"]);
    
    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1){
            // Fetch result row as an associative array
            $industry = mysqli_fetch_assoc($result);
        } else{
            // URL doesn't contain valid id. Redirect to error page or industries list
            header("location: industries.php"); // Redirect to industries list for now
            exit();
        }
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }

    // Close statement
    mysqli_stmt_close($stmt);
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Industry - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
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

        .details-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .detail-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #E9ECEF;
        }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            display: block;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Industry Details</h1>
            <p class="lead">Viewing details for <?php echo htmlspecialchars($industry['company_name']); ?></p>
        </div>
    </div>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="details-card">
                    <div class="detail-item">
                        <span class="detail-label">Company Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['company_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact Person:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['contact_person']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone Number:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['phone']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email Address:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo nl2br(htmlspecialchars($industry['address'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Industry Type:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['industry_type'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Created By:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($industry['created_by_username'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="mt-4">
                        <a href="industries.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Industries List</a>
                        <a href="edit_industry.php?id=<?php echo $industry['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Industry</a>
                        <a href="delete_industry.php?id=<?php echo $industry['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this industry?');"><i class="fas fa-trash"></i> Delete Industry</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "../../includes/footer.php"; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->
</body>
</html> 