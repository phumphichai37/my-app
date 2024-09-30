<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}
require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    $sql_delete = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting user');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        body {
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }

        
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 575.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
            }

            .container {
                margin-left: 0;
                margin-top: 20px;
                padding: 10px;
            }
        }

        /* Table adjustments */
        table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
            max-width: 100%;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            white-space: nowrap;
            /* ป้องกันการตัดบรรทัดใน header */
        }

        td {
            white-space: nowrap;
            /* ป้องกันการตัดบรรทัดในเนื้อหาของเซลล์ */
        }

        /* Adjust the email column to wrap if needed */
        td.email {
            word-wrap: break-word;
            max-width: 180px;
        }

        /* Button adjustments */
        .add-user-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            float: right;
            margin-right: 5%;
        }

        .btn-group {
            justify-content: flex-end;
            margin-right: 20px;
        }
    </style>
</head>

<body>
    <?php 
    include('css/navSIde.php');
    ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>ข้อมูลผู้ป่วย</h2>
        </div>
        <div class="btn-group">
            <a href="adduser.php" class="btn btn-success">เพิ่มผู้ใช้งาน</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ลำดับสมาชิก</th>
                    <th scope="col">ชื่อสมาชิก</th>
                    <th scope="col">วัน/เดือน/ปี</th>
                    <th scope="col">เบอร์โทรศัพท์</th>
                    <th scope="col">อีเมล</th>
                    <th scope="col">แก้ไข</th>
                    <th scope="col">ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT user_id, full_name, birthday, phone_number, email FROM users";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>{$row['user_id']}</td>
                            <td>{$row['full_name']}</td>
                            <td>{$row['birthday']}</td>
                            <td>{$row['phone_number']}</td>
                            <td class='email'>{$row['email']}</td>
                            <td class='table-buttons'>
                                <a href='usermanage.php?user_id={$row['user_id']}' class='btn btn-success'>แก้ไข</a>
                            </td>
                            <td class='table-buttons'>
                                <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>
                                    <input type='hidden' name='delete_user_id' value='{$row['user_id']}'>
                                    <button type='submit' class='btn btn-danger'>ลบ</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No members found</td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>