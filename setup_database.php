<?php
require_once 'db_connect.php';

$sql = file_get_contents('database.sql');

if ($conn->multi_query($sql)) {
    do {
        // consumes all results
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Database tables created successfully.";

    // Check if admin user exists, if not create one
    // Re-connect to ensure sync? Not needed but safe.

    // Check admin
    $check_admin = "SELECT * FROM users WHERE username = 'admin'";
    $result = $conn->query($check_admin);
    if ($result->num_rows == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO users (username, password, email) VALUES ('admin', '$password', 'admin@lapakbangsawan.com')";
        if ($conn->query($insert_admin) === TRUE) {
            echo "\nDefault admin user created (admin / admin123).";
        } else {
            echo "\nError creating admin user: " . $conn->error;
        }
    } else {
        echo "\nAdmin user already exists.";
    }

} else {
    echo "Error creating tables: " . $conn->error;
}

$conn->close();
?>