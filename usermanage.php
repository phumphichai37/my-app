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
    $user_id = intval($_POST['user_id']);
    $description = trim($_POST['description']);

    // อัพเดตข้อมูล description ในฐานข้อมูล
    $sql = "UPDATE users SET description = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $description, $user_id);

    if ($stmt->execute()) {
        // อัพเดตสำเร็จ redirect ไปยังหน้า profile
        header("Location: usermanage.php?user_id=$user_id");
        exit();
    } else {
        // กรณีมีปัญหาในการอัพเดต
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = intval($u_id);
$sql = "
    SELECT users.full_name, users.email, users.birthday, users.phone_number, users.image, users.description,
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
$conn->close();
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
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.2em;
            text-align: center;
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
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
    </aside>

    <div class="container">
        <div class="profile-container">
            <!-- Use Bootstrap Grid system for 2 columns -->
            <div class="row">
                <!-- Column for Profile Picture -->
                <div class="col-md-4">
                    <strong>Profile Picture:</strong><br>
                    <?php if (!empty($user['image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode(file_get_contents($user['image'])); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture">
                            <span>Profile</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Column for User Details -->
                <div class="col-md-8">
                    <div class="profile-details">
                        <form method="post" action="">
                            <strong>Description:</strong><br />
                            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea><br />
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" class="btn btn-primary">ตกลง</button>
                        </form> <br />
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['full_name'] ?? ''); ?> 
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?> <br />
                        <strong>Birthday:</strong> <?php echo htmlspecialchars($user['birthday'] ?? ''); ?> 
                        <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? ''); ?> <br />
                        <strong>House No:</strong> <?php echo htmlspecialchars($user['house_no'] ?? ''); ?> 
                        <strong>Village No:</strong> <?php echo htmlspecialchars($user['village_no'] ?? ''); ?> <br />
                        <strong>Sub Area:</strong> <?php echo htmlspecialchars($user['sub_area'] ?? ''); ?> 
                        <strong>Area:</strong> <?php echo htmlspecialchars($user['area'] ?? ''); ?> <br />
                        <strong>Province:</strong> <?php echo htmlspecialchars($user['province'] ?? ''); ?> 
                        <strong>Postal Code:</strong> <?php echo htmlspecialchars($user['postal_code'] ?? ''); ?> <br />
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>