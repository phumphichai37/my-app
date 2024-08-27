<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$hostName = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbName = $_ENV['DB_NAME'];

$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
