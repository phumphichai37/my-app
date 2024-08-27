<?php
session_start();


if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}


include 'connectdb.php';

$message = "";

// ตรวจสอบว่าผู้ใช้ได้คลิกซื้อสินค้าหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['medicine_id'])) {
    $medicine_id = $_POST['medicine_id'];

    // ดึงข้อมูลยาจากฐานข้อมูล
    $sql = "SELECT * FROM medicine WHERE medicine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $medicine_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $medicine = $result->fetch_assoc();

        // ตรวจสอบว่ามีสต็อกเพียงพอหรือไม่
        if ($medicine['stock'] > 0) {
            $new_stock = $medicine['stock'] - 1;
            $sql_update = "UPDATE medicine SET stock = ? WHERE medicine_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ii', $new_stock, $medicine_id);
            if ($stmt_update->execute()) {
                $message = "ทำรายการสำเร็จ" . $medicine['medicine_name'] . " for $" . number_format((float)$medicine['price'], 2) . ". Remaining stock: " . $new_stock;
            } else {
                $message = "Failed to update stock.";
            }
            $stmt_update->close();
        } else {
            $message = "This medicine is out of stock.";
        }
    } else {
        $message = "Medicine not found.";
    }

    $stmt->close();
}

// ดึงข้อมูลยาจากฐานข้อมูล
$sql = "SELECT * FROM medicine";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED TIME - Buy Medicine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style2.css">
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
            width: calc(100% - 20px);
        }

        .medicine-item {
            margin-bottom: 20px;
        }

        .medicine-item img {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="asset/band.png" alt="Logo">
        </div>
        <a href="index.php" class="btn btn-secondary me-2">Index</a>
        <a href="info.php" class="btn btn-secondary me-2">Info</a>
        <a href="users.php" class="btn btn-secondary me-2">User</a>
        <a href="admin.php" class="btn btn-secondary me-2">Admin</a>
        <a href="medicine.php" class="btn btn-secondary me-2">medicine</a>
    </div>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="index.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row mt-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6 medicine-item">';
                    echo '<div class="card text-black h-100">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row["medicine_name"] . '</h5>';
                    $shortDescription = mb_substr($row["description"], 0, 100);
                    // แสดงข้อความที่ตัด และมีปุ่ม "อ่านเพิ่มเติม"
                    echo '<p class="card-text">' . $shortDescription . '<span id="dots-' . $row["medicine_id"] . '">...</span>';
                    echo '<span id="more-' . $row["medicine_id"] . '" style="display:none;">' . mb_substr($row["description"], 100) . '</span></p>';
                    echo '<button onclick="showMore(' . $row["medicine_id"] . ')" id="myBtn-' . $row["medicine_id"] . '" class="btn btn-link">อ่านเพิ่มเติม</button>';
                    echo '<p class="card-text"><strong>Price: $' . number_format((float)$row["price"], 2) . '</strong></p>';
                    echo '<p class="card-text"><strong>Stock: ' . $row["stock"] . '</strong></p>';
                    $image = $row["image"];

                    // ตรวจสอบว่าค่าใน $image เป็น Base64 หรือไม่
                    if (preg_match('/^data:image\/(\w+);base64,/', $image)) {
                        // ถ้าเป็น Base64 ก็แสดงผลโดยใช้ Base64
                        echo '<img src="' . $image . '" alt="Image">';
                    } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
                        // ถ้าเป็น URL ก็แสดงผลโดยใช้ URL
                        echo '<img src="' . $image . '" alt="Image">';
                    } else {
                        // ถ้าไม่ใช่ทั้ง Base64 และ URL อาจจะแสดงข้อความหรือรูปภาพ placeholder
                        echo '<img src="path/to/placeholder.jpg" alt="Invalid Image">';
                    }
                    echo '<form method="POST" action="" class="mt-2">';
                    echo '<input type="hidden" name="medicine_id" value="' . $row["medicine_id"] . '">';
                    echo '<input type="submit" class="btn btn-success" value="Buy Now">';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p>No medicines found.</p></div>';
            }
            ?>
        </div>
    </div>
    <script>
        function showMore(id) {
            var dots = document.getElementById("dots-" + id);
            var moreText = document.getElementById("more-" + id);
            var btnText = document.getElementById("myBtn-" + id);

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                btnText.innerHTML = "อ่านเพิ่มเติม";
                moreText.style.display = "none";
            } else {
                dots.style.display = "none";
                btnText.innerHTML = "แสดงน้อยลง";
                moreText.style.display = "inline";
            }
        }
    </script>
</body>

</html>