<?php
// Include config file
require_once "../config/db.php";

// Default admin credentials
$username = "admin";
$password = "admin123";

// Check if admin user exists
$sql = "SELECT id, username, password, role FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<h2>Admin User Check</h2>";

if($row = mysqli_fetch_assoc($result)) {
    echo "Admin user found:<br>";
    echo "ID: " . $row['id'] . "<br>";
    echo "Username: " . $row['username'] . "<br>";
    echo "Role: " . $row['role'] . "<br>";
    
    // Verify password
    if(password_verify($password, $row['password'])) {
        echo "<br>Password verification: SUCCESS<br>";
        echo "Stored hash: " . $row['password'] . "<br>";
        echo "Test password: " . $password . "<br>";
    } else {
        echo "<br>Password verification: FAILED<br>";
        echo "Stored hash: " . $row['password'] . "<br>";
        echo "Test password: " . $password . "<br>";
        
        // Create new admin user with correct password
        echo "<br>Attempting to update admin password...<br>";
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $new_hash, $username);
        
        if(mysqli_stmt_execute($update_stmt)) {
            echo "Admin password updated successfully!<br>";
        } else {
            echo "Error updating admin password: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Admin user not found. Creating new admin user...<br>";
    
    // Create new admin user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);
    
    if(mysqli_stmt_execute($insert_stmt)) {
        echo "Admin user created successfully!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password . "<br>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
    }
}

mysqli_close($conn);
?> 