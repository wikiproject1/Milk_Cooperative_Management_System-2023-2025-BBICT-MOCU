<?php
// Include config file
require_once "../../config/db.php";

// Add quality control columns to milk_deliveries table
$sql = "ALTER TABLE milk_deliveries 
        ADD COLUMN quality_score DECIMAL(3,1) NULL,
        ADD COLUMN ph_level DECIMAL(4,2) NULL,
        ADD COLUMN temperature DECIMAL(4,1) NULL,
        ADD COLUMN fat_content DECIMAL(4,2) NULL,
        ADD COLUMN quality_notes TEXT NULL";

if(mysqli_query($conn, $sql)){
    echo "Quality control columns added successfully to milk_deliveries table.";
} else {
    echo "Error adding quality control columns: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 