<?php
// Include config file
require_once "../../config/db.php";

// Add columns to industries table
$sql = "ALTER TABLE industries 
        ADD COLUMN industry_type VARCHAR(255) NULL,
        ADD COLUMN created_by INT NULL,
        ADD CONSTRAINT fk_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL";

if(mysqli_query($conn, $sql)){
    echo "Quality control columns added successfully to milk_deliveries table.";
} else {
    echo "Error adding quality control columns: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 