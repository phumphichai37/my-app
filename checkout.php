<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

function insertOrder($conn, $status_payment, $total_price, $payment_info, $status_noti, $t_id)
{
    // สร้างหมายเลขสุ่ม 14 หลัก
    $o_id = sprintf('%014d', random_int(0, 99999999999999));

    $orderQuery = "
        INSERT INTO orders (o_id, status_payment, total_price, payment_info, status_noti, t_id, order_time, create_at)
        VALUES (?, ?, ?, ?, ?, ?, CONVERT_TZ(NOW(), @@global.time_zone, '+07:00'), CONVERT_TZ(NOW(), @@global.time_zone, '+07:00'))
    ";

    try {
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param("ssdsss", $o_id, $status_payment, $total_price, $payment_info, $status_noti, $t_id);
        $stmt->execute();

        return $stmt->insert_id;
    } catch (Exception $e) {
        error_log("Error inserting order: " . $e->getMessage());
        throw new Exception('Internal server error');
    }
}


function insertOrderDetails($conn, $order_id, $items)
{
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

                $user_id = $_SESSION['pharmacist'];
                $status_payment = $_POST['status_payment'];
                $total_price = (float) $_POST['total_price'];
                $payment_info = $_POST['payment_info'];
                $status_noti = $_POST['status_noti'];
                $t_id = $_POST['t_id'];

                $order_id = insertOrder($conn, $status_payment, $total_price, $payment_info, $status_noti, $t_id);
                insertOrderDetails($conn, $order_id, $_SESSION['cart']);

                unset($_SESSION['cart']);

                $conn->commit();
                $message = "การชำระเงินเสร็จสิ้นแล้ว";

                // Add JavaScript to display alert and redirect after confirmation
                echo "<script>
                    alert('การชำระเงินเสร็จสิ้นแล้ว');
                    window.location.href = 'status.php';
                </script>";
                exit(); // Ensure no further code is executed after the redirect
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/crc-32@1.2.0/crc32.min.js"></script>
    <script src="promptpay-qr-generator.js"></script>
    <link rel="stylesheet" href="css/style.css">
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
                                <td>฿<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>฿<?php echo number_format($itemTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td>฿<?php echo number_format($totalAmount, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
                <form method="POST" action="" onsubmit="return validatePaymentMethod()">
                    <div class="mb-3">
                        <label class="form-label">วิธีการชำระเงิน</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_info" id="payment_transfer"
                                    value="โอนเงิน" <?php echo isset($_POST['payment_info']) && $_POST['payment_info'] === 'โอนเงิน' ? 'checked' : ''; ?> onclick="toggleQRCode('show')">
                                <label class="form-check-label" for="payment_transfer">
                                    โอนเงิน
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_info" id="payment_cash"
                                    value="เงินสด" <?php echo isset($_POST['payment_info']) && $_POST['payment_info'] === 'เงินสด' ? 'checked' : ''; ?> onclick="toggleQRCode('hide')">
                                <label class="form-check-label" for="payment_cash">
                                    เงินสด
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="total_price" value="<?php echo number_format($totalAmount, 2); ?>">
                    <input type="hidden" name="status_payment" value="รอการอนุมัติ">
                    <input type="hidden" name="status_noti" value="รอการแจ้งเตือน">
                    <input type="hidden" name="t_id" value="1">
                    <input type="submit" name="checkout" value="Checkout" class="btn btn-success">
                </form>

                <!-- PromptPay QR Code Container -->
                <img id="qrcode" src="https://promptpay.io/0640219417/<?php echo $totalAmount ?>.png"
                    style="display: none;">

                <script>
                    function toggleQRCode(action) {
                        var qrcode = document.getElementById('qrcode');
                        if (action === 'show') {
                            qrcode.style.display = 'block';
                        } else {
                            qrcode.style.display = 'none';
                        }
                    }

                    function validatePaymentMethod() {
                        var paymentTransfer = document.getElementById('payment_transfer').checked;
                        var paymentCash = document.getElementById('payment_cash').checked;

                        if (!paymentTransfer && !paymentCash) {
                            alert('กรุณาเลือกวิธีการชำระเงิน');
                            return false; // หยุดการส่งฟอร์ม
                        }
                        return true; // ถ้าเลือกวิธีการชำระเงินแล้ว ให้ส่งฟอร์มต่อไป
                    }



                </script>




            <?php else: ?>
                <p>ไม่มีสินค้าที่อยู่ในตะกร้า</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>