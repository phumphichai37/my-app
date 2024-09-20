<?php
require_once 'connectdb.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch orders from the database
$sql = "SELECT * FROM orders"; // Modify this query as needed
$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

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
