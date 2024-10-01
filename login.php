<?php
session_start(); // เริ่มต้นเซสชัน

if (isset($_SESSION["pharmacist"])) {
    header("Location: index.php");
    exit();
}

$images = [
    'asset/dash.png',
    'asset/cart.png',
    'asset/picChat.png',
    'asset/status.png',
];

require_once "connectdb.php"; // เชื่อมต่อฐานข้อมูล

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM pharmacist WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user["password"])) {
            $_SESSION["pharmacist"] = $user; // เก็บข้อมูลผู้ใช้ในเซสชัน
            $_SESSION["pharmacist_id"] = $user["pharmacist_id"]; // ตั้งค่าตัวแปรเซสชัน
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .body {
            background-color: #fffcf9;
        }

        .container {
            max-width: 400px;
            margin-top: 5%;
            padding: 20px;
            background: #fffcf9;
            border-radius: 20px;
            box-shadow: 0 0 10px #fff1e3;
            margin-right: 10%;
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

        .btn-login {
            width: 100%;
            background-color: #17a2b8;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: bold;
            color: #ffffff;
        }

        .btn-login:hover {
            background-color: #138496;
        }

        .manual-button {
            display: inline-flex;
            align-items: center;
            background-color: #fff1e3;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: flex;
            justify-content: center;
        }

        .manual-button:hover {
            background-color: #ffe6cc;
        }

        .manual-button a {
            color: #333;
            text-decoration: none;
        }

        .icon-container {
            margin-left: 10px;
        }

        .icon-container i {
            font-size: 20px;
            color: #17a2b8;
        }

        .image-slider {
            position: fixed;
            width: 40%;
            height: 50vh;
            overflow: hidden;
            float: left;
            margin-top: 5%;
            margin-left: 10%;
            border: 3px solid transparent;
            background-image: linear-gradient(white, white),
                linear-gradient(to right, #17a2b8, #20c997);
            background-origin: border-box;
            background-clip: content-box, border-box;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            background-color: #f8f9fa;
        }

        .slide.active {
            opacity: 1;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center center;
        }

        .container {
            float: right;
            width: 35%;
            margin-right: 10%;
            margin-top: 10vh;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
        </div>
    </nav>

    <div class="image-slider">
        <?php foreach ($images as $index => $image): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo $image; ?>" alt="Slide <?php echo $index + 1; ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <div class="container">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <form action="login.php" method="post">
            <div class="form-group">
                <input type="email" placeholder="Enter Email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="password" placeholder="Enter Password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login" name="login" class="btn btn-login">
            </div>
        </form>
        <div class="manual-button">
            <a href="#" onclick="downloadPDF()">คู่มือการใช้งาน</a>
            <div class="icon-container">
                <i class="fas fa-cloud-download-alt" style="color: #17a2b8;" onclick="downloadPDF()"></i>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const pdfUrl = "asset/document.pdf";
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = "คู่มือการใช้งาน.pdf";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function showSlide(n) {
            slides[currentSlide].classList.remove('active');
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        setInterval(nextSlide, 5000);
    </script>
</body>

</html>