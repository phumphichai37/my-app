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
            $imgData = file_get_contents($_FILES['file']['tmp_name']);
            $imgName = basename($_FILES['file']['name']);

            // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูลยาและรูปภาพ
            $sql = "INSERT INTO madicine (medicine_name, description, type, price, stock, image_data, image_name) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssdiss', $itemName, $itemDescription, $itemType, $itemPrice, $itemStock, $imgData, $imgName);

            if ($stmt->execute()) {
                $message = "Item '$itemName' added successfully!";
            } else {
                $message = "Failed to add item: " . $conn->error;
            }

            $stmt->close();
        } else {
            $message = "Error uploading image.";
        }
    }
}

// ดึงข้อมูลรูปภาพจากฐานข้อมูล
$sql = "SELECT medicine_name, description, image_data, image_name FROM madicine";
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="info.php" class="btn btn-secondary me-2">Info</a>
        <a href="users.php" class="btn btn-secondary me-2">User</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
        <a href="buy.php" class="btn btn-secondary me-2">Buy</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="logo">
            <img src="asset/logo.png" alt="Logo">
        </div>
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>

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
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6 medicine-item">';
                    echo '<div class="card text-black h-100">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row["medicine_name"] . '</h5>';
                    echo '<p class="card-text">' . $row["description"] . '</p>';
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($row["image_data"]) . '" alt="' . $row["image_name"] . '">';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p>No medicines found.</p></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
