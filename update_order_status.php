<?php
header('Content-Type: application/json');
include 'connectdb.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตรวจสอบว่ามีการส่งข้อมูล order_id และ status
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // อัปเดตสถานะในฐานข้อมูล
    $sql = "UPDATE orders SET status_payment = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "ไม่สามารถอัปเดตสถานะได้"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "ข้อมูลไม่ถูกต้อง"]);
}
