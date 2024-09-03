<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style2.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        margin-left: 220px;
    }

    .logo {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo img {
        max-width: 150px;
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
        width: calc(100% - 20px);
    }
</style>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="medicine.php" class="btn btn-secondary me-2">Medicine</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
        <a href="users.php" class="btn btn-secondary me-2">User</a>
        <a href="info.php" class="btn btn-secondary me-2">Info</a>
        <a href="chat.php" class="btn btn-secondary me-2">chat</a>
    </div>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>Manage</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>

        <div style="width: 50%; margin: auto;">
            <canvas id="stockChart"></canvas> <!-- Element ที่ใช้แสดงผลกราฟ -->
        </div>

        <script>
            // ดึงข้อมูลจากไฟล์ PHP
            fetch('data.php')
                .then(response => response.json())
                .then(data => {
                    // สร้าง arrays สำหรับเก็บชื่อยาและจำนวนสต็อก
                    const itemNames = data.map(item => item.itemName);
                    const itemStocks = data.map(item => item.itemStock);

                    // สร้างกราฟโดยใช้ Chart.js
                    const ctx = document.getElementById('stockChart').getContext('2d');
                    const stockChart = new Chart(ctx, {
                        type: 'bar', // ประเภทกราฟ: bar, line, pie, etc.
                        data: {
                            labels: itemNames, // ชื่อยาสำหรับแกน X
                            datasets: [{
                                label: 'Medicine Stock',
                                data: itemStocks, // จำนวนสต็อกสำหรับแกน Y
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
        </script>
</body>
</html>