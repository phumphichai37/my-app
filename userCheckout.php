<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : null;
    // ทำการจัดการอื่น ๆ ตามที่ต้องการ
}

include 'connectdb.php';

function insertOrder($conn, $user_id, $status_payment, $total_price, $payment_info, $status_noti, $t_id)
{
    // สร้างหมายเลขสุ่ม 14 หลักสำหรับ o_id
    $o_id = sprintf('%014d', random_int(0, 99999999999999));

    // เพิ่ม user_id เข้าไปในคำสั่ง SQL
    $orderQuery = "
        INSERT INTO orders (o_id, user_id, status_payment, total_price, payment_info, status_noti, t_id, order_time, create_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, CONVERT_TZ(NOW(), @@global.time_zone, '+07:00'), CONVERT_TZ(NOW(), @@global.time_zone, '+07:00'))
    ";

    try {
        // ปรับการ bind_param ให้รองรับ user_id ด้วย
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param("issdsss", $o_id, $user_id, $status_payment, $total_price, $payment_info, $status_noti, $t_id);
        $stmt->execute();

        return $stmt->insert_id; // Return the inserted order ID
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
$user_full_name = ""; // Initialize full name variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['checkout'])) {
        if (!empty($_SESSION['cart'])) {
            try {
                $conn->begin_transaction();

                $user_id = $_SESSION['user_id']; // หรือค่าที่ได้จาก session หรือฐานข้อมูล
                $status_payment = $_POST['status_payment'];
                $total_price = (float) $_POST['total_price'];
                $payment_info = $_POST['payment_info'];
                $status_noti = $_POST['status_noti'];
                $t_id = $_POST['t_id'];

                $order_id = insertOrder($conn, $user_id, $status_payment, $total_price, $payment_info, $status_noti, $t_id);
                insertOrderDetails($conn, $order_id, $_SESSION['cart']);

                unset($_SESSION['cart']); // Clear the cart after successful checkout

                $conn->commit();
                $message = "การชำระเงินเสร็จสิ้นแล้วสำหรับผู้ใช้ ID: $user_id";

                echo "<script>
                    alert('การชำระเงินเสร็จสิ้นแล้วสำหรับผู้ใช้ ID: $user_id');
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
    } else if (isset($_POST['user_id'])) {
        // Fetch user full name when user_id is provided
        $user_id = (int) $_POST['user_id'];
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_full_name);
        $stmt->fetch();
        $stmt->close();
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
                <form method="POST" action="" id="checkout-form">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User ID</label>
                        <input type="number" class="form-control" id="user_id" name="user_id" required
                            value="<?php echo isset($user_id) ? $user_id : ''; ?>">
                    </div>

                    <?php if (!empty($user_full_name)): ?>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name"
                                value="<?php echo htmlspecialchars($user_full_name); ?>" readonly>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">วิธีการชำระเงิน</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_info" id="payment_cash"
                                    value="เก็บเงินปลายทาง" <?php echo isset($_POST['payment_info']) && $_POST['payment_info'] === 'เก็บเงินปลายทาง' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="payment_cash">เก็บเงินปลายทาง</label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="total_price" value="<?php echo number_format($totalAmount, 2); ?>">
                    <input type="hidden" name="status_payment" value="รอการอนุมัติ">
                    <input type="hidden" name="status_noti" value="รอการแจ้งเตือน">
                    <input type="hidden" name="t_id" value="1">
                    <input type="submit" name="checkout" value="Checkout" class="btn btn-success">
                </form>

                <script>
                    document.getElementById('checkout-form').addEventListener('submit', function (event) {
                        const paymentInfo = document.querySelector('input[name="payment_info"]:checked');
                        if (!paymentInfo) {
                            alert('กรุณาเลือกวิธีการชำระเงิน');
                            event.preventDefault(); // ป้องกันการส่งฟอร์มถ้ายังไม่ได้เลือก
                        }
                    });
                </script>
                
            <?php else: ?>
                <p>ไม่มีสินค้าที่อยู่ในตะกร้า</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>