<?php
include 'connectdb.php';

$type = $_GET['type'];
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$data = []; // สร้างอาร์เรย์สำหรับเก็บข้อมูล

if ($type === 'daily' && $selectedMonth) {
    error_log("Selected Month: $selectedMonth");
    
    $query = "SELECT DAY(order_time) AS day, SUM(total_price) AS total 
              FROM orders 
              WHERE DATE_FORMAT(order_time, '%Y-%m') = '$selectedMonth' 
              GROUP BY day 
              ORDER BY day ASC";
} elseif ($type === 'monthly' && $selectedYear) {
    error_log("Selected Year: $selectedYear");
    
    $query = "SELECT DATE_FORMAT(order_time, '%Y-%m') AS month, SUM(total_price) AS total 
              FROM orders 
              WHERE DATE_FORMAT(order_time, '%Y') = '$selectedYear' 
              GROUP BY month 
              ORDER BY month ASC";
} else {
    echo json_encode(['error' => 'Invalid request parameters']);
    exit;
}

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

while ($row = $result->fetch_assoc()) {
    // แปลง total เป็น float เพื่อให้มั่นใจว่ามันคือหมายเลข
    $data[] = [
        'day' => isset($row['day']) ? (int)$row['day'] : null,
        'month' => isset($row['month']) ? $row['month'] : null,
        'total' => isset($row['total']) ? (float)$row['total'] : 0.0
    ];
}

echo json_encode($data);
?>
