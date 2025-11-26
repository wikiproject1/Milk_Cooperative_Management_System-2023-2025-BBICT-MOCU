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

// Define variables and initialize with empty values
$full_name = $phone = $address = $quota = $email = "";
$full_name_err = $phone_err = $address_err = $quota_err = $email_err = "";

// Variables for generated credentials
$generated_username = $generated_password = "";

// Add this function at the top after db.php
function generateUniqueFarmerId($conn, $length = 8) {
    do {
        $id = 'F' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
        $sql = "SELECT 1 FROM farmers WHERE farmer_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
    } while ($exists);
    return $id;
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate full name
    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter full name.";
    } else{
        $full_name = trim($_POST["full_name"]);
    }

    // Validate phone
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Please enter phone number.";
    } else{
        $phone = trim($_POST["phone"]);
    }

    // Validate address
    if(empty(trim($_POST["address"]))){
        $address_err = "Please enter address.";
    } else{
        $address = trim($_POST["address"]);
    }

    // Validate quota
    if(empty(trim($_POST["quota"]))){
        $quota_err = "Please enter daily milk quota.";
    } else{
        $quota = trim($_POST["quota"]);
    }

    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter email.";
    } else{
         // Prepare a select statement to check if email already exists in farmers table
        $sql = "SELECT id FROM farmers WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered for a farmer.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong with email check. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Check input errors before inserting in database
    if(empty($full_name_err) && empty($phone_err) && empty($address_err) && empty($quota_err) && empty($email_err)){
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Generate unique farmer ID
            $farmer_id = generateUniqueFarmerId($conn);

            // Extract first name from full name
            $first_name = explode(' ', $full_name)[0];
            $display_name = $first_name . '-' . $farmer_id;

            // --- Create User Account for Farmer ---
            // Generate username (e.g., lowercase full name, alphanumeric only, potentially with numbers)
            $generated_username_base = strtolower(preg_replace('/[^A-Za-z0-9]/', '', str_replace(' ', '', $full_name)));
            $generated_username = $generated_username_base;
            $i = 1;
            // Ensure username is unique
            while (true) {
                $sql_check_user = "SELECT id FROM users WHERE username = ?";
                if($stmt_check_user = mysqli_prepare($conn, $sql_check_user)){
                     mysqli_stmt_bind_param($stmt_check_user, "s", $generated_username);
                     mysqli_stmt_execute($stmt_check_user);
                     mysqli_stmt_store_result($stmt_check_user);
                     if(mysqli_stmt_num_rows($stmt_check_user) == 0){
                         mysqli_stmt_close($stmt_check_user);
                         break; // Username is unique, exit loop
                     }
                     mysqli_stmt_close($stmt_check_user);
                     // If username exists, try appending a number
                     $generated_username = $generated_username_base . $i;
                     $i++;
                } else{
                    throw new Exception("Error preparing user check statement: " . mysqli_error($conn));
                }
            }

            // Generate a random password (e.g., 10 characters)
            $generated_password = bin2hex(random_bytes(5)); // 10 hex characters

            // Insert into users table with 'farmer' role
            $sql_insert_user = "INSERT INTO users (username, password, role) VALUES (?, ?, 'farmer')";
            if($stmt_insert_user = mysqli_prepare($conn, $sql_insert_user)){
                $hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt_insert_user, "ss", $generated_username, $hashed_password);
                
                if(mysqli_stmt_execute($stmt_insert_user)){
                    $user_id = mysqli_insert_id($conn);
                    
                    // --- Insert Farmer Data ---
                    $sql_insert_farmer = "INSERT INTO farmers (user_id, farmer_id, full_name, phone, address, quota, email) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    if($stmt_insert_farmer = mysqli_prepare($conn, $sql_insert_farmer)){
                        mysqli_stmt_bind_param($stmt_insert_farmer, "issssds", $user_id, $farmer_id, $display_name, $phone, $address, $quota, $email);
                        
                        if(mysqli_stmt_execute($stmt_insert_farmer)){
                            mysqli_commit($conn);
                           
                            // Store generated credentials in session for popup
                            $_SESSION['registered_farmer_id'] = $farmer_id;
                            $_SESSION['registered_farmer_username'] = $generated_username;
                            $_SESSION['registered_farmer_password'] = $generated_password;

                            // --- Email Sending Logic (requires mail server configuration) ---
                            $to = $email;
                            $subject = "Welcome to Milk Cooperative System - Your Login Details";
                            $message = "Dear " . htmlspecialchars($full_name) . ",\n\n";
                            $message .= "Welcome to the Milk Cooperative System!\n\n";
                            $message .= "Your Farmer ID: " . htmlspecialchars($farmer_id) . "\n";
                            $message .= "Your login details are:\n";
                            $message .= "Username: " . htmlspecialchars($generated_username) . "\n";
                            $message .= "Password: " . htmlspecialchars($generated_password) . "\n\n"; // Use plain password here
                            $message .= "You can login here: http://localhost/MCS/modules/auth/login.php\n\n";
                            $message .= "Please keep this information secure.\n\n";
                            $message .= "Sincerely,\nThe MCS Team";
                            $headers = 'From: webmaster@your-coop-domain.com' . "\r\n" .
                               'Reply-To: webmaster@your-coop-domain.com' . "\r\n" .
                               'X-Mailer: PHP/' . phpversion();

                            // Attempt to send email (Note: This requires your server to be configured to send mail)
                            if(mail($to, $subject, $message, $headers)){
                                // Email sent successfully (optional: log this)
                                // echo "Login details email sent to " . htmlspecialchars($email);
                            } else{
                                // Email sending failed (optional: log this or show a warning)
                                // echo "Failed to send login details email to " . htmlspecialchars($email);
                            }
                            // --- End Email Sending Logic ---

                            // Redirect back to the registration page to trigger the SweetAlert2 popup
                            header("location: register_farmer.php");
                            exit();
                        } else{
                            throw new Exception("Error inserting farmer data: " . mysqli_error($conn));
                        }
                         mysqli_stmt_close($stmt_insert_farmer);
                    }
                } else{
                    throw new Exception("Error inserting user data for farmer: " . mysqli_error($conn));
                }
                 mysqli_stmt_close($stmt_insert_user);
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION["error"] = "Registration failed: " . $e->getMessage();
             header("location: register_farmer.php"); // Redirect back to the registration page
             exit();
        }
    }
    
     // Close connection (only if not redirected)
    if(!isset($_SESSION["success"]) && !isset($_SESSION["error"])) {
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Farmer - Milk Cooperative System</title>
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

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #E9ECEF;
            transition: all 0.3s ease;
        }

        .form-control:focus {
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
            <h1 class="display-4">Register New Farmer</h1>
            <p class="lead">Add a new farmer to the cooperative system</p>
        </div>
    </div>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-card">
                     <?php if(isset($_SESSION["success"])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION["success"];
                                unset($_SESSION["success"]);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION["error"])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION["error"];
                                unset($_SESSION["error"]);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-section">
                            <h3 class="section-title">Personal Information</h3>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                                <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                                <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $address; ?></textarea>
                                <span class="invalid-feedback"><?php echo $address_err; ?></span>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Milk Collection Details</h3>
                            <div class="mb-3">
                                <label class="form-label">Daily Milk Quota (Liters)</label>
                                <input type="number" step="0.01" name="quota" class="form-control <?php echo (!empty($quota_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $quota; ?>">
                                <span class="invalid-feedback"><?php echo $quota_err; ?></span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Register Farmer</button>
                            <a href="farmers.php" class="btn btn-outline-secondary">Cancel</a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js"></script>
    <!-- Custom JS -->
    <!-- <script src="../../assets/js/main.js"></script> -->

    <?php if(isset($_SESSION['registered_farmer_username'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Farmer Registered!',
            html: 'Farmer <strong><?php echo htmlspecialchars($_SESSION['registered_farmer_username']); ?></strong> registered successfully.<br>Login Details:<br>Username: <strong><?php echo htmlspecialchars($_SESSION['registered_farmer_username']); ?></strong><br>Password: <strong><?php echo htmlspecialchars($_SESSION['registered_farmer_password']); ?></strong>',
            confirmButtonText: 'OK'
        });
        <?php 
            // Clear the session variables after displaying
            unset($_SESSION['registered_farmer_username']);
            unset($_SESSION['registered_farmer_password']);
        ?>
    </script>
    <?php endif; ?>
</body>
</html> 