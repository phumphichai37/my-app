<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รวมการเชื่อมต่อฐานข้อมูล
include 'connectdb.php';

// ตรวจสอบว่า user_id ถูกส่งมาหรือไม่
if (!isset($_GET['user_id'])) {
    die("User ID not provided.");
}

$user_id = intval($_GET['user_id']);

// เตรียมคำสั่ง SQL สำหรับดึงข้อมูลโปรไฟล์ผู้ใช้
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่ามีข้อมูลผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
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

        .profile-container {
            margin-top: 20px;
        }

        .profile-details {
            margin-bottom: 15px;
        }

        .profile-picture {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            border-radius: 50%;
            /* ทำให้เป็นวงกลม */
        }

        .profile-picture {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            border-radius: 50%;
            /* ทำให้เป็นวงกลม */
            background-color: #ddd;
            /* สีพื้นหลังเมื่อไม่มีรูปภาพ */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.2em;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="info.php" class="btn btn-secondary me-2">Info</a>
        <a href="medicine.php" class="btn btn-secondary me-2">Medicine</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
        <a href="buy.php" class="btn btn-secondary me-2">Buy</a>
    </div> -->
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="users.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
        <div class="container">
            <div class="profile-container">
                <div class="profile-details">
                    <strong>Profile Picture:</strong><br>
                    <?php if (!empty($user['image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture">
                            <span>Profile</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <strong>Username:</strong> <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <div class="profile-details">
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div class="profile-details">
                    <strong>Birthday:</strong> <?php echo htmlspecialchars($user['birthday']); ?>
                </div>
                <div class="profile-details">
                    <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?>
                </div>
                <div class="profile-details">
                    <strong>Description:</strong> <?php echo htmlspecialchars($user['description']); ?>
                </div>
                <a href="editprofile.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
</body>

</html>