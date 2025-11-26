<?php
// Include config file
require_once "../../config/db.php";

// SQL to create orders table
$sql = "CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    industry_id INT NOT NULL,
    order_date DATE DEFAULT CURRENT_DATE,
    required_date DATE,
    quantity_liters DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    assigned_quantity DECIMAL(10, 2) DEFAULT 0.00,
    delivery_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (industry_id) REFERENCES industries(id)
);";

if (mysqli_query($conn, $sql)) {
    echo "Table orders created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);

?> 