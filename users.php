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
    <title>Manage Members</title>
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
        <a href="medicine.php" class="btn btn-secondary me-2">Medicine</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
    </div>
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>ข้อมูลผู้ป่วย</h2>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">รหัสสมาชิก</th>
                    <th scope="col">ชื่อสมาชิก</th>
                    <th scope="col">นามสกุล</th>
                    <th scope="col">เบอร์โทรศัพท์</th>
                    <th scope="col">Add</th>
                    <th scope="col">Edit</th>
                    <th scope="col">Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Sample data for display
                $members = [
                    ['id' => '01', 'firstName' => 'John', 'lastName' => 'Smit', 'phone' => '+66986122011'],
                    ['id' => '02', 'firstName' => 'John', 'lastName' => 'Smit', 'phone' => '+66986122011'],
                    // Add more members as needed
                ];

                foreach ($members as $member) {
                    echo "<tr>
                        <td>{$member['id']}</td>
                        <td>{$member['firstName']}</td>
                        <td>{$member['lastName']}</td>
                        <td>{$member['phone']}</td>
                        <td><button class='btn btn-success'>Add</button></td>
                        <td><button class='btn btn-warning'>Edit</button></td>
                        <td><button class='btn btn-danger'>Delete</button></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
