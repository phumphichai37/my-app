<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

$query = "SELECT user_id, full_name FROM users";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select User</title>
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

        .chat-message.user {
            background-color: #e9ecef;
        }

        .chat-message.pharmacist {
            background-color: #d4edda;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="โลโก้">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="info.php" class="btn btn-secondary me-2">ข้อมูล</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="admin.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
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
        <div class="container mt-5">
            <h1>Select User to Chat</h1>
            <ul class="list-group">
                <?php foreach ($users as $user): ?>
                    <li class="list-group-item">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#chatModal" data-user-id="<?php echo $user['user_id']; ?>" data-user-name="<?php echo htmlspecialchars($user['full_name']); ?>">
                            Chat with <?php echo htmlspecialchars($user['full_name']); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Chat Modal -->
        <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="chatModalLabel">Chat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="chat-box" id="chat-box"></div>
                        <form id="chat-form">
                            <div class="input-group mt-3">
                                <input type="text" class="form-control" id="message-input" placeholder="Type your message">
                                <button class="btn btn-primary" type="submit">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
        <script>
            const chatModal = document.getElementById('chatModal');
            const chatBox = document.getElementById('chat-box');
            let currentUserId = null;
            let pharmacistId = "<?php echo $_SESSION['pharmacist_id']; ?>";

            chatModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                currentUserId = button.getAttribute('data-user-id');

                if (currentUserId) {
                    loadMessages();
                }
            });

            document.getElementById('chat-form').addEventListener('submit', async function(event) {
                event.preventDefault();

                const text = document.getElementById('message-input').value;

                if (currentUserId) {
                    const response = await fetch('chat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'userId': currentUserId,
                            'pharmacistId': pharmacistId,
                            'text': text,
                            'image': ''
                        })
                    });

                    const message = await response.json();
                    document.getElementById('message-input').value = '';
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('chat-message', message.senderType);
                    messageElement.textContent = message.text;
                    chatBox.appendChild(messageElement);
                    chatBox.scrollTop = chatBox.scrollHeight;

                    // Ensure the modal stays open
                    chatModal.addEventListener('hidden.bs.modal', function() {
                        chatModal.removeEventListener('hidden.bs.modal', arguments.callee);
                        chatModal.show();
                    });
                }
            });

            async function loadMessages() {
                const response = await fetch(`chat.php?userId=${currentUserId}&pharmacistId=${pharmacistId}`);
                const messages = await response.json();

                chatBox.innerHTML = '';
                messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('chat-message', message.sender_type);
                    messageElement.textContent = message.text;
                    chatBox.appendChild(messageElement);
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            setInterval(() => {
                if (currentUserId) {
                    loadMessages();
                }
            }, 5000);
        </script>
</body>

</html>