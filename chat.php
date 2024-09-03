<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง ถ้ายังไม่เข้าสู่ระบบให้ไปที่หน้า login.php
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php'; // เชื่อมต่อฐานข้อมูล

// ฟังก์ชันดึงข้อมูลการสนทนา
function selectChat($userId, $pharmacistId) {
    global $conn;
    $query = "
        SELECT c.id as conversationId, m.id as messageId, m.sender_type, m.sender_id, m.text, m.image_url, m.created_at
        FROM conversations c
        LEFT JOIN messages m ON c.id = m.conversation_id
        WHERE c.user_id = ? AND c.pharmacist_id = ?
        ORDER BY m.created_at ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $pharmacistId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    return $messages;
}

// สมมติว่าคุณได้ค่า $userId จาก request หรือ session
$userId = 1; // สมมติค่า user_id (ควรได้มาจาก session หรือข้อมูลอื่นๆ)
$pharmacistId = $_SESSION['pharmacist']; // ใช้ pharmacist_id จาก session
$chats = selectChat($userId, $pharmacistId);

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
            background: #f8f9fa; /* Light grey background */
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
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
        }
        .chat-message p {
            margin: 0;
        }
        .chat-message.user {
            background-color: #e9f7fe;
            text-align: left;
        }
        .chat-message.pharmacist {
            background-color: #f0f0f0;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="users.php" class="btn btn-secondary me-2">User</a>
        <a href="medicine.php" class="btn btn-secondary me-2">Medicine</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
        <a href="info.php" class="btn btn-secondary me-2">Info</a>
        <a href="buy.php" class="btn btn-secondary me-2">Buy</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>  
    </div>
</body>
</html>

