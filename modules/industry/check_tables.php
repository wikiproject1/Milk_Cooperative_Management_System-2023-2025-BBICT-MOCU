<?php
require_once "../../config/db.php";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Function to check if a column exists in a table
function columnExists($conn, $tableName, $columnName) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return mysqli_num_rows($result) > 0;
}

// Check tables
$tables = ['orders', 'payments', 'delivery_feedback'];
$missingTables = [];
$missingColumns = [];

foreach ($tables as $table) {
    if (!tableExists($conn, $table)) {
        $missingTables[] = $table;
    }
}

// Check specific columns
if (tableExists($conn, 'payments')) {
    if (!columnExists($conn, 'payments', 'industry_id')) {
        $missingColumns[] = 'payments.industry_id';
    }
}

if (tableExists($conn, 'orders')) {
    if (!columnExists($conn, 'orders', 'industry_id')) {
        $missingColumns[] = 'orders.industry_id';
    }
}

// Output results
echo "<h2>Database Structure Check</h2>";

if (empty($missingTables) && empty($missingColumns)) {
    echo "<p style='color: green;'>All tables and columns are present!</p>";
} else {
    if (!empty($missingTables)) {
        echo "<p style='color: red;'>Missing tables:</p>";
        echo "<ul>";
        foreach ($missingTables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($missingColumns)) {
        echo "<p style='color: red;'>Missing columns:</p>";
        echo "<ul>";
        foreach ($missingColumns as $column) {
            echo "<li>$column</li>";
        }
        echo "</ul>";
    }
    
    echo "<p>Please run the SQL commands from database/industry_tables.sql to create the missing tables and columns.</p>";
}

mysqli_close($conn);
?> 