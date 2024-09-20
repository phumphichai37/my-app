<?php
// Include your database connection file
include 'connectdb.php'; // Ensure this path is correct

// Write a query to fetch users
$query = "SELECT user_id, full_name FROM users"; // Adjust the table and column names as needed

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
  die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User List</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
    }

    .container {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .navbar-info {
      background-color: #17a2b8;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      padding: 10px;
    }

    .sidebar {
      position: fixed;
      top: 56px;
      left: 0;
      width: 220px;
      height: calc(100% - 56px);
      background-color: rgba(23, 162, 184, 0.9);
      border-right: 1px solid #ddd;
      z-index: 1000;
      overflow-y: auto;
      padding-top: 20px;
    }

    .sidebar .btn {
      background-color: #17a2b8;
      border: none;
      color: #fff;
      margin: 10px;
      width: calc(100% - 20px);
    }

    .sidebar .btn:hover {
      background-color: #138496;
    }

    body {
      margin: 0;
      padding: 0;
      padding-left: 220px;
      padding-top: 56px;
    }

    h2 {
      color: #333;
      margin-bottom: 30px;
      text-align: center;
    }

    .list-group-item {
      border: none;
      padding: 15px 20px;
      font-size: 1.2rem;
      border-bottom: 1px solid #eee;
    }

    .list-group-item a {
      text-decoration: none;
      color: #007bff;
    }

    .list-group-item:hover {
      background-color: #f0f8ff;
    }

    .list-group-item a:hover {
      color: #0056b3;
    }

    .list-group-item:last-child {
      border-bottom: none;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-info">
    <div class="container-fluid">
      <h5 class="text-white">TAKECARE</h5>
      <div>
        <a href="logout.php" class="btn btn-light">ออกจากระบบ</a>
      </div>
    </div>
  </nav>

  <aside class="sidebar">
    <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
    <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
    <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
    <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
    <a href="admin.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
    <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
  </aside>
  <div class="container">
    <h2>ผู้ใช้ที่มีการสนทนา</h2>
    <ul class="list-group">
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <li class="list-group-item">
          <a href="chat.php?user_id=<?= htmlspecialchars($row['user_id']) ?>"><?= htmlspecialchars($row['full_name']) ?></a>
        </li>
      <?php } ?>
    </ul>
  </div>
</body>

</html>