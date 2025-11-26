<?php
// Include config file
require_once "../config/db.php";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Read and execute the SQL schema
$sql = file_get_contents('schema.sql');

// Split the SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$message = "";

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    // Extract table name from CREATE TABLE statement
    if (preg_match('/CREATE TABLE `?(\w+)`?/i', $statement, $matches)) {
        $tableName = $matches[1];
        
        // Skip if table already exists
        if (tableExists($conn, $tableName)) {
            $message .= "Table '$tableName' already exists. Skipping...<br>";
            continue;
        }
    }
    
    // Execute the statement
    if (!mysqli_query($conn, $statement)) {
        $success = false;
        $message .= "Error executing statement: " . mysqli_error($conn) . "<br>";
        break;
    }
}

if ($success) {
    echo "Database initialized successfully!<br>";
    echo $message;
} else {
    echo "Error initializing database:<br>";
    echo $message;
}

mysqli_close($conn);
?> 