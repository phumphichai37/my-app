<?php
include 'connectdb.php';

$type = $_GET['type'];
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

if ($type === 'daily' && $selectedMonth) {
    // ดึงข้อมูลรายวัน
    $query = "SELECT DAY(order_time) AS day, SUM(total_price) AS total 
              FROM orders 
              WHERE DATE_FORMAT(order_time, '%Y-%m') = '$selectedMonth' 
              GROUP BY day 
              ORDER BY day ASC";
} elseif ($type === 'monthly' && $selectedYear) {
    // ดึงข้อมูลรายเดือน
    $query = "SELECT DATE_FORMAT(order_time, '%Y-%m') AS month, SUM(total_price) AS total 
              FROM orders 
              WHERE DATE_FORMAT(order_time, '%Y') = '$selectedYear' 
              GROUP BY month 
              ORDER BY month ASC";
}

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
