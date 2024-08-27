<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รวมการเชื่อมต่อฐานข้อมูล
include 'connectdb.php';

if (!isset($_GET['user_id'])) {
    die("User ID not provided.");
}

$user_id = intval($_GET['user_id']);

$sql = "
    SELECT users.full_name, users.email, users.birthday, users.phone_number, users.image, users.description,
            address.house_no, address.village_no, address.sub_area, address.area, address.province, address.postal_code
      FROM users
      JOIN address ON users.user_id = address.user_id
      WHERE users.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
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

        .profile-container {
            margin-top: 20px;
        }

        .profile-details {
            margin-bottom: 15px;
        }

        .profile-picture {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.2em;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container" style="margin-left: 220px;">
        <div class="d-flex justify-content-between align-items-center my-4">
            <h1>MED TIME</h1>
            <div>
                <a href="users.php" class="btn btn-secondary me-2">Back</a>
                <a href="logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>

        <div class="container">
            <div class="profile-container">
                <div class="profile-details">
                    <strong>Profile Picture:</strong><br>
                    <?php if (!empty($user['image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture">
                            <span>Profile</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <strong>Username:</strong> <?php echo htmlspecialchars($user['full_name'] ?? ''); ?> <br />
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?> <br />
                    <strong>Birthday:</strong> <?php echo htmlspecialchars($user['birthday'] ?? ''); ?> <br />
                    <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number'] ?? ''); ?> <br />
                    <strong>Description:</strong> <?php echo htmlspecialchars($user['description'] ?? ''); ?> <br />
                    <strong>house_no:</strong> <?php echo htmlspecialchars($user['house_no'] ?? ''); ?> <br />
                    <strong>village_no:</strong> <?php echo htmlspecialchars($user['village_no'] ?? ''); ?> <br />
                    <strong>sub_area:</strong> <?php echo htmlspecialchars($user['sub_area'] ?? ''); ?> <br />
                    <strong>area:</strong> <?php echo htmlspecialchars($user['area'] ?? ''); ?> <br />
                    <strong>province:</strong> <?php echo htmlspecialchars($user['province'] ?? ''); ?> <br />
                    <strong>postal_code:</strong> <?php echo htmlspecialchars($user['postal_code'] ?? ''); ?> <br />
                </div>
                <a href="editprofile.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>
</body>

</html>
