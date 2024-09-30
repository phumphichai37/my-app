<?php
session_start();
include 'connectdb.php';

// รับค่า user_id จาก URL หรือ Session
$u_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "");

// ถ้าไม่มี user_id ให้ redirect ไปยังหน้า error
if (empty($u_id)) {
    header("Location: error_page.php?message=User ID not provided.");
    exit();
}

$_SESSION['user_id'] = $u_id; // เก็บ user_id ไว้ใน Session

// ถ้าการ request เป็น POST (การอัปเดตข้อมูล)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจาก form
    $user_id = intval($_POST['user_id']); // ใช้ user_id ที่ส่งมาจากฟอร์ม
    $description = trim($_POST['description']);
    $drug_allergy = trim($_POST['drug_allergy']);

    // จัดการรูปภาพที่อัปโหลด
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        // อ่านไฟล์และแปลงเป็น base64
        $imageData = file_get_contents($_FILES['profile_image']['tmp_name']);
        $imageBase64 = base64_encode($imageData);

        // อัพเดตข้อมูล description, drug_allergy และรูปภาพในฐานข้อมูล
        $sql = "UPDATE users SET description = ?, drug_allergy = ?, image = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $description, $drug_allergy, $imageBase64, $user_id);
    } else {
        // อัพเดตเฉพาะ description และ drug_allergy
        $sql = "UPDATE users SET description = ?, drug_allergy = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $description, $drug_allergy, $user_id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // อัพเดตสำเร็จ redirect ไปยังหน้า profile
        $_SESSION['success_message'] = "แก้ไขข้อมูลเสร็จสิ้น"; // เพิ่มข้อความสำเร็จใน session
        header("Location: usermanage.php?user_id=$user_id");
        exit();
    } else {
        // กรณีมีปัญหาในการอัพเดต
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = intval($u_id);
$sql = "
    SELECT users.*,
            address.house_no, address.village_no, address.sub_area, address.area, address.province, address.postal_code
      FROM users
      JOIN address ON users.user_id = address.user_id
      WHERE users.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 575.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .container {
                margin-left: 0;
                max-width: 90%;
                padding: 10px;
            }
        }

        .profile-container {
            margin-top: 20px;
        }

        .profile-details {
            margin-bottom: 15px;
        }

        .profile-picture {
            max-width: 50%;
            max-height: 50%;
            width: auto;
            height: auto;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.2em;
            text-align: center;
            margin-left: 10%;
        }
    </style>
</head>

<body>
    <?php include('css/navSIde.php');?>

    <div class="container">
        <?php
        // แสดงข้อความสำเร็จ
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']); // ลบข้อความหลังจากแสดง
        }
        ?>
        <div class="profile-container">
            <div class="row">
                <div class="col-md-4">
                    <strong>Profile Picture:</strong><br>
                    <?php if (!empty($user['image'])): ?>
                        <img src="data:image/*;base64,<?= $user['image']; ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <img src="asset/default_user_icon.png" alt="Default Profile Picture" class="profile-picture">
                    <?php endif; ?>
                </div>

                <div class="col-md-8">
                    <div class="profile-details">
                        <form method="post" action="" enctype="multipart/form-data">
                            <strong>Change Profile Picture:</strong><br />
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <input type="file" name="profile_image" class="form-control"><br>
                            <strong>ประวัติการรักษา:</strong><br />
                            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea><br />
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <strong>ประวัติการแพ้ยา:</strong><br />
                            <textarea name="drug_allergy" class="form-control" rows="5"><?php echo htmlspecialchars($user['drug_allergy'] ?? ''); ?></textarea><br />
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" class="btn btn-primary">ตกลง</button>
                        </form> <br />
                        <strong>ชื่อ-สกุล:</strong> <?php echo htmlspecialchars($user['full_name'] ?? ''); ?> <br />
                        <strong>อัเมล:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?> <br />
                        <strong>วันที่เกิด:</strong> <?php echo htmlspecialchars($user['birthday'] ?? ''); ?> <br />
                        <strong>เบอร์โทรศัพท์:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? ''); ?> <br />
                        <strong>บ้านเลขที่:</strong> <?php echo htmlspecialchars($user['house_no'] ?? ''); ?> <br />
                        <strong>หมู่:</strong> <?php echo htmlspecialchars($user['village_no'] ?? ''); ?> <br />
                        <strong>ตำบล:</strong> <?php echo htmlspecialchars($user['sub_area'] ?? ''); ?> <br />
                        <strong>อำเภอ:</strong> <?php echo htmlspecialchars($user['area'] ?? ''); ?> <br />
                        <strong>จังหวัด:</strong> <?php echo htmlspecialchars($user['province'] ?? ''); ?> <br />
                        <strong>รหัสไปรษณีย์:</strong> <?php echo htmlspecialchars($user['postal_code'] ?? ''); ?> <br />
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>