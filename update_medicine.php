<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['pharmacist'])) {
    echo "Unauthorized access!";
    exit();
}

// เชื่อมต่อฐานข้อมูล
include 'connectdb.php';

// ตรวจสอบว่ามีข้อมูล POST ส่งเข้ามาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['medicineId'], $_POST['editItemName'], $_POST['editItemDescription'], $_POST['editItemType'], $_POST['editItemPrice'], $_POST['editItemStock'])) {
        $medicineId = intval($_POST['medicineId']);
        $itemName = $_POST['editItemName'];
        $itemDescription = $_POST['editItemDescription'];
        $itemType = $_POST['editItemType'];
        $itemPrice = floatval($_POST['editItemPrice']);
        $itemStock = intval($_POST['editItemStock']);

        // SQL สำหรับการอัปเดตข้อมูล
        $sql = "UPDATE medicine SET medicine_name = ?, description = ?, type = ?, price = ?, stock = ? WHERE medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssdis', $itemName, $itemDescription, $itemType, $itemPrice, $itemStock, $medicineId);

        if ($stmt->execute()) {
            echo "แก้ไขข้อมูลสำเร็จ";
        } else {
            echo "เกิดข้อผิดพลาด: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

$conn->close();
?>
