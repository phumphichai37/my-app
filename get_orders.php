<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
ob_clean(); // เคลียร์ output buffer
include 'connectdb.php';


if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    // ฟังก์ชันดึงรายละเอียดออเดอร์
    function getOrderDetails($order_id)
    {
        global $conn;
        $sql = "SELECT o.*, u.full_name, GROUP_CONCAT(CONCAT(od.quantity, 'x ', m.medicine_name) SEPARATOR ', ') AS items
            FROM orders o
            JOIN order_details od ON o.order_id = od.order_id
            JOIN medicine m ON od.medicine_id = m.medicine_id
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.order_id = ?
            GROUP BY o.order_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    $orderDetails = getOrderDetails($order_id);
    if ($orderDetails) {
        echo json_encode($orderDetails); // ตรวจสอบว่า $orderDetails เป็นข้อมูลที่ถูกต้อง
    } else {
        echo json_encode(null); // ส่งค่า null ถ้าไม่พบออเดอร์
    }
} else {
    echo json_encode(['error' => 'No order_id provided']); // ส่งข้อความ error ถ้าไม่มีการส่ง order_id มา
}
