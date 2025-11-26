<?php
// Include config file
require_once "../../config/db.php";

// Add delivery_time column to milk_deliveries table
$sql = "ALTER TABLE milk_deliveries 
        ADD COLUMN delivery_time TIME NULL AFTER delivery_date";

if(mysqli_query($conn, $sql)){
    echo "delivery_time column added successfully to milk_deliveries table.";
} else {
    echo "Error adding delivery_time column: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 