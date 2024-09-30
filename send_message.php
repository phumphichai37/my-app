<?php
require 'connectdb.php';
session_start();

if (!isset($_SESSION['pharmacist_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_POST['user_id'] ?? null;
$pharmacistId = $_SESSION['pharmacist_id'];
$message = trim($_POST['message'] ?? '');
$base64Image = $_POST['compressedImage'] ?? null;

if (!$userId) {
    die("Error: ไม่พบข้อมูลผู้ใช้");
}

// ดึง conversation_id
$query = "SELECT id FROM conversations WHERE user_id = ? AND pharmacist_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $pharmacistId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = mysqli_fetch_assoc($result)) {
    $conversationId = $row['id'];
} else {
    $insertQuery = "INSERT INTO conversations (user_id, pharmacist_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $userId, $pharmacistId);
    $stmt->execute();
    $conversationId = $stmt->insert_id;
}

// ตรวจสอบว่ามีข้อความหรือรูปภาพอย่างน้อยหนึ่งอย่าง
if (empty($message) && empty($base64Image)) {
    die("Error: ต้องมีข้อความหรือรูปภาพ");
}

if (!empty($base64Image)) {
    $base64Image = preg_replace('#^data:image/\\w+;base64,#i', '', $base64Image);
}

// เพิ่มข้อความหรือรูปภาพลงในฐานข้อมูล
$insertMessageQuery = "INSERT INTO messages (conversation_id, sender_type, sender_id, text, image) VALUES (?, 'pharmacist', ?, ?, ?)";
$stmt = $conn->prepare($insertMessageQuery);
$stmt->bind_param("iiss", $conversationId, $pharmacistId, $message, $base64Image);

if (!$stmt->execute()) {
    die("Error: ไม่สามารถส่งข้อความได้ " . $stmt->error);
}

echo "ส่งข้อความสำเร็จ!";
