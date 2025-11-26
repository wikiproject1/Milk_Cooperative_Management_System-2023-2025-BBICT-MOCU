<?php
// Include config file
require_once "../../config/db.php";

// Add shift column to milk_deliveries table
$sql = "ALTER TABLE milk_deliveries 
        ADD COLUMN shift VARCHAR(50) NULL AFTER delivery_time";

if(mysqli_query($conn, $sql)){
    echo "shift column added successfully to milk_deliveries table.";
} else {
    echo "Error adding shift column: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 