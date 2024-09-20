<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: users.php");
    exit();
}

require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $birthday = $_POST['birthday'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบเบอร์โทรศัพท์
    if (strlen($phone_number) !== 10) {
        header("Location: adduser.php?status=phone_error");
        exit();
    }

    // ตรวจสอบรหัสผ่าน
    if (strlen($password) < 6) {
        header("Location: adduser.php?status=password_error");
        exit();
    }

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // รับค่าที่อยู่
    $house_no = $_POST['house_no'];
    $village_no = $_POST['village_no'];
    $sub_area = $_POST['sub_area'];
    $area = $_POST['area'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];

    // การจัดการไฟล์รูปภาพ
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $file_new_name = uniqid() . '.' . $file_ext;
            $upload_dir = 'uploads/';

            // ตรวจสอบและสร้างไดเรกทอรีหากไม่มี
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // อัปโหลดไฟล์
            $file_path = $upload_dir . $file_new_name;
            if (move_uploaded_file($file_tmp, $file_path)) {
                $profile_picture = $file_path;
            }
        }
    }

    // เริ่มต้นการทำงานกับฐานข้อมูล
    $conn->begin_transaction();

    try {
        // บันทึกข้อมูลผู้ใช้งาน
        $sql = "INSERT INTO users (full_name, birthday, phone_number, email, password, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $full_name, $birthday, $phone_number, $email, $hashed_password, $profile_picture);
        $stmt->execute();
        $user_id = $stmt->insert_id; // รับ ID ของผู้ใช้งานที่เพิ่งเพิ่ม
        $stmt->close();

        // บันทึกข้อมูลที่อยู่
        $sql = "INSERT INTO address (user_id, house_no, village_no, sub_area, area, province, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $house_no, $village_no, $sub_area, $area, $province, $postal_code);
        $stmt->execute();
        $stmt->close();

        // ยืนยันการทำธุรกรรม
        $conn->commit();
        header("Location: users.php?status=success");
        exit();
    } catch (Exception $e) {
        // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
        $conn->rollback();
        header("Location: adduser.php?status=error");
        exit();
    } finally {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script>
        function validateForm() {
            const phoneNumber = document.getElementById('phone_number').value;
            const password = document.getElementById('password').value;
            let valid = true;

            // ตรวจสอบเบอร์โทรศัพท์
            if (phoneNumber.length !== 10) {
                alert('เบอร์โทรศัพท์ต้องมีความยาว 10 หลัก');
                valid = false;
            }

            // ตรวจสอบรหัสผ่าน
            if (password.length < 6) {
                alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัว');
                valid = false;
            }

            return valid;
        }
    </script>
</head>
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
</style>

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
    </aside>

    <div class="container" style="margin-top: 50px;">
        <h1>เพิ่มผู้ใช้งาน</h1>
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="full_name" class="form-label">ชื่อสมาชิก</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">วัน/เดือน/ปี</label>
                <input type="date" class="form-control" id="birthday" name="birthday" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <!-- ส่วนข้อมูลที่อยู่ -->
            <div class="mb-3">
                <label for="house_no" class="form-label">House Number</label>
                <input type="text" class="form-control" id="house_no" name="house_no" required>
            </div>
            <div class="mb-3">
                <label for="village_no" class="form-label">Village Number</label>
                <input type="text" class="form-control" id="village_no" name="village_no" required>
            </div>
            <div class="mb-3">
                <label for="sub_area" class="form-label">Sub Area</label>
                <input type="text" class="form-control" id="sub_area" name="sub_area" required>
            </div>
            <div class="mb-3">
                <label for="area" class="form-label">Area</label>
                <input type="text" class="form-control" id="area" name="area" required>
            </div>
            <div class="mb-3">
                <label for="province" class="form-label">Province</label>
                <input type="text" class="form-control" id="province" name="province" required>
            </div>
            <div class="mb-3">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
            </div>

            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
            </div>

            <button type="submit" class="btn btn-primary">เพิ่มผู้ใช้งาน</button>
        </form>

        <?php if (isset($_GET['status'])): ?>
            <script>
                var status = "<?php echo $_GET['status']; ?>";
                if (status === 'success') {
                    alert('User added successfully');
                } else if (status === 'error') {
                    alert('Error adding user');
                } else if (status === 'phone_error') {
                    alert('เบอร์โทรศัพท์ต้องมีความยาว 10 หลัก');
                } else if (status === 'password_error') {
                    alert('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัว');
                }
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
