<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

// ฟังก์ชันที่แปลงมาใหม่
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
                $user_id = $_SESSION['pharmacist'];  // แทนที่ด้วยค่าที่เหมาะสม
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


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="buy.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
        <div class="container mt-5">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <h2>Your Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่อยา</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalAmount = 0;
                        foreach ($_SESSION['cart'] as $item):
                            $itemTotal = $item['price'] * $item['quantity'];
                            $totalAmount += $itemTotal;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($itemTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td>$<?php echo number_format($totalAmount, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status_payment" class="form-label">วิธีการชำระเงิน</label>
                        <select id="status_payment" name="status_payment" class="form-select">
                            <option value="โอนเงิน">โอนเงิน</option>
                            <option value="เงินสด">เงินสด</option>
                        </select>
                    </div>
                    <input type="hidden" name="total_price" value="<?php echo number_format($totalAmount, 2); ?>">
                    <input type="hidden" name="payment_info" value="ชำระเงินเรียบร้อย">
                    <input type="submit" name="checkout" value="Checkout" class="btn btn-success">
                </form>
            <?php else: ?>
                <p>ไม่มีสินค้าที่อยู่ในตะกร้า</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
