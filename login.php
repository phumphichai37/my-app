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
    <title>login</title>
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
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
        
            require_once "connectdb.php";
        
            $stmt = $conn->prepare("SELECT * FROM pharmacist WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        
            if ($user) {
                if (password_verify($password, $user["password"])) {
                    session_start();
                    $_SESSION["pharmacist"] = "yes";
                    header("Location: index.php");
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>รหัสผ่านไม่ถูกต้อง</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>อีเมลไม่ถูกต้อง</div>";
            }
        
            
            $stmt->close();
            $conn->close();
        }
        ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <input type="email" placeholder="Enter Email" name="email" class= "form-control" >
            </div>
            <div class="form-group">
                <input type="password" placeholder="Enter Password" name="password" class= "form-control" >
            </div>
            <div class="form-group">
                <input type="submit" value="Login" name="login" class="btn btn-primary">
            </div>
            <div><a href="register.php">สมัครสมาชิก</a></div>
        </form>
    </div>
</body>
</html>
