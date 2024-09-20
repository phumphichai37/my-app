<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รวมการเชื่อมต่อฐานข้อมูล
include 'connectdb.php';

$message = "";

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่ามีการส่งข้อมูลในฟอร์ม
    if (isset($_POST['itemName'], $_POST['itemDescription'], $_POST['itemType'], $_POST['itemPrice'], $_POST['itemStock'])) {
        $itemName = $_POST['itemName'];
        $itemDescription = $_POST['itemDescription'];
        $itemType = $_POST['itemType'];
        $itemPrice = $_POST['itemPrice'];
        $itemStock = $_POST['itemStock'];

        // จัดการการอัปโหลดรูปภาพ
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['file']['tmp_name']);
            if (strpos($fileType, 'image/') === 0) {
                // อ่านข้อมูลรูปภาพ
                $imgData = file_get_contents($_FILES['file']['tmp_name']);
                // เข้ารหัสรูปภาพเป็น Base64
                $image = 'data:' . $fileType . ';base64,' . base64_encode($imgData);

                // เพิ่มข้อมูลลงในฐานข้อมูล
                $sql = "INSERT INTO medicine (medicine_name, description, type, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssdis', $itemName, $itemDescription, $itemType, $itemPrice, $itemStock, $image);

                if ($stmt->execute()) {
                    $message = "Item '$itemName' added successfully!";
                } else {
                    $message = "Failed to add item: " . $conn->error;
                }

                $stmt->close();
            } else {
                $message = "Uploaded file is not an image.";
            }
        } else {
            $message = "Error uploading image: " . $_FILES['file']['error'];
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql = "DELETE FROM medicine WHERE medicine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $message = "Item deleted successfully!";
        echo "<script>window.location.href = 'medicine.php';</script>";
    } else {
        $message = "Failed to delete item: " . $conn->error;
    }

    $stmt->close();

    exit();
}

$sql = "SELECT * FROM medicine";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        body {
            margin: 0;
            padding: 0;
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
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
    </aside>

    <div class="container">
        <div class="mb-4">
            <h2>เพิ่มรายการยา</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="itemName" class="form-label">ชื่อยา</label>
                    <input type="text" class="form-control" id="itemName" name="itemName" required>
                </div>
                <div class="mb-3">
                    <label for="itemDescription" class="form-label">รายละเอียด</label>
                    <input type="text" class="form-control" id="itemDescription" name="itemDescription" required>
                </div>
                <div class="mb-3">
                    <label for="itemType" class="form-label">ประเภทยา</label>
                    <input type="text" class="form-control" id="itemType" name="itemType" required>
                </div>
                <div class="mb-3">
                    <label for="itemPrice" class="form-label">ราคา</label>
                    <input type="number" step="0.01" class="form-control" id="itemPrice" name="itemPrice" required>
                </div>
                <div class="mb-3">
                    <label for="itemStock" class="form-label">จำนวนสินค้า</label>
                    <input type="number" class="form-control" id="itemStock" name="itemStock" required>
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">เลือกรูปภาพ</label>
                    <input type="file" class="form-control" name="file" id="file" accept="image/*">
                </div>
                <input type="submit" class="btn btn-primary" value="เพิ่มข้อมูล">
            </form>
        </div>

        <div class="row mt-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6 medicine-item">';
                    echo '<div class="card text-black h-100">';
                    echo '<div class="card-body">';
                    echo '<div class="d-flex justify-content-end">';
                    echo '<a href="javascript:void(0)" onclick="deleteItem(' . $row["medicine_id"] . ', event)" class="btn btn-danger mt-2">ลบ</a>';
                    echo '</div>';
                    echo '<h5 class="card-title">' . $row["medicine_name"] . '</h5>';
                    $shortDescription = mb_substr($row["description"], 0, 100);
                    echo '<p class="card-text">' . $shortDescription . '<span id="dots-' . $row["medicine_id"] . '">...</span>';
                    echo '<span id="more-' . $row["medicine_id"] . '" style="display:none;">' . mb_substr($row["description"], 100) . '</span>';
                    echo '<a href="javascript:void(0)" onclick="showMore(' . $row["medicine_id"] . ', event)" id="readMoreBtn-' . $row["medicine_id"] . '"> อ่านเพิ่มเติม</a></p>';

                    // ตรวจสอบว่าเป็นรูปภาพ Base64 หรือ URL
                    if (preg_match('/^data:image\/(\w+);base64,/', $row["image"])) {
                        // แสดงผลรูปภาพ Base64
                        echo '<img src="' . $row["image"] . '" alt="Image" style="max-width:100%; height:auto;">';
                    } elseif (filter_var($row["image"], FILTER_VALIDATE_URL)) {
                        // แสดงผลรูปภาพที่เป็น URL
                        echo '<img src="' . $row["image"] . '" alt="Image" style="max-width:100%; height:auto;">';
                    } else {
                        // กรณีไม่มีรูปภาพ
                        echo '<img src="placeholder.jpg" alt="No Image" style="max-width:100%; height:auto;">';
                    }

                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>ไม่มีรายการยา</p>';
            }
            ?>
        </div>
    </div>

    <script>
        function showMore(id, event) {
            event.preventDefault(); // ป้องกันการเลื่อนหน้าด้วย
            var dots = document.getElementById("dots-" + id);
            var moreText = document.getElementById("more-" + id);
            var btnText = document.getElementById("readMoreBtn-" + id);

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                btnText.innerHTML = " อ่านเพิ่มเติม";
                moreText.style.display = "none";
            } else {
                dots.style.display = "none";
                btnText.innerHTML = " ซ่อนข้อความ";
                moreText.style.display = "inline";
            }
        }

        function deleteItem(id, event) {
            event.preventDefault(); // ป้องกันการเลื่อนหน้าด้วย
            if (confirm('Are you sure you want to delete this item?')) {
                window.location.href = 'medicine.php?delete=' + id;
            }
        }
    </script>
</body>

</html>
