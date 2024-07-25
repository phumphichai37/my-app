<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(function() {
            $("#datepicker").datepicker({
                dateFormat: "yy-mm-dd"
            });
        });
    </script>
    <style>
        body {
            /* background: url('asset/bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; */
            background: aquamarine;

        }
        .container {
            max-width: 500px;
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
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        /* @media (max-width: 575.98px) {
            .container {
                max-width: 90%;
                padding: 10px;
            }
        } */
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <?php
        $fullname = '';
        $email = '';
        $phone = '';
        $birthdate = '';
        if (isset($_POST["submit"])) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $birthdate = $_POST['birthdate'];
            $password = $_POST['password'];

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $errors = array();
            if (empty($fullname) || empty($email) || empty($phone) || empty($birthdate) || empty($password)) {
                array_push($errors, 'กรุณากรอกข้อมูล');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, 'ไม่พบอีเมล');
            }
            if (strlen($password) < 8) {
                array_push($errors, 'กรุณาใส่รหัสผ่านอย่างน้อย 8 ตัว');
            }

            require_once "connectdb.php";
            $sql = "SELECT * FROM user WHERE email = ?";
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
                $sql = "INSERT INTO user (userName, email, phone, birthdate, password) VALUES (?,?,?,?,?)";
                $stmt = mysqli_stmt_init($conn);
                $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
                if ($prepareStmt) {
                    mysqli_stmt_bind_param($stmt, "sssss", $fullname, $email, $phone, $birthdate, $hashed_password);
                    mysqli_stmt_execute($stmt);
                    echo "<div class='alert alert-success'>คุณได้ทำการสมัครสมาชิกเรียบร้อย</div>";
                } else {
                    echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการสมัครสมาชิก</div>";
                }
            }
        }
        ?>

        <form action="register.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="fullname" placeholder="Full Name" value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <input type="tel" class="form-control" name="phone" placeholder="Phone Number" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($phone); ?>" required>
                <small class="form-text text-muted">Example: 0661234567</small>
            </div>
            <div class="form-group">
                <label for="date">Select a birthdate:</label>
                <input type="date" id="date" name="birthdate" placeholder="Birth Date" class="form-control" value="<?php echo htmlspecialchars($birthdate); ?>" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <!-- <div class="form-group">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
            </div> -->
            <div class="form-group">
                <input type="submit" class="btn btn-secondary" value="Register" name="submit">
            </div>
            <div><a href="login.php">เข้าสู่ระบบ</a></div>
        </form>
    </div>
</body>
</html>
