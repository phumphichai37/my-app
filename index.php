<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>manage</h1>
            <a href="logout.php" class="btn btn-warning">Logout</a>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-info text-black h-100">
                    <div class="card-body">
                        <h5>สถิติการเข้าใช้ระบบ</h5>
                        <a href="info.php" class="btn btn-lycan">More info</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning text-black h-100">
                    <div class="card-body">
                        <h5>ยา</h5>
                        <a href="medicine.php" class="btn btn-lycan">More info</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-cornsilk text-black h-100">
                    <div class="card-body">
                        <h5>ผู้ใช้งานในระบบ</h5>
                        <a href="users.php" class="btn btn-lycan">More info</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-danger text-black h-100">
                    <div class="card-body">
                        <h5 class="card-title">ผู้ดูแลระบบ</h5>
                        <a href="admin.php" class="btn btn-lycan">More info</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-danger text-black h-100">
                    <div class="card-body">
                        <h5 class="card-title">ร้านยา</h5>
                        <a href="buy.php" class="btn btn-lycan">More info</a>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>
