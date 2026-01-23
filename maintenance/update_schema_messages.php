<?php
require("../config/database.php");

// Add is_read column if it doesn't exist
$check = $conn->query("SHOW COLUMNS FROM messages LIKE 'is_read'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER message";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'is_read' added successfully to 'messages' table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'is_read' already exists.";
}

$conn->close();
?>