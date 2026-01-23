<?php
// configlinux
// $host = "localhost";
// $user = "andi";
// $pass = "passwordbaru";
// $db_name = "lapak_bangsawan";

// config windows
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "lapak_bangsawan";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    // echo "Database created successfully";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($db_name);
?>