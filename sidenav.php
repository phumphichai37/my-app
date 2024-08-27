<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidenav Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .sidenav {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #17a2b8;
            padding-top: 20px;
        }
        .sidenav a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .sidenav a:hover {
            background-color: #0056b3;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidenav bg-info">
        <a href="admin.php" class="list-group-item list-group-item-action bg-info text-white">admin</a>
        <a href="madicine.php" class="list-group-item list-group-item-action bg-info text-white">madicine</a>
        <a href="user.php" class="list-group-item list-group-item-action bg-info text-white">user</a>
    </div>

    <div class="content">
        <h2>Main Content Area</h2>
        <p>This is where your main content will go. The sidenav remains fixed on the left.</p>
    </div>
</body>
</html>
