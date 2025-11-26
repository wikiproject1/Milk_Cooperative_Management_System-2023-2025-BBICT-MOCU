<?php
// Initialize the session
session_start();

// Check if the user is logged in, if yes then redirect to appropriate dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    switch($_SESSION["role"]) {
        case "admin":
            header("location: ../coop/dashboard.php");
            break;
        case "farmer":
            header("location: ../farmer/dashboard.php");
            break;
        case "industry":
            header("location: ../industry/dashboard.php");
            break;
    }
    exit;
}

// Include config file
require_once "../../config/db.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Get user type from URL parameter
$user_type = isset($_GET['type']) ? $_GET['type'] : '';

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["user_id"] = $id;
                            
                            // Redirect user to appropriate dashboard
                            switch($role) {
                                case "admin":
                                    header("location: ../coop/dashboard.php");
                                    break;
                                case "farmer":
                                    header("location: ../farmer/dashboard.php");
                                    break;
                                case "industry":
                                    header("location: ../industry/dashboard.php");
                                    break;
                            }
                            exit();
                        } else{
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
            } else{
                $login_err = "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Milk Cooperative System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .login-type {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <?php if($user_type == 'farmer'): ?>
                    <i class="fas fa-user text-primary"></i>
                    <h2>Farmer Login</h2>
                <?php elseif($user_type == 'industry'): ?>
                    <i class="fas fa-industry text-success"></i>
                    <h2>Industry Login</h2>
                <?php elseif($user_type == 'admin'): ?>
                    <i class="fas fa-user-shield text-danger"></i>
                    <h2>Admin Login</h2>
                <?php else: ?>
                    <i class="fas fa-sign-in-alt text-primary"></i>
                    <h2>Login</h2>
                <?php endif; ?>
            </div>

            <?php if(!empty($login_err)): ?>
                <div class="alert alert-danger"><?php echo $login_err; ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . ($user_type ? "?type=$user_type" : ""); ?>" method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                            <span class="invalid-feedback"><?php echo $username_err; ?></span>
                        </div>    
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <?php if($user_type == 'admin'): ?>
                    
                
                <?php else: ?>
                    <p>Don't have an account? Contact your cooperative station to register.</p>
                <?php endif; ?>
                <a href="../../index.php" class="btn btn-link">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 