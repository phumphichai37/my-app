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
        table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }
        table th,
        table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }
        table tbody + tbody {
            border-top: 2px solid #dee2e6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="info.php" class="btn btn-secondary me-2">ข้อมูล</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>ข้อมูลผู้ป่วย</h2>
            <div>
                <a href="adduser.php" class="btn btn-success">เพิ่มผู้ใช้งาน</a>
            </div>
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
                    <th scope="col">ลบ</th>
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
                            <td>{$row['email']}</td>
                            <td><a href='usermanage.php?user_id={$row['user_id']}' class='btn btn-success'>แก้ไข</a></td>
                            <td>
                                <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>
                                    <input type='hidden' name='delete_user_id' value='{$row['user_id']}'>
                                    <button type='submit' class='btn btn-danger'>ลบ</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No members found</td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
