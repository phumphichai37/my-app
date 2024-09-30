<?php
// Start the session at the top of the script
session_start();

// Include your database connection file
include 'connectdb.php'; // Ensure this path is correct

// Check if pharmacist session data is set
if (!isset($_SESSION['pharmacist'])) {
    die("Pharmacist session is not set. Please log in again.");
}

$pharmacist_data = $_SESSION['pharmacist'];
$pharmacist_id = $pharmacist_data['pharmacist_id'];

// Query to fetch pharmacist image
$sql = "SELECT image FROM pharmacist WHERE pharmacist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacist_id);
$stmt->execute();
$result = $stmt->get_result();
$pharmacist = $result->fetch_assoc();
$image_path = $pharmacist['image'] ?? 'asset/default_user_icon.png';

// Query to fetch users with their profile pictures
$query = "SELECT user_id, full_name, IFNULL(image, 'asset/default_user_icon.png') AS image FROM users";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .user-list {
            list-style-type: none;
            padding: 0;
        }

        .user-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }

        .user-avatar-container {
            width: 80px;
            height: 80px;
            margin: 10px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .user-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 15px;
        }

        .user-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .chat-button {
            margin-left: auto;
            margin-right: 15px;
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
        <i class="fa-solid fa-comment-dots pharmacist-image"></i>
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

    <div class="container mt-4">
        <h2>ผู้ใช้ที่มีการสนทนา</h2>
        <ul class="user-list">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <a href="chat.php?user_id=<?= htmlspecialchars($row['user_id']) ?>" style="text-decoration: none; color: inherit;">
                    <li class="user-item">
                        <div class="user-avatar-container">
                            <img src="data:image/*;base64,<?= htmlspecialchars($row['image']) ?>" alt="Profile Picture" class="user-avatar">
                        </div>
                        <div class="user-info">
                            <div class="user-name">
                                <?= htmlspecialchars($row['full_name']) ?>
                            </div>
                        </div>
                    </li>
                </a>
            <?php } ?>
        </ul>
    </div>
</body>

</html>