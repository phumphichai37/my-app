<?php
 // เริ่มใช้งาน session
require_once 'connectdb.php';

// ตรวจสอบว่ามีการเข้าสู่ระบบของเภสัชกรหรือไม่
if (isset($_SESSION['pharmacist'])) {
    $pharmacist_data = $_SESSION['pharmacist'];
    $pharmacist_id = $pharmacist_data['pharmacist_id'];
} else {
    // หากไม่พบข้อมูลเภสัชกร ให้ออกจากระบบหรือแสดงข้อผิดพลาด
    header("Location: logout.php");
    exit;
}

// ดึงข้อมูลรูปภาพเภสัชกรจากฐานข้อมูล
$sql = "SELECT image FROM pharmacist WHERE pharmacist_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $pharmacist_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $pharmacist = $result->fetch_assoc()) {
        // ถ้าพบรูปภาพในฐานข้อมูล
        $image_path = $pharmacist['image'] ? htmlspecialchars($pharmacist['image']) : 'asset/default_user_icon.png';
    } else {
        // กรณีไม่พบเภสัชกรหรือไม่มีข้อมูลรูปภาพ
        $image_path = 'asset/default_user_icon.png';
    }

    $stmt->close();
} else {
    // กรณีเกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL
    $image_path = 'asset/default_user_icon.png';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <title>Pharmacist Dashboard</title>
    <style>
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

        .pharmacist-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 3px solid #fff;
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
        <img src="<?php echo $image_path; ?>" alt="Pharmacist Image" class="pharmacist-image">
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
</body>

</html>
