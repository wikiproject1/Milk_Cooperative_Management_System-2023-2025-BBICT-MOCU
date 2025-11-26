<?php
// Include config file
require_once "../../config/db.php";

// Add user_id column and foreign key to industries table
$sql = "ALTER TABLE industries 
        ADD COLUMN user_id INT NULL,
        ADD CONSTRAINT fk_industry_user 
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";

if(mysqli_query($conn, $sql)){
    echo "User ID column and foreign key added successfully to industries table.";
} else {
    echo "Error adding user_id column and foreign key: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 