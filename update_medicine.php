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

        // เริ่ม transaction
        $conn->begin_transaction();

        try {
            // อัปเดตข้อมูลในตาราง medicine
            $sql = "UPDATE medicine SET medicine_name = ?, description = ?, type = ?, price = ?, stock = ? WHERE medicine_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssdii', $itemName, $itemDescription, $itemType, $itemPrice, $itemStock, $medicineId);
            $stmt->execute();

            // ลบข้อมูลเวลาใช้ยาเก่า
            $deleteSql = "DELETE FROM medicine_time WHERE medicine_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $medicineId);
            $deleteStmt->execute();

            // เพิ่มข้อมูลเวลาใช้ยาใหม่
            if (isset($_POST['editItemTiming'])) {
                $timings = $_POST['editItemTiming'];
                $insertSql = "INSERT INTO medicine_time (medicine_id, name_time) VALUES (?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                foreach ($timings as $timing) {
                    if ($timing === 'other') {
                        $otherTiming = isset($_POST['editOtherTimingInput']) ? trim($_POST['editOtherTimingInput']) : '';
                        if (!empty($otherTiming)) {
                            $insertStmt->bind_param("is", $medicineId, $otherTiming);
                            $insertStmt->execute();
                        }
                    } else {
                        $insertStmt->bind_param("is", $medicineId, $timing);
                        $insertStmt->execute();
                    }
                }
            }

            // Commit transaction
            $conn->commit();
            echo "แก้ไขข้อมูลสำเร็จ";
        } catch (Exception $e) {
            // Rollback ในกรณีที่เกิดข้อผิดพลาด
            $conn->rollback();
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }

        $stmt->close();
        if (isset($deleteStmt)) $deleteStmt->close();
        if (isset($insertStmt)) $insertStmt->close();
    } else {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

$conn->close();
