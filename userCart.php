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


if (isset($_GET['user_id'])) {
    $_SESSION['user_id'] = (int)$_GET['user_id'];
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

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // ดึงข้อมูลรายการยาที่มีชื่อเท่ากับหรือมีคำค้นอยู่ในชื่อ
    $query = "SELECT * FROM medicine WHERE medicine_name = ? OR medicine_name LIKE ?";
    $stmt = $conn->prepare($query);
    $searchParam = "%" . $search . "%"; // สำหรับการค้นหาที่มีคำค้นอยู่ในชื่อ
    $stmt->bind_param("ss", $search, $searchParam); // binding แบบสองค่า
    $stmt->execute();
    $result = $stmt->get_result();

    // แสดงผลลัพธ์การค้นหา
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div>{$row['medicine_name']}</div>"; // แสดงชื่อยา
        }
    } else {
        echo "<div>ไม่พบรายการยา</div>";
    }
    exit; // สิ้นสุดสคริปต์ที่นี่เพื่อไม่ให้แสดงเนื้อหาที่ไม่ต้องการ
}



// ดึงข้อมูลยาทั้งหมดจากฐานข้อมูล รวมเวลาการใช้ยา
$sql = "SELECT m.*, GROUP_CONCAT(mt.name_time SEPARATOR ', ') AS timings
        FROM medicine m
        LEFT JOIN medicine_time mt ON m.medicine_id = mt.medicine_id
        GROUP BY m.medicine_id";
$result = $conn->query($sql);

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - ซื้อยา</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .navbar-info {
            background-color: #17a2b8;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 10px;
        }

        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            width: 220px;
            height: calc(100% - 56px);
            background-color: rgba(23, 162, 184, 0.9);
            border-right: 1px solid #ddd;
            z-index: 1000;
            overflow-y: auto;
            padding-top: 20px;
        }

        .sidebar .btn {
            background-color: #17a2b8;
            border: none;
            color: #fff;
            margin: 10px;
            width: calc(100% - 20px);
        }

        .sidebar .btn:hover {
            background-color: #138496;
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
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
        <a href="selectUser.php" class="btn btn-secondary me-2">เพิ่มสินค้า</a>
    </aside>

    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <h2>ค้นหารายการยา</h2>
            <form id="searchForm" method="get" action="buy.php">
                <div class="mb-3">
                    <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อยา" id="searchInput">
                </div>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </form>
        </div>

        <div id="searchResults"></div>

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
                    <form method="POST" action="userCheckout.php">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
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
                    if (preg_match('/^data:image\/(\w+);base64,/', $image)) {
                        echo '<img src="' . htmlspecialchars($image) . '" alt="ภาพยา">';
                    } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
                        echo '<img src="' . htmlspecialchars($image) . '" alt="ภาพยา">';
                    } else {
                        echo '<img src="path/to/placeholder.jpg" alt="รูปภาพไม่ถูกต้อง">';
                    }

                    echo '<form method="POST" action="" class="mt-2">';
                    echo '<input type="hidden" name="medicine_id" value="' . $row["medicine_id"] . '">';
                    echo '<input type="number" name="quantity" value="1" min="1" class="form-control mb-2" style="width: 70px;">';
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
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault(); // หยุดการส่งฟอร์มแบบปกติ

            const searchValue = document.getElementById('searchInput').value;

            fetch('buy.php?search=' + encodeURIComponent(searchValue))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data; // อัปเดตเนื้อหาใน modal
                })
                .catch(error => console.error('Error:', error));
        });

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