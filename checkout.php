<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

$message = "";

// ตรวจสอบการชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่ามีการชำระเงิน
    if (isset($_POST['checkout'])) {
        // ตรวจสอบว่ามีตะกร้าสินค้า
        if (!empty($_SESSION['cart'])) {
            // เริ่มต้นการทำธุรกรรม
            $conn->begin_transaction();

            try {
                // เพิ่มข้อมูลการสั่งซื้อ
                $sql = "INSERT INTO orders (order_date) VALUES (NOW())";
                $conn->query($sql);
                $order_id = $conn->insert_id;

                // เพิ่มรายการในตาราง orders_items
                foreach ($_SESSION['cart'] as $item) {
                    $sql = "INSERT INTO orders_items (order_id, medicine_id, medicine_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iisis", $order_id, $item['medicine_id'], $item['medicine_name'], $item['price'], $item['quantity']);
                    $stmt->execute();
                }

                // เคลียร์ตะกร้าสินค้า
                unset($_SESSION['cart']);

                // ทำธุรกรรมสำเร็จ
                $conn->commit();
                $message = "การชำระเงินเสร็จสิ้นแล้ว";
            } catch (Exception $e) {
                // ทำธุรกรรมล้มเหลว
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
                    <input type="submit" name="checkout" value="Checkout" class="btn btn-success">
                </form>
            <?php else: ?>
                <p>ไม่มีสินค้าที่อยู่ในตะกร้า</p>
            <?php endif; ?>
        </div>
</body>

</html>