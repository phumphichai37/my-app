<?php
session_start();
if (isset($_SESSION["pharmacist"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.1/js/all.min.js" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMYD4OVIt5yEd3/xKBf7elGmcYFZoaMH7enPbmZ" crossorigin="anonymous"></script>
    <style>
        .container {
            max-width: 400px;
            margin-top: 50px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
        }
        .btn {
            width: 100%;
            margin-top: 10px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .navbar-info {
            background-color: #17a2b8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
        </div>
    </nav>
    
    <div class="container">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <?php
        $fullname = '';
        $email = '';
        $password = '';
        if (isset($_POST["submit"])) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email']; 
            $password = $_POST['password'];

            $options = [
                'cost' => 10,
            ];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);
            
            $errors = array();

            if (empty($fullname) || empty($email) || empty($password)) {
                array_push($errors, 'กรุณากรอกข้อมูล');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, 'ไม่พบอีเมล');
            }
            if (strlen($password) < 8) {
                array_push($errors, 'กรุณาใส่รหัสผ่านอย่างน้อย 8 ตัว');
            }

            require_once "connectdb.php";
            $sql = "SELECT * FROM pharmacist WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $rowCount = mysqli_num_rows($result);

            if ($rowCount > 0) {
                array_push($errors, "อีเมลนี้ได้ทำการสมัครแล้ว");
            }
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                $sql = "INSERT INTO pharmacist (pharmacist_name, email, password) VALUES (?,?,?)";
                $stmt = mysqli_stmt_init($conn);
                $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
                if ($prepareStmt) {
                    mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $hashed_password);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "<div class='alert alert-success'>คุณได้ทำการสมัครสมาชิกเรียบร้อย</div>";
                    } else {
                        echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการสมัครสมาชิก</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL</div>";
                }
            }
        }
        ?>

        <form action="register.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="fullname" placeholder="First Name" value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
                <small class="form-text text-muted">ตัวอย่าง: abc@gmail.com</small>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <small class="form-text text-muted">กรุณาใส่รหัสอย่างน้อย 8 ตัว</small>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Register Account" name="submit">
            </div>
            <div><a href="login.php">เข้าสู่ระบบ</a></div>
        </form>
    </div>
</body>
</html>
