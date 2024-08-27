<?php
include 'connectdb.php';  // ไฟล์เชื่อมต่อฐานข้อมูล

$query = "SELECT itemName, itemStock FROM medicine";  // คำสั่ง SQL ดึงข้อมูล
$result = $conn->query($query);

$data = array();
foreach ($result as $row) {
    $data[] = $row;
}

$result->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data);  // ส่งข้อมูลออกมาในรูปแบบ JSON
?>
