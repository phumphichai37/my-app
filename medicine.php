<?php
session_start();

if (!isset($_SESSION['user'])) {
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
    <link rel="stylesheet" href="style2.css">
    <style>
        body {
            background: #f8f9fa; /* Light grey background */
        }
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
        @media (max-width: 575.98px) {
            .container {
                max-width: 90%;
                padding: 10px;
            }
        }
        .sidebar {
            background-color: #F8F8FF;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            padding-top: 60px;
            overflow-x: hidden;
        }
        .sidebar .btn {
            margin: 10px;
            width: calc(100% - 20px); /* Adjust width to fit within the sidebar */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="users.php" class="btn btn-secondary me-2">User</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="logo">
            <img src="asset/logo.png" alt="Logo">
        </div>
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
        <input type="text" class="form-control" placeholder="search" aria-label="Search">
        <div class="row mt-4">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 1" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 2" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 3" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 4" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 5" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card text-black h-100">
                    <div class="card-body">
                        <img src="asset/bg.jpg" alt="Image 6" class="img-fluid mb-3">
                        <p>Text</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
