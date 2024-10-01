<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}
require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    $sql_delete = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting user');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        body {
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }


        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 575.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
            }

            .container {
                margin-left: 0;
                margin-top: 20px;
                padding: 10px;
            }
        }

        /* Table adjustments */
        table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
            max-width: 100%;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            white-space: nowrap;
            /* ป้องกันการตัดบรรทัดใน header */
        }

        td {
            white-space: nowrap;
            /* ป้องกันการตัดบรรทัดในเนื้อหาของเซลล์ */
        }

        /* Adjust the email column to wrap if needed */
        td.email {
            word-wrap: break-word;
            max-width: 180px;
        }

        /* Button adjustments */
        .add-user-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            float: right;
            margin-right: 5%;
        }

        .btn-group {
            justify-content: flex-end;
            margin-right: 20px;
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

        @keyframes flipY {
            0% {
                transform: rotateY(0deg);
            }

            100% {
                transform: rotateY(360deg);
            }
        }

        .pharmacist-image {
            font-size: 100px;
            color: #fff;
            display: block;
            margin: 0 auto 20px;
            text-align: center;
            animation: flipY 3s infinite;
            /* หมุน 5 วินาที และสั่นทุกๆ 0.5 วินาที */
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
        <i class="fa-solid fa-user pharmacist-image"></i>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>ข้อมูลผู้ป่วย</h2>
        </div>
        <div class="btn-group">
            <a href="adduser.php" class="btn btn-success">เพิ่มผู้ใช้งาน</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ลำดับสมาชิก</th>
                    <th scope="col">ชื่อสมาชิก</th>
                    <th scope="col">วัน/เดือน/ปี</th>
                    <th scope="col">เบอร์โทรศัพท์</th>
                    <th scope="col">อีเมล</th>
                    <th scope="col">แก้ไข</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT user_id, full_name, birthday, phone_number, email FROM users";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>{$row['user_id']}</td>
                            <td>{$row['full_name']}</td>
                            <td>{$row['birthday']}</td>
                            <td>{$row['phone_number']}</td>
                            <td class='email'>{$row['email']}</td>
                            <td class='table-buttons'>
                                <a href='usermanage.php?user_id={$row['user_id']}' class='btn btn-success'>แก้ไข</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No members found</td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>