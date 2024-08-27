<?php
session_start();

if (!isset($_SESSION['pharmacist'])) {
    header("Location: users.php");
    exit();
}

require_once 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $birthday = $_POST['birthday'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (full_name, birthday, phone_number, email, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $full_name, $birthday, $phone_number, $email, $password);

    if ($stmt->execute()) {
        // Redirect to another page after successful insert
        header("Location: users.php?status=success");
        exit();
    } else {
        // Redirect to the same page with an error status
        header("Location: adduser.php?status=error");
        exit();
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
    <title>Add User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h1>Add User</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="full_name" class="form-label">ชื่อสมาชิก</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">วัน/เดือน/ปี</label>
                <input type="date" class="form-control" id="birthday" name="birthday" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="users.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
    <!-- Display status message if any -->
    <?php if (isset($_GET['status'])): ?>
        <script>
            var status = "<?php echo $_GET['status']; ?>";
            if (status === 'success') {
                alert('User added successfully');
            } else if (status === 'error') {
                alert('Error adding user');
            }
        </script>
    <?php endif; ?>
</body>
</html>
