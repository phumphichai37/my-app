<?php
session_start();


if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_name = $_POST['name'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน

    // รับข้อมูล ID ผู้ใช้จากเซสชัน
    $user_id = $_SESSION['pharmacist']['id'];

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE pharmacists SET name=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_name, $new_password, $user_id);

    if ($stmt->execute()) {
        // อัปเดตเซสชันด้วยข้อมูลใหม่
        $_SESSION['pharmacist']['name'] = $new_name;

        echo "Profile updated successfully!";
        header("Location: admin.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
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
            /* Light grey background */
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
            /* Adjust width to fit within the sidebar */
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="info.php" class="btn btn-secondary me-2">ข้อมูล</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="chat.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="logo">
            <img src="asset/logo.png" alt="Logo">
        </div>
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">ย้อนกลับ</a>
                <a href="logout.php" class="btn btn-warning">ออกจากระบบ</a>
            </div>
        </div>

        <!-- Add this form for updating name and password -->
        <h2>Edit Profile</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="name" class="form-label">New Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>