<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function updateCart($conn, $updates)
{
    if (!is_array($updates) || empty($updates)) {
        return "No valid updates provided.";
    }

    $updateCases = [];
    $updateParams = [];
    $updateIds = [];

    foreach ($updates as $medicineId => $newQuantity) {
        $medicineId = intval($medicineId);
        $newQuantity = intval($newQuantity);

        if ($newQuantity <= 0) {
            // Remove item from cart if quantity is 0 or negative
            foreach ($_SESSION['cart'] as $index => $item) {
                if ($item['medicine_id'] == $medicineId) {
                    unset($_SESSION['cart'][$index]);
                    break;
                }
            }
        } else {
            $updateCases[] = "WHEN ? THEN ?";
            $updateParams[] = $medicineId;
            $updateParams[] = $newQuantity;
            $updateIds[] = $medicineId;

            // Update session cart
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['medicine_id'] == $medicineId) {
                    $item['quantity'] = $newQuantity;
                    break;
                }
            }
        }
    }

    if (empty($updateCases)) {
        return "No valid updates to process.";
    }

    // Prepare the SQL query for bulk update
    $sql = "UPDATE medicine SET stock = CASE medicine_id " .
        implode(' ', $updateCases) .
        " ELSE stock END WHERE medicine_id IN (" . implode(',', array_fill(0, count($updateIds), '?')) . ")";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        return "Failed to prepare statement: " . $conn->error;
    }

    // Bind parameters
    $types = str_repeat('ii', count($updateCases)) . str_repeat('i', count($updateIds));
    $stmt->bind_param($types, ...[...$updateParams, ...$updateIds]);

    // Execute the statement
    if (!$stmt->execute()) {
        return "Failed to update cart: " . $stmt->error;
    }

    $stmt->close();

    // Reindex the cart array
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    return "Cart updated successfully.";
}

$searchQuery = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE m.medicine_name LIKE '%$search%' ";
}

$sql = "SELECT m.*, GROUP_CONCAT(mt.name_time SEPARATOR ', ') AS timings
        FROM medicine m
        LEFT JOIN medicine_time mt ON m.medicine_id = mt.medicine_id
        $searchQuery
        GROUP BY m.medicine_id";
$result = $conn->query($sql);

$message = "";

function addToCart($medicine_id, $quantity)
{
    global $conn, $message;

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['medicine_id'] == $medicine_id) {
            $item['quantity'] += $quantity;
            $found = true;
            $message = "เพิ่มจำนวน " . htmlspecialchars($item['medicine_name']) . " อีก $quantity ชิ้นในตะกร้าของคุณแล้ว";
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
            $message = "เพิ่ม " . htmlspecialchars($medicine['medicine_name']) . " จำนวน $quantity ชิ้นลงในตะกร้าของคุณแล้ว";
        } else {
            $message = "ไม่พบยา";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity <= 0) {
            $message = "จำนวนสินค้าต้องมากกว่าศูนย์";
        } else {
            addToCart($medicine_id, $quantity);
        }
    }
}

if (isset($_POST['update_cart'])) {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        $message = updateCart($conn, $_POST['quantity']);
    } else {
        $message = "Invalid update data provided.";
    }
}

if (isset($_POST['remove_selected'])) {
    if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
        foreach ($_POST['selected_items'] as $medicine_id) {
            foreach ($_SESSION['cart'] as $index => $item) {
                if ($item['medicine_id'] == $medicine_id) {
                    unset($_SESSION['cart'][$index]);
                }
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        $message = "รายการที่เลือกถูกลบออกจากตะกร้าเรียบร้อยแล้ว";
    } else {
        $message = "กรุณาเลือกรายการที่ต้องการลบ";
    }
}


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - ซื้อยา</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/keyframes.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            padding-left: 220px;
            padding-top: 56px;
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
            margin-top: 20px;
        }

        .btn-outline-custom {
            border: 2px solid #007bff;
            color: #007bff;
        }

        .btn-outline-custom:hover {
            background-color: #007bff;
            color: white;
        }

        .card-text {
            position: relative;
        }

        .card-text #more {
            display: none;
        }

        .card-text #dots {
            display: inline;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
            <div>
                <a href="logout.php" class="btn btn-light">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <aside class="sidebar">
        <i class="fa-solid fa-store pharmacist-image"></i>
        <a href="index.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-home"></i> หน้าหลัก
        </a>
        <a href="medicine.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-pills"></i> ยา
        </a>
        <a href="buy.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-store"></i> ร้านค้า
        </a>
        <a href="users.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-users"></i> ผู้ใช้งาน
        </a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-user"></i> ข้อมูลส่วนตัว
        </a>
        <a href="online.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-comment-dots"></i> แชท
        </a>
        <a href="status.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-truck"></i> สถานะ
        </a>
    </aside>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <h2>ค้นหารายการยา</h2>
            <form method="get" action="buy.php">
                <div class="mb-3">
                    <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อยา">
                </div>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </form>
        </div>

        <h2>ตะกร้าสินค้า</h2>
        <div class="row mt-4">
            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="col-12">
                    <form method="POST" action="">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>เลือก</th>
                                    <th>ชื่อยา</th>
                                    <th>ราคา</th>
                                    <th>จำนวน</th>
                                    <th>รวม</th>
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
                                        <td>
                                            <input type="checkbox" name="selected_items[]" value="<?php echo $item['medicine_id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($item['medicine_name']); ?></td>
                                        <td>฿<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <input type="number" name="quantity[<?php echo $item['medicine_id']; ?>]"
                                                value="<?php echo $item['quantity']; ?>"
                                                min="0" class="form-control quantity-input"
                                                style="width: 70px;"
                                                data-price="<?php echo $item['price']; ?>">
                                        </td>
                                        <td class="item-total">฿<?php echo number_format($itemTotal, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4"><strong>รวมทั้งหมด</strong></td>
                                    <td id="cart-total">฿<?php echo number_format($totalAmount, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mb-3">
                            <button type="submit" name="remove_selected" class="btn btn-danger">ลบรายการที่เลือก</button>
                            <button type="submit" name="update_cart" class="btn btn-primary">อัปเดตตะกร้า</button>
                        </div>
                    </form>
                    <form action="Checkout.php" method="post">
                        <input type="submit" value="ชำระเงิน" class="btn btn-success">
                    </form>
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
                    echo '<p class="card-text">' . $shortDescription . '<span id="dots-' . $row["medicine_id"] . '"></span>';
                    echo '<span id="more-' . $row["medicine_id"] . '" style="display:none;">' . mb_substr($row["description"], 100) . '</span></p>';
                    echo '<button onclick="showMore(' . $row["medicine_id"] . ')" id="myBtn-' . $row["medicine_id"] . '" class="btn btn-link">อ่านเพิ่มเติม</button>';
                    echo '<p class="card-text"><strong>ราคา: ฿' . number_format((float)$row["price"], 2) . '</strong></p>';
                    echo '<p class="card-text"><strong>คงเหลือ: ' . htmlspecialchars($row["stock"]) . '</strong></p>';

                    // แสดงเวลาใช้ยา
                    if (!empty($row["timings"])) {
                        echo '<div>';
                        echo '<strong>เวลาใช้ยา:</strong>';
                        echo '<ul>';
                        $timings = explode(', ', $row["timings"]);
                        foreach ($timings as $timing) {
                            if ($timing !== 'other') {
                                echo '<li>' . htmlspecialchars($timing) . '</li>';
                            }
                        }
                        echo '</ul>';
                        echo '</div>';
                    } else {
                        echo '<p>ไม่มีข้อมูลเวลาใช้ยา</p>';
                    }

                    $image = $row["image"];
                    if (filter_var($image, FILTER_VALIDATE_URL)) {
                        echo '<img src="' . htmlspecialchars($image) . '" alt="ภาพยา">';
                    } else {
                        echo '<img src="data:image/*;base64,' . htmlspecialchars($image) . '" alt="ภาพยา">';
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
                btnText.innerHTML = "ย่อ";
                moreText.style.display = "inline";
            }
        }
    </script>
</body>

</html>