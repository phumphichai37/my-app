<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Order Tracking</title>
    <style>
        body {
            background: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-left: 240px;
            /* เพิ่มพื้นที่ให้ sidebar */
            max-width: 1200px;
            /* จำกัดความกว้างให้ดูสมดุล */
        }

        .order-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 20px;
            /* เพิ่ม padding เพื่อให้เนื้อหามีพื้นที่ */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            /* เพิ่ม shadow ให้ดูน่าสนใจ */
            transition: box-shadow 0.3s ease;
            /* เพิ่มเอฟเฟกต์ hover */
        }

        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .order-card h2 {
            font-size: 20px;
            margin: 0 0 15px 0;
            font-weight: bold;
            color: #333;
        }

        .order-details {
            font-size: 16px;
            margin-bottom: 10px;
            color: #555;
        }

        .order-details strong {
            font-weight: bold;
            color: #000;
        }

        .sidebar {
            background-color: #F8F8FF;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            padding-top: 60px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-x: hidden;
        }

        .sidebar .btn {
            margin: 10px;
            width: calc(100% - 20px);
        }

        .sidebar img {
            max-width: 160px;
            display: block;
            margin: 0 auto 20px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="info.php" class="btn btn-secondary me-2">ข้อมูล</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="admin.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="chat.php" class="btn btn-secondary me-2">แชท</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
    </div>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">ย้อนกลับ</a>
                <a href="logout.php" class="btn btn-warning">ออกจากระบบ</a>
            </div>
        </div>

        <div id="order-list"></div>
    </div>

    <!-- นำเข้าไฟล์ JavaScript -->
    <script src="script.js"></script>
</body>

</html>