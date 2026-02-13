<?php
// Simulating the environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['order_id'] = 4; // Use a valid ID from previous SQL dump (e.g. 4 is pending)
$_POST['reason'] = 'Debug Test Cancellation';

// Catch output
ob_start();
include 'api/cancel_order.php';
$output = ob_get_clean();

echo "--- RAW OUTPUT START ---\n";
echo $output;
echo "\n--- RAW OUTPUT END ---\n";

// Decode to check validity
$json = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "VALID JSON\n";
    print_r($json);
} else {
    echo "INVALID JSON: " . json_last_error_msg();
}
?>