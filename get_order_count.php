<?php
header('Content-Type: application/json');
include 'connectdb.php';

function getOrderCountByStatus($status) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM orders WHERE status_order = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

$pendingCount = getOrderCountByStatus("รอการอนุมัติ");
$preparingCount = getOrderCountByStatus("กำลังจัดเตรียมสินค้า");
$shippingCount = getOrderCountByStatus("กำลังจัดส่ง");

echo json_encode([
    'pending' => $pendingCount,
    'preparing' => $preparingCount,
    'shipping' => $shippingCount
]);
?>