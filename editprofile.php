<?php
session_start();
include 'connectdb.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่า user_id ถูกส่งมาหรือไม่
if (!isset($_GET['user_id'])) {
    // กรณีที่ user_id ไม่ถูกส่งมา ให้ redirect ไปที่หน้าข้อผิดพลาดหรือหน้าอื่น
    header("Location: error_page.php?message=User ID not provided.");
    exit();
}

$user_id = intval($_GET['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $description = $_POST['description'];

    // ตรวจสอบการอัปโหลดไฟล์
    $image_path = $user['image']; // ใช้รูปเดิมถ้าไม่มีการอัปโหลดรูปใหม่
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $upload_dir = 'uploads/'; // โฟลเดอร์ที่เก็บไฟล์ที่อัปโหลด
        $image_path = $upload_dir . $file_name;

        // ตรวจสอบว่าโฟลเดอร์สำหรับเก็บไฟล์มีอยู่แล้วหรือไม่
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // ย้ายไฟล์จาก temp directory ไปยังโฟลเดอร์ที่ต้องการ
        if (move_uploaded_file($file_tmp_name, $image_path)) {
            // อัปโหลดไฟล์สำเร็จ
        } else {
            echo "Failed to upload image.";
            exit();
        }
    }

    // เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูลโปรไฟล์
    $sql = "UPDATE users SET full_name=?, email=?, phone_number=?, description=?, image=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssi', $username, $email, $phone, $description, $image_path, $user_id);

    if ($stmt->execute()) {
        header("Location: usermanage.php?user_id=$user_id");
        exit();
    } else {
        echo "Failed to update profile: " . $conn->error;
    }

    $stmt->close();
}

// ดึงข้อมูลโปรไฟล์ผู้ใช้
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // กรณีที่ไม่พบข้อมูลผู้ใช้ ให้ redirect ไปที่หน้าข้อผิดพลาดหรือหน้าอื่น
    header("Location: error_page.php?message=User not found.");
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
    <title>Edit Profile</title>
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

        .profile-picture {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="usermanage.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
        <div class="container">
            <h1 class="text-center">Edit Profile</h1>
            <form method="post" action="editprofile.php?user_id=<?php echo $user_id; ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <?php if (!empty($user['image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="Profile Picture" class="profile-picture" />
                    <?php endif; ?>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($user['description']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
</body>

</html>