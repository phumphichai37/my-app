<?php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
</head>
<body>
    <h1>Error</h1>
    <p><?php echo $message; ?></p>
</body>
</html>
