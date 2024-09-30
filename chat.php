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

$pharmacist_data = $_SESSION['pharmacist'];
$pharmacist_id = $pharmacist_data['pharmacist_id'];

$pharmacist_id = $_SESSION['pharmacist_id'];
$sql = "SELECT image FROM pharmacist WHERE pharmacist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pharmacist_id);
$stmt->execute();
$result = $stmt->get_result();
$pharmacist = $result->fetch_assoc();
$image_path = $pharmacist['image'] ?? 'asset/default_user_icon.png';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/storeChat.css">
</head>
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
        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Pharmacist Image" class="pharmacist-image">
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
        <a href="online.php" class="btn btn-secondary mb-3">ย้อนกลับ</a>
        <button class='btn btn-success' onclick="goToUserCart(<?php echo $userId; ?>)">สั่งซื้อ</button>
        <h2>Chat</h2>
        <div class="chat-window" id="chat-window">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="message <?= htmlspecialchars($row['sender_type']) === 'user' ? 'user' : 'pharmacist' ?>">
                    <div class="text">
                        <?= htmlspecialchars($row['text']) ?>
                        <?php if ($row['image']) { ?>
                            <div class="image">
                                <img src="data:image/*;base64,<?= htmlspecialchars($row['image']) ?>" alt="Image" class="thumbnail" style="cursor: pointer;" onclick="openModal('data:image/*;base64,<?= htmlspecialchars($row['image']) ?>')">
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

            <div id="image-preview" style="display: none;" class="mt-3">
                <img id="preview-image" src="" alt="Selected Image" style="max-width: 200px;">
                <button type="button" class="btn btn-danger mt-2" id="remove-image">Remove Image</button>
            </div>
        </form>

        <!-- โมดัลสำหรับดูรูปภาพ -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel">ดูรูปภาพ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img id="modalImage" src="" alt="Full-size image" style="width: 100%;">
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openModal(imageSrc) {
                document.getElementById('modalImage').src = imageSrc;
                var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
                myModal.show();
            }

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

                            const compressedImage = canvas.toDataURL('image/jpeg', 0.3);
                            document.getElementById('compressedImage').value = compressedImage;

                            document.getElementById('image-preview').style.display = 'block';
                            document.getElementById('preview-image').src = compressedImage;
                        };
                    };
                }
            });

            document.getElementById('remove-image').addEventListener('click', function() {
                document.getElementById('file-input').value = '';
                document.getElementById('compressedImage').value = '';
                document.getElementById('image-preview').style.display = 'none';
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
                        setTimeout(function() {
                            const chatWindow = document.getElementById('chat-window');
                            chatWindow.scrollTop = chatWindow.scrollHeight;
                        }, 100);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });

                document.getElementById('sendButton').disabled = false;
            });

            window.onload = function() {
                const chatWindow = document.getElementById('chat-window');
                chatWindow.scrollTop = chatWindow.scrollHeight;
            };

            function goToUserCart(userId) {
                // ตรวจสอบว่าค่า userId ถูกต้องหรือไม่
                if (!userId) {
                    alert("User ID is missing.");
                    return;
                }

                // เปลี่ยนหน้าไปยัง userCart.php พร้อมกับส่ง user_id ผ่าน URL
                window.location.href = 'userCart.php?user_id=' + userId;
            }
        </script>
    </div>
</body>

</html>