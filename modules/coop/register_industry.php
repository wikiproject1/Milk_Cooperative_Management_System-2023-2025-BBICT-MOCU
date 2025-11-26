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
$company_name = $contact_person = $phone = $email = $address = $industry_type = "";
$company_name_err = $contact_person_err = $phone_err = $email_err = $address_err = $industry_type_err = "";

// Variables for generated credentials
$generated_username = $generated_password = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate company name
    if(empty(trim($_POST["company_name"]))){
        $company_name_err = "Please enter the company name.";
    } else{
        $company_name = trim($_POST["company_name"]);
    }

    // Validate contact person
    if(empty(trim($_POST["contact_person"]))){
        $contact_person_err = "Please enter the contact person's name.";
    } else{
        $contact_person = trim($_POST["contact_person"]);
    }

    // Validate phone
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Please enter the phone number.";
    } else{
        $phone = trim($_POST["phone"]);
    }

    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter the email address.";
    } else{
        // Prepare a select statement to check if email already exists in industries table
        $sql = "SELECT id FROM industries WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered for an industry.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong with email check. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Validate address
    if(empty(trim($_POST["address"]))){
        $address_err = "Please enter the address.";
    } else{
        $address = trim($_POST["address"]);
    }

    // Validate industry type (optional, so no error check needed)
    $industry_type = trim($_POST["industry_type"]);

    // Get logged-in user's ID (created_by)
    $created_by = $_SESSION["user_id"] ?? null; // Use null if user_id is not set

    // Check input errors before inserting in database
    if(empty($company_name_err) && empty($contact_person_err) && empty($phone_err) && empty($email_err) && empty($address_err) && empty($industry_type_err)){
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // --- Create User Account for Industry ---
            // Generate username (e.g., lowercase company name, alphanumeric only)
            $generated_username = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $company_name));
            // Ensure username is unique (simple check, more robust needed for production)
            $sql_check_user = "SELECT id FROM users WHERE username = ?";
            if($stmt_check_user = mysqli_prepare($conn, $sql_check_user)){
                 mysqli_stmt_bind_param($stmt_check_user, "s", $generated_username);
                 mysqli_stmt_execute($stmt_check_user);
                 mysqli_stmt_store_result($stmt_check_user);
                 if(mysqli_stmt_num_rows($stmt_check_user) > 0){
                     // If username exists, append a random number (simple approach)
                     $generated_username .= rand(100, 999);
                 }
                 mysqli_stmt_close($stmt_check_user);
            }

            // Generate a random password (e.g., 10 characters)
            $generated_password = bin2hex(random_bytes(5)); // 10 hex characters

            // Insert into users table with 'industry' role
            $sql_insert_user = "INSERT INTO users (username, password, role) VALUES (?, ?, 'industry')";
            if($stmt_insert_user = mysqli_prepare($conn, $sql_insert_user)){
                $hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt_insert_user, "ss", $generated_username, $hashed_password);
                
                if(mysqli_stmt_execute($stmt_insert_user)){
                    $user_id = mysqli_insert_id($conn);
                    
                    // --- Insert Industry Data ---
                    // Generate unique industry ID
                    $industry_id = generateUniqueIndustryId($conn);

                    $sql_insert_industry = "INSERT INTO industries (industry_id, company_name, contact_person, phone, email, address, industry_type, created_by, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    if($stmt_insert_industry = mysqli_prepare($conn, $sql_insert_industry)){
                        mysqli_stmt_bind_param($stmt_insert_industry, "sssssssii", $industry_id, $param_company_name, $param_contact_person, $param_phone, $param_email, $param_address, $param_industry_type, $param_created_by, $user_id);
                        
                        $param_company_name = $company_name;
                        $param_contact_person = $contact_person;
                        $param_phone = $phone;
                        $param_email = $email;
                        $param_address = $address;
                        $param_industry_type = $industry_type;
                        $param_created_by = $created_by;
                        // $user_id is already set above
                        
                        if(mysqli_stmt_execute($stmt_insert_industry)){
                            mysqli_commit($conn);
                            $_SESSION["success"] = "Industry registered successfully.";
                            // Store generated credentials in session for popup
                            $_SESSION['registered_industry_id'] = $industry_id;
                            $_SESSION['registered_industry_username'] = $generated_username;
                            $_SESSION['registered_industry_password'] = $generated_password; // Store plain password for display
                            header("location: register_industry.php"); // Redirect back to the registration page
                            exit();
                        } else{
                            throw new Exception("Error inserting industry data: " . mysqli_error($conn));
                        }
                        mysqli_stmt_close($stmt_insert_industry);
                    } else{
                         throw new Exception("Error preparing industry insert statement: " . mysqli_error($conn));
                    }
                } else{
                     throw new Exception("Error inserting user data for industry: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt_insert_user);
            } else {
                 throw new Exception("Error preparing user insert statement: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION["error"] = "Registration failed: " . $e->getMessage();
             header("location: register_industry.php"); // Redirect back to the registration page
             exit();
        }
    }
    
    // Close connection (only if not redirected)
    if(!isset($_SESSION["success"]) && !isset($_SESSION["error"])) {
        mysqli_close($conn);
    }
}

// Add this function after db.php
function generateUniqueIndustryId($conn, $length = 8) {
    do {
        $id = 'I' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
        $sql = "SELECT 1 FROM industries WHERE industry_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
    } while ($exists);
    return $id;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Industry - Milk Cooperative System</title>
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
    </style>
</head>
<body>
    <?php include "../../includes/header.php"; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="display-4">Register New Industry</h1>
            <p class="lead">Add a new industry partner to the cooperative system</p>
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
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control <?php echo (!empty($company_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $company_name; ?>">
                            <span class="invalid-feedback"><?php echo $company_name_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control <?php echo (!empty($contact_person_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $contact_person; ?>">
                            <span class="invalid-feedback"><?php echo $contact_person_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                            <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Industry Type (Optional)</label>
                            <select name="industry_type" class="form-select <?php echo (!empty($industry_type_err)) ? 'is-invalid' : ''; ?>">
                                <option value="">Select Industry Type</option>
                                <option value="Dairy Processor">Dairy Processor</option>
                                <option value="Exporter">Exporter</option>
                                <option value="Local Distributor">Local Distributor</option>
                                <option value="Other">Other</option>
                            </select>
                            <span class="invalid-feedback"><?php echo $industry_type_err; ?></span>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register Industry</button>
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

    <?php if(isset($_SESSION['registered_industry_username'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Industry Registered!',
            html: `<b>Industry ID:</b> <?php echo $_SESSION['registered_industry_id']; ?><br>
                   <b>Username:</b> <?php echo $_SESSION['registered_industry_username']; ?><br>
                   <b>Password:</b> <?php echo $_SESSION['registered_industry_password']; ?>`,
            confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['registered_industry_id'], $_SESSION['registered_industry_username'], $_SESSION['registered_industry_password']); ?>
    </script>
    <?php endif; ?>
</body>
</html> 