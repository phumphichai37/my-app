<?php
session_start();
include 'connectdb.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รับค่า user_id จาก POST
$u_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : "";

// ถ้าไม่มี user_id ให้ redirect ไปยังหน้า error
if (empty($u_id)) {
    header("Location: error_page.php?message=User ID not provided.");
    exit();
}

// รับค่าข้อมูลที่อัปเดตจากฟอร์ม
$full_name = $_POST['username'];
$email = $_POST['email'];
$phone_number = $_POST['phone'];
$description = $_POST['description'];
$house_no = $_POST['house_no'];
$village_no = $_POST['village_no'];
$sub_area = $_POST['sub_area'];
$area = $_POST['area'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];

// ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่หรือไม่
$image = 'default-image.jpg';
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = 'uploads/';
    $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $image = $target_file;
    } else {
        header("Location: error_page.php?message=Failed to upload image.");
        exit();
    }
}

// อัปเดตข้อมูลผู้ใช้ในฐานข้อมูล
$sql = "
    UPDATE users
    SET full_name = ?, email = ?, phone_number = ?, description = ?, image = ?
    WHERE user_id = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header("Location: error_page.php?message=Failed to prepare update statement.");
    exit();
}
$stmt->bind_param('sssssi', $full_name, $email, $phone_number, $description, $image, $u_id);
if (!$stmt->execute()) {
    header("Location: error_page.php?message=Failed to update user information.");
    exit();
}

$sql = "
    UPDATE address
    SET house_no = ?, village_no = ?, sub_area = ?, area = ?, province = ?, postal_code = ?
    WHERE user_id = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header("Location: error_page.php?message=Failed to prepare address update statement.");
    exit();
}
$stmt->bind_param('ssssssi', $house_no, $village_no, $sub_area, $area, $province, $postal_code, $u_id);
if (!$stmt->execute()) {
    header("Location: error_page.php?message=Failed to update address information.");
    exit();
}

$stmt->close();
$conn->close();

// Redirect to profile page or show success message
header("Location: usermanage.php?user_id=" . $u_id);
exit();
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

        body {
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
        }

        .profile-picture {
            max-width: 150px;
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
    </aside>
    <div class="container">
        <h1>Edit Profile</h1>
        <form method="post" action="editprofile.php" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($u_id); ?>">

            <!-- ส่วนแสดงรูปโปรไฟล์และเลือกอัปโหลดใหม่ -->
            <div class="mb-3">
                <img src="<?php echo isset($user['image']) ? htmlspecialchars($user['image']) : 'default-image.jpg'; ?>" alt="Profile Picture" class="profile-picture">
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            </div>

            <!-- ส่วนข้อมูลของผู้ใช้ -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?php echo isset($user['description']) ? htmlspecialchars($user['description']) : ''; ?>" required>
            </div>

            <!-- ส่วนที่อยู่ของผู้ใช้ -->
            <div class="mb-3">
                <label for="house_no" class="form-label">House Number</label>
                <input type="text" class="form-control" id="house_no" name="house_no" value="<?php echo isset($user['house_no']) ? htmlspecialchars($user['house_no']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="village_no" class="form-label">Village Number</label>
                <input type="text" class="form-control" id="village_no" name="village_no" value="<?php echo isset($user['village_no']) ? htmlspecialchars($user['village_no']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="sub_area" class="form-label">Sub Area</label>
                <input type="text" class="form-control" id="sub_area" name="sub_area" value="<?php echo isset($user['sub_area']) ? htmlspecialchars($user['sub_area']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="area" class="form-label">Area</label>
                <input type="text" class="form-control" id="area" name="area" value="<?php echo isset($user['area']) ? htmlspecialchars($user['area']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="province" class="form-label">Province</label>
                <input type="text" class="form-control" id="province" name="province" value="<?php echo isset($user['province']) ? htmlspecialchars($user['province']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo isset($user['postal_code']) ? htmlspecialchars($user['postal_code']) : ''; ?>" required>
            </div>

            <!-- ปุ่มบันทึกข้อมูล -->
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</body>

</html>