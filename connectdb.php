<?php

$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "takecare";

// Create connection
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>