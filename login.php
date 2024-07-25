<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<style>
        body {
            /* background: url('asset/bg.jpg') no-repeat center center fixed;
            background-size: cover; */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
<body>
    <div class="container">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <?php
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];

            require_once "connectdb.php";
            $sql = " SELECT * FROM user WHERE email = '$email'" ;
            $result = mysqli_query($conn, $sql) ;
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC) ;
            if ($user) {
                if (password_verify($password, $user["password"])) {
                    header("Location: index.php");
                    session_start();
                    $_SESSION["user"] = "yes";
                    header("Location: index.php");
                    die();
                }else{
                    echo "<div class='alert alert-danger'>รหัสผ่านไม่ถูกต้อง</div>";
                }
            }else{
                echo "<div class='alert alert-danger'>อีเมลไม่ถูกต้อง</div>";
            }
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