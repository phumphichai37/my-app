<?php
require_once 'connectdb.php';

// Fetch orders from the database
$sql = "SELECT * FROM orders"; // Modify this query as needed
$result = $conn->query($sql);

$orders = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Return the orders as JSON
header('Content-Type: application/json');
echo json_encode($orders);
?>
