<?php
require 'connectdb.php';
session_start();

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['pharmacist_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาหรือไม่
if (!isset($_GET['user_id'])) {
    die("Error: No user selected for the chat.");
}

$userId = $_GET['user_id'];
$pharmacistId = $_SESSION['pharmacist_id'];

// ดึงประวัติการสนทนา
$query = "
  SELECT m.sender_type, m.text, m.created_at, m.image 
  FROM messages m
  JOIN conversations c ON m.conversation_id = c.id
  WHERE c.user_id = ? AND c.pharmacist_id = ?
  ORDER BY m.created_at ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $pharmacistId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
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
            margin: 20px auto;
            max-width: 800px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .chat-window {
            height: 400px;
            overflow-y: auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .message {
            margin-bottom: 10px;
            display: flex;
        }

        .message.user .text {
            background-color: #e0f7fa;
            color: #007bff;
            margin-left: auto;
            border-radius: 15px 15px 0 15px;
        }

        .message.pharmacist .text {
            background-color: #f1f1f1;
            color: #333;
            margin-right: auto;
            border-radius: 15px 15px 15px 0;
        }

        .message .text {
            padding: 10px 15px;
            max-width: 60%;
        }

        .message .timestamp {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }

        .message .image {
            margin-top: 10px;
            max-width: 300px;
            overflow: hidden;
            text-align: center;
        }

        .message .image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            margin: 0 auto;
        }

        form {
            margin-top: 20px;
        }

        .input-group {
            border-radius: 30px;
            overflow: hidden;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 0;
            padding: 15px;
        }

        .btn-info {
            border-radius: 0;
            padding: 15px 30px;
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

        .btn-secondary {
            background-color: #6c757d;
        }

        label[for="file-input"] {
            cursor: pointer;
        }

        label[for="file-input"] i {
            font-size: 1.5rem;
            color: #007bff;
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
        <a href="index.php" class="btn btn-secondary">หน้าหลัก</a>
        <a href="medicine.php" class="btn btn-secondary">ยา</a>
        <a href="buy.php" class="btn btn-secondary">ร้านค้า</a>
        <a href="users.php" class="btn btn-secondary">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary">ข้อมูลส่วนตัว</a>
        <a href="status.php" class="btn btn-secondary">สถานะ</a>
    </aside>

    <div class="container">
        <a href="online.php" class="btn btn-secondary mb-3">ย้อนกลับไปที่รายชื่อผู้ใช้</a>
        <h2>Chat</h2>
        <div class="chat-window" id="chat-window">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="message <?= htmlspecialchars($row['sender_type']) === 'user' ? 'user' : 'pharmacist' ?>">
                    <div class="text">
                        <?= htmlspecialchars($row['text']) ?>
                        <?php if ($row['image']) { ?>
                            <div class="image">
                                <img src=" data:image/*;base64, <?= htmlspecialchars($row['image']) ?>" alt="Image">
                            </div>
                        <?php } ?>
                        <div class="timestamp"><?= htmlspecialchars($row['created_at']) ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <form action="send_message.php" method="POST" enctype="multipart/form-data" id="chatForm">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
            <input type="hidden" id="compressedImage" name="compressedImage">
            <div class="input-group mt-3">
                <input type="text" name="message" class="form-control" placeholder="Type your message">
                <label for="file-input" class="btn btn-outline-secondary">
                    <i class="bi bi-upload"></i>
                </label>
                <input type="file" id="file-input" class="d-none" accept="image/*">
                <button type="submit" class="btn btn-info" id="sendButton">Send</button>
            </div>
        </form>

        <!-- เพิ่ม JavaScript ที่ท้ายไฟล์ -->
        <script>
            document.getElementById('file-input').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function(e) {
                        const img = new Image();
                        img.src = e.target.result;
                        img.onload = function() {
                            const canvas = document.createElement('canvas');
                            const MAX_WIDTH = 800;
                            const scaleSize = MAX_WIDTH / img.width;
                            canvas.width = MAX_WIDTH;
                            canvas.height = img.height * scaleSize;

                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                            const compressedImage = canvas.toDataURL('image/jpeg', 0.7);
                            document.getElementById('compressedImage').value = compressedImage;
                        };
                    };
                }
            });

            document.getElementById('chatForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const message = document.querySelector('input[name="message"]').value.trim();
                const compressedImage = document.getElementById('compressedImage').value;

                if (!message && !compressedImage) {
                    alert("Please enter a message or upload an image.");
                    return;
                }

                const formData = new FormData();
                formData.append('user_id', document.querySelector('input[name="user_id"]').value);
                formData.append('message', message);
                if (compressedImage) {
                    formData.append('compressedImage', compressedImage);
                }

                fetch('send_message.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.text())
                    .then(result => {
                        console.log(result);
                        location.reload(); // Reload page to show new messages
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });

                document.getElementById('sendButton').disabled = false;
            });
        </script>

        <!-- แก้ไขส่วนการแสดงข้อความและรูปภาพ -->
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="message <?= htmlspecialchars($row['sender_type']) === 'user' ? 'user' : 'pharmacist' ?>">
                <div class="text">
                    <?= htmlspecialchars($row['text']) ?>
                    <?php if (!empty($row['image'])) { ?>
                        <div class="image">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Uploaded Image" onerror="this.style.display='none'">
                        </div>
                    <?php } ?>
                    <div class="timestamp"><?= htmlspecialchars($row['created_at']) ?></div>
                </div>
            </div>
        <?php } ?>
</body>

</html>