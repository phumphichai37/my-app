<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

$message = "";

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ตรวจสอบว่าผู้ใช้ได้คลิกซื้อสินค้าหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // การจัดการเพิ่มสินค้า
    if (isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity <= 0) {
            $message = "จำนวนสินค้าต้องมากกว่าศูนย์";
        } else {
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['medicine_id'] == $medicine_id) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $sql = "SELECT * FROM medicine WHERE medicine_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $medicine_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $medicine = $result->fetch_assoc();
                    $_SESSION['cart'][] = [
                        'medicine_id' => $medicine['medicine_id'],
                        'medicine_name' => $medicine['medicine_name'],
                        'price' => $medicine['price'],
                        'quantity' => $quantity,
                    ];
                    $message = "เพิ่ม " . htmlspecialchars($medicine['medicine_name']) . " จำนวน $quantity ลงในตะกร้าของคุณแล้ว";
                } else {
                    $message = "ไม่พบยา";
                }

                $stmt->close();
            }
        }
    }

    // การจัดการอัปเดตจำนวนสินค้า
    if (isset($_POST['update_quantity']) && isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity <= 0) {
            $message = "จำนวนสินค้าต้องมากกว่าศูนย์";
        } else {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['medicine_id'] == $medicine_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
            $message = "อัปเดตจำนวนสินค้าเรียบร้อยแล้ว";
        }
    }

    // การจัดการลบสินค้าออกจากตะกร้า
    if (isset($_POST['remove_from_cart']) && isset($_POST['medicine_id'])) {
        $medicine_id = $_POST['medicine_id'];

        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['medicine_id'] == $medicine_id) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                $message = "ลบสินค้าจากตะกร้าเรียบร้อยแล้ว";
                break;
            }
        }
    }
}

// ดึงข้อมูลยาทั้งหมดจากฐานข้อมูล
$sql = "SELECT * FROM medicine";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - ซื้อยา</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style2.css">
    <style>
        body {
            background: #f8f9fa;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px;
        }

        @media (max-width: 575.98px) {
            .container {
                max-width: 90%;
                padding: 10px;
            }
        }

        .sidebar {
            background-color: #F8F8FF;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            padding-top: 60px;
            overflow-x: hidden;
        }

        .sidebar .btn {
            margin: 10px;
            width: calc(100% - 20px);
        }

        .medicine-item {
            margin-bottom: 20px;
        }

        .medicine-item img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .btn-outline-custom {
            border: 2px solid #007bff;
            color: #007bff;
        }

        .btn-outline-custom:hover {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="โลโก้">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="info.php" class="btn btn-secondary me-2">ข้อมูล</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="admin.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="chat.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">ย้อนกลับ</a>
                <a href="logout.php" class="btn btn-warning">ออกจากระบบ</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>ตะกร้าสินค้า</h2>
        <div class="row mt-4">
            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="col-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อยา</th>
                                <th>ราคา</th>
                                <th>จำนวน</th>
                                <th>รวม</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalAmount = 0;
                            foreach ($_SESSION['cart'] as $index => $item):
                                $itemTotal = $item['price'] * $item['quantity'];
                                $totalAmount += $itemTotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                                    <td>฿<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="" class="form-inline">
                                            <input type="hidden" name="medicine_id" value="<?php echo $item['medicine_id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control">
                                            <input type="submit" name="update_quantity" value="อัปเดต" class="btn btn-outline-secondary mt-2">
                                        </form>
                                    </td>
                                    <td>฿<?php echo number_format($itemTotal, 2); ?></td>
                                    <td>
                                        <form method="POST" action="" class="form-inline">
                                            <input type="hidden" name="medicine_id" value="<?php echo $item['medicine_id']; ?>">
                                            <input type="submit" name="remove_from_cart" value="ลบ" class="btn btn-outline btn-danger">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3"><strong>รวมทั้งหมด</strong></td>
                                <td>฿<?php echo number_format($totalAmount, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="button-group">
                        <form method="POST" action="checkout.php">
                            <input type="submit" value="ชำระเงิน" class="btn btn-success">
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-12">
                    <p>ตะกร้าของคุณว่างเปล่า</p>
                </div>
            <?php endif; ?>
        </div>

        <h2>เลือกยา</h2>
        <div class="row mt-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6 medicine-item">';
                    echo '<div class="card text-black h-100">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row["medicine_name"]) . '</h5>';
                    $shortDescription = mb_substr($row["description"], 0, 100);
                    echo '<p class="card-text">' . $shortDescription . '<span id="dots-' . $row["medicine_id"] . '">...</span>';
                    echo '<span id="more-' . $row["medicine_id"] . '" style="display:none;">' . mb_substr($row["description"], 100) . '</span></p>';
                    echo '<button onclick="showMore(' . $row["medicine_id"] . ')" id="myBtn-' . $row["medicine_id"] . '" class="btn btn-link">อ่านเพิ่มเติม</button>';
                    echo '<p class="card-text"><strong>ราคา: ฿' . number_format((float)$row["price"], 2) . '</strong></p>';
                    echo '<p class="card-text"><strong>คงเหลือ: ' . htmlspecialchars($row["stock"]) . '</strong></p>';
                    $image = $row["image"];

                    if (preg_match('/^data:image\/(\w+);base64,/', $image)) {
                        echo '<img src="' . htmlspecialchars($image) . '" alt="ภาพยา">';
                    } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
                        echo '<img src="' . htmlspecialchars($image) . '" alt="ภาพยา">';
                    } else {
                        echo '<img src="path/to/placeholder.jpg" alt="รูปภาพไม่ถูกต้อง">';
                    }
                    echo '<form method="POST" action="" class="mt-2">';
                    echo '<input type="hidden" name="medicine_id" value="' . $row["medicine_id"] . '">';
                    echo '<input type="number" name="quantity" value="1" min="1" class="form-control mb-2" style="width: 100px;">';
                    echo '<input type="submit" class="btn btn-success" value="ซื้อทันที">';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p>ไม่พบข้อมูลยา</p></div>';
            }
            ?>
        </div>
    </div>
    <script>
        function showMore(id) {
            var dots = document.getElementById("dots-" + id);
            var moreText = document.getElementById("more-" + id);
            var btnText = document.getElementById("myBtn-" + id);

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                btnText.innerHTML = "อ่านเพิ่มเติม";
                moreText.style.display = "none";
            } else {
                dots.style.display = "none";
                btnText.innerHTML = "แสดงน้อยลง";
                moreText.style.display = "inline";
            }
        }
    </script>
</body>

</html>
