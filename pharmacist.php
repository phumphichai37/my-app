<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["pharmacist_id"])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

// รับค่า pharmacist_id จาก URL หรือ Session
$p_id = isset($_GET['pharmacist_id']) ? trim($_GET['pharmacist_id']) : $_SESSION['pharmacist_id'];

$_SESSION['pharmacist_id'] = $p_id;

// ตรวจสอบว่า pharmacist_id มีค่าหรือไม่
if (empty($p_id)) {
    die('No pharmacist ID provided');
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$pharmacist_id = intval($p_id);
$sql = "SELECT pharmacist_name, email, licen_number, specialization, image FROM pharmacist WHERE pharmacist_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param('i', $pharmacist_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pharmacist = $result->fetch_assoc();
} else {
    die('No pharmacist found with ID: ' . $pharmacist_id);
}

$stmt->close();

// Handle form submission to update pharmacist details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['pharmacist_name']);
    $new_email = trim($_POST['email']);
    $new_licen_number = trim($_POST['licen_number']);
    $new_specialization = trim($_POST['specialization']);
    
    // Handle image upload
    $upload_dir = 'uploads/profile_pics/';
    $image_path = '';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Validate image extension (jpg, png, etc.)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $image_path = $upload_dir . $new_file_name;

            // Move uploaded file to the designated directory
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $image_path)) {
                // Image uploaded successfully
            } else {
                echo "<script>alert('Failed to upload the image.');</script>";
            }
        } else {
            echo "<script>alert('Invalid image file type.');</script>";
        }
    }

    // Update pharmacist details in the database, including image path if available
    $update_sql = "UPDATE pharmacist SET pharmacist_name = ?, email = ?, licen_number = ?, specialization = ?, image = ? WHERE pharmacist_id = ?";
    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $image_path = !empty($image_path) ? $image_path : $pharmacist['image']; // Keep the old image if a new one isn't uploaded
    $update_stmt->bind_param('sssssi', $new_name, $new_email, $new_licen_number, $new_specialization, $image_path, $pharmacist_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Pharmacist details updated successfully.');</script>";
        echo "<script>window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Failed to update details.');</script>";
    }

    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
            <div>
                <a href="users.php" class="btn btn-light me-2">ย้อนกลับ</a>
                <a href="logout.php" class="btn btn-light">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <aside class="sidebar">
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะสินค้า</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
    </aside>

    <div class="container">
        <div class="profile-container">
            <div class="row">
                <div class="col-md-4">
                    <strong>Profile Picture:</strong><br>
                    <?php if (!empty($pharmacist['image'])): ?>
                        <img src="<?php echo htmlspecialchars($pharmacist['image']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture">
                            <span>Profile</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-8">
                    <div class="profile-details">
                        <form method="post" action="" enctype="multipart/form-data">
                            <strong>Pharmacist Name:</strong><br />
                            <input type="text" name="pharmacist_name" class="form-control" value="<?php echo htmlspecialchars($pharmacist['pharmacist_name'] ?? ''); ?>"><br />
                            
                            <strong>Email:</strong><br />
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($pharmacist['email'] ?? ''); ?>"><br />
                            
                            <strong>License Number:</strong><br />
                            <input type="text" name="licen_number" class="form-control" value="<?php echo htmlspecialchars($pharmacist['licen_number'] ?? ''); ?>"><br />
                            
                            <strong>Specialization:</strong><br />
                            <textarea name="specialization" class="form-control" rows="3"><?php echo htmlspecialchars($pharmacist['specialization'] ?? ''); ?></textarea><br />
                            
                            <strong>Profile Picture:</strong><br />
                            <input type="file" name="profile_picture" class="form-control"><br />

                            <input type="hidden" name="pharmacist_id" value="<?php echo htmlspecialchars($pharmacist_id); ?>">
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
