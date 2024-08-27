<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รวมการเชื่อมต่อฐานข้อมูล
include 'connectdb.php';

$user_id = $_SESSION['pharmacist'];

// ดึงข้อมูลสินค้าจากตะกร้า
$sql = "SELECT c.cart_id, m.medicine_id, m.medicine_name, m.price, c.quantity, m.stock 
        FROM cart c
        JOIN madicine m ON c.medicine_id = m.medicine_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // เริ่มการทำธุรกรรม
    $conn->begin_transaction();

    $order_successful = true;

    while($row = $result->fetch_assoc()) {
        $new_stock = $row['stock'] - $row['quantity'];
        if ($new_stock >= 0) {
            $sql_update = "UPDATE madicine SET stock = ? WHERE medicine_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ii', $new_stock, $row['medicine_id']);
            if (!$stmt_update->execute()) {
                $order_successful = false;
                break;
            }
            $stmt_update->close();
        } else {
            $order_successful = false;
            break;
        }
    }

    if ($order_successful) {
        // ยืนยันการทำธุรกรรม
        $conn->commit();
        $message = "Order placed successfully!";
        // ลบสินค้าจากตะกร้า
        $sql_delete = "DELETE FROM cart WHERE user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param('i', $user_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    } else {
        // ยกเลิกการทำธุรกรรม
        $conn->rollback();
        $message = "Failed to place order. Please try again.";
    }
} else {
    $message = "Your cart is empty.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
    </div>
</body>
</html>
