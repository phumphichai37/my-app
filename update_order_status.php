<?php
require_once 'connectdb.php'; // เชื่อมต่อฐานข้อมูล

// รับข้อมูล JSON ที่ส่งมาจาก JavaScript
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['order_id']) && isset($data['status_order'])) {
    $order_id = $data['order_id'];
    $status_order = $data['status_order'];

    // อัปเดตสถานะในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE orders SET status_order = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status_order, $order_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating status"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}

?>
