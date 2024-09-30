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
    $description = $_POST['description'];
    $drug_allergy = $_POST['drug_allergy'];

    // ตรวจสอบเบอร์โทรศัพท์
    if (strlen($phone_number) !== 10) {
        header("Location: adduser.php?status=phone_error");
        exit();
    }

    // รับค่าที่อยู่
    $house_no = $_POST['house_no'];
    $village_no = $_POST['village_no'];
    $sub_area = $_POST['sub_area'];
    $area = $_POST['area'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];

    // การจัดการไฟล์รูปภาพ
    // $profile_picture = null;
    // if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    //     $file_tmp = $_FILES['profile_picture']['tmp_name'];
    //     $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    //     $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    //     if ($file_ext === 'jpeg' || $file_ext === 'jpg') {
    //         $image = imagecreatefromjpeg($file_tmp);
    //         ob_start();
    //         imagejpeg($image, null, 75); // ลดคุณภาพภาพเป็น 75% เพื่อลดขนาด
    //         $image_data = ob_get_clean();
    //         $base64_data = base64_encode($image_data);
    //         $profile_picture = $base64_data;  // บันทึกเฉพาะข้อมูล Base64
    //     }
    // }

    // เริ่มต้นการทำงานกับฐานข้อมูล
    $conn->begin_transaction();

    try {
        // บันทึกข้อมูลผู้ใช้งาน
        $sql = "INSERT INTO users (full_name, birthday, phone_number, description, drug_allergy) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $full_name, $birthday, $phone_number, $description, $drug_allergy);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        echo "User ID: " . $user_id;

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
        // Log the error message and redirect
        error_log("Error: " . $e->getMessage());
        $conn->rollback();  // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
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
            let valid = true;

            // ตรวจสอบเบอร์โทรศัพท์
            if (phoneNumber.length !== 10) {
                alert('เบอร์โทรศัพท์ต้องมีความยาว 10 หลัก');
                valid = false;
            }

            return valid;
        }
    </script>
</head>
<style>
    body {
        margin: 0;
        padding: 0;
        padding-left: 220px;
    }
</style>

<body>
    <?php
    include('css/navSIde.php');
    ?>

    <div class="container" style="margin-top: 50px;">
        <h1>เพิ่มผู้ใช้งาน</h1>
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="full_name" class="form-label">ชื่อสมาชิก</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">วัน/เดือน/ปีเกิด</label>
                <input type="date" class="form-control" id="birthday" name="birthday" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">ประวัติการรักษา</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="drug_allergy" class="form-label">ประวัติการแพ้ยา</label>
                <textarea class="form-control" id="drug_allergy" name="drug_allergy" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="house_no" class="form-label">บ้านเลขที่</label>
                <input type="text" class="form-control" id="house_no" name="house_no" required>
            </div>
            <div class="mb-3">
                <label for="village_no" class="form-label">หมู่ที่</label>
                <input type="text" class="form-control" id="village_no" name="village_no" required>
            </div>
            <div class="mb-3">
                <label for="sub_area" class="form-label">ตำบล</label>
                <input type="text" class="form-control" id="sub_area" name="sub_area" required>
            </div>
            <div class="mb-3">
                <label for="area" class="form-label">อำเภอ</label>
                <input type="text" class="form-control" id="area" name="area" required>
            </div>
            <div class="mb-3">
                <label for="province" class="form-label">จังหวัด</label>
                <input type="text" class="form-control" id="province" name="province" required>
            </div>
            <div class="mb-3">
                <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
            </div>
            <button type="submit" class="btn btn-primary">เพิ่มผู้ใช้งาน</button>
            <a href="users.php" class="btn btn-light">ย้อนกลับ</a>
        </form>

        <?php if (isset($_GET['status'])): ?>
            <script>
                var status = "<?php echo $_GET['status']; ?>";
                if (status === 'success') {
                    alert('เพิ่มผู้ใช้งานสำเร็จ');
                } else if (status === 'error') {
                    alert('เกิดข้อผิดพลาดในการเพิ่มผู้ใช้งาน');
                } else if (status === 'phone_error') {
                    alert('เบอร์โทรศัพท์ต้องมีความยาว 10 หลัก');
                }
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>