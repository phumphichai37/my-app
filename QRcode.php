<?php
function insertOrder($conn, $status_payment, $total_price, $payment_info) {
    $orderQuery = "
        INSERT INTO orders (status_payment, total_price, payment_info, order_time, create_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ";
    
    try {
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param("sds", $status_payment, $total_price, $payment_info); // ใช้ sds เพื่อรองรับค่าที่เป็นข้อความ
        $stmt->execute();
        
        return $stmt->insert_id;
    } catch (Exception $e) {
        error_log("Error inserting order: " . $e->getMessage());
        throw new Exception('Internal server error');
    }
}

function insertOrderDetails($conn, $order_id, $items) {
    $itemQuery = "
        INSERT INTO order_details (order_id, medicine_id, quantity)
        VALUES (?, ?, ?)
    ";
    
    try {
        $stmt = $conn->prepare($itemQuery);

        foreach ($items as $item) {
            $stmt->bind_param("iii", $order_id, $item['medicine_id'], $item['quantity']);
            $stmt->execute();
        }

        return true;
    } catch (Exception $e) {
        error_log("Error inserting order details: " . $e->getMessage());
        throw new Exception('Internal server error');
    }
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['checkout'])) {
        if (!empty($_SESSION['cart'])) {
            try {
                $conn->begin_transaction();

                // ข้อมูลที่จำเป็นสำหรับการสร้างคำสั่งซื้อ
                $status_payment = $_POST['status_payment']; // รับค่าจากฟอร์ม
                $total_price = (float)$_POST['total_price']; // รับค่าจากฟอร์ม
                $payment_info = $_POST['payment_info']; // รับค่าจากฟอร์ม

                $order_id = insertOrder($conn, $status_payment, $total_price, $payment_info);
                insertOrderDetails($conn, $order_id, $_SESSION['cart']);

                unset($_SESSION['cart']);

                $conn->commit();
                $message = "การชำระเงินเสร็จสิ้นแล้ว";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "เกิดข้อผิดพลาดในการชำระเงิน: " . $e->getMessage();
            }
        } else {
            $message = "ตะกร้าสินค้าว่างเปล่า";
        }
    }
}

$conn->close();
?>
