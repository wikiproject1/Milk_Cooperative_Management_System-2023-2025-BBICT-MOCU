<?php
// Include config file
require_once "../config/db.php";

// Default admin credentials
$username = "admin";
$password = "admin123"; // This will be hashed
$role = "admin";

// Check if admin user already exists
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) == 0) {
    // Admin user doesn't exist, create it
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "Admin user created successfully!<br>";
            echo "Username: " . $username . "<br>";
            echo "Password: " . $password . "<br>";
            echo "<br>Please delete this file after first use for security reasons.";
        } else {
            echo "Error creating admin user.";
        }
    }
} else {
    echo "Admin user already exists.";
}

mysqli_close($conn);
?> 