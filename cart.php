<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}


include 'connectdb.php';

$user_id = $_SESSION['pharmacist']; 


$sql = "SELECT c.cart_id, m.medicine_name, m.price, c.quantity 
        FROM cart c
        JOIN madicine m ON c.medicine_id = m.medicine_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0;
                    while($row = $result->fetch_assoc()) {
                        $total = $row['price'] * $row['quantity'];
                        $total_price += $total;
                        echo "<tr>";
                        echo "<td>" . $row['medicine_name'] . "</td>";
                        echo "<td>$" . number_format($row['price'], 2) . "</td>";
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "<td>$" . number_format($total, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>$<?php echo number_format($total_price, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
            <a href="checkout.php" class="btn btn-primary">Checkout</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>
