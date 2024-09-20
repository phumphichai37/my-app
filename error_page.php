<?php
session_start();

// รับข้อความแสดงข้อผิดพลาดและ user_id จาก URL
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred.';
$u_id = isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '');

// แสดงข้อความแจ้งเตือน
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container text-center mt-5">
        <h1>Error</h1>
        <p><?php echo $message; ?></p>
        <a href="usermanage.php?user_id=<?php echo $u_id; ?>" class="btn btn-primary">ย้อนกลับ</a>
    </div>
</body>
</html>
