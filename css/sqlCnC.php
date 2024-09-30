<?php
require 'connectdb.php';
session_start();

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['pharmacist_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง user_id มาหรือไม่
if (!isset($_GET['user_id'])) {
    die("Error: No user selected for the chat.");
}

$userId = $_GET['user_id'];
$pharmacistId = $_SESSION['pharmacist_id'];

// ดึงประวัติการสนทนา
$query = "
  SELECT m.sender_type, m.text, m.created_at, m.image 
  FROM messages m
  JOIN conversations c ON m.conversation_id = c.id
  WHERE c.user_id = ? AND c.pharmacist_id = ?
  ORDER BY m.created_at ASC
";

if (isset($_GET['ajax_search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchResult = searchMedicines($conn, $search);

    header('Content-Type: application/json');

    if ($searchResult) {
        $medicines = array();
        while ($row = $searchResult->fetch_assoc()) {
            $medicines[] = $row;
        }
        echo json_encode($medicines);
    } else {
        echo json_encode([]);
    }
    exit();
}
// ฟังก์ชันสำหรับการค้นหายา
function searchMedicines($conn, $search) {
    $search = $conn->real_escape_string($search);
    $sql = "SELECT m.*, GROUP_CONCAT(mt.name_time SEPARATOR ', ') AS timings
            FROM medicine m
            LEFT JOIN medicine_time mt ON m.medicine_id = mt.medicine_id
            WHERE m.medicine_name LIKE '%$search%'
            GROUP BY m.medicine_id";
    return $conn->query($sql);
}

// ตรวจสอบว่าเป็นการเรียก AJAX สำหรับการค้นหาหรือไม่
if (isset($_GET['ajax_search'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchResult = searchMedicines($conn, $search);
    
    // ส่งผลลัพธ์กลับเป็น JSON
    $medicines = array();
    while ($row = $searchResult->fetch_assoc()) {
        $medicines[] = $row;
    }
    echo json_encode($medicines);
    exit();
}

// การจัดการคำขอ POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // การจัดการเพิ่มสินค้า
    if (isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity <= 0) {
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "จำนวนสินค้าต้องมากกว่าศูนย์"]);
            exit();
        }

        $sql = "SELECT * FROM medicine WHERE medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $medicine_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $medicine = $result->fetch_assoc();
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['medicine_id'] == $medicine_id) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $_SESSION['cart'][] = [
                    'medicine_id' => $medicine['medicine_id'],
                    'medicine_name' => $medicine['medicine_name'],
                    'price' => $medicine['price'],
                    'quantity' => $quantity,
                ];
            }
            echo json_encode(["status" => "success", "message" => "เพิ่ม " . htmlspecialchars($medicine['medicine_name']) . " จำนวน $quantity ลงในตะกร้าของคุณแล้ว"]);
        } else {
            echo json_encode(["status" => "error", "message" => "ไม่พบยา"]);
        }
        exit();
    }

    // การจัดการอัปเดตจำนวนสินค้า
    if (isset($_POST['update_quantity']) && isset($_POST['medicine_id']) && isset($_POST['quantity'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity <= 0) {
            echo json_encode(["status" => "error", "message" => "จำนวนสินค้าต้องมากกว่าศูนย์"]);
            exit();
        }

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['medicine_id'] == $medicine_id) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        echo json_encode(["status" => "success", "message" => "อัปเดตจำนวนสินค้าเรียบร้อยแล้ว"]);
        exit();
    }

    // การจัดการลบสินค้าออกจากตะกร้า
    if (isset($_POST['remove_from_cart']) && isset($_POST['medicine_id'])) {
        $medicine_id = $_POST['medicine_id'];

        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['medicine_id'] == $medicine_id) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                echo json_encode(["status" => "success", "message" => "ลบสินค้าจากตะกร้าเรียบร้อยแล้ว"]);
                exit();
            }
        }
        echo json_encode(["status" => "error", "message" => "ไม่พบสินค้าในตะกร้า"]);
        exit();
    }
}


// ดึงข้อมูลยาทั้งหมดจากฐานข้อมูล รวมเวลาการใช้ยา
$sql = "SELECT m.*, GROUP_CONCAT(mt.name_time SEPARATOR ', ') AS timings
        FROM medicine m
        LEFT JOIN medicine_time mt ON m.medicine_id = mt.medicine_id
        GROUP BY m.medicine_id";
$result = $conn->query($sql);


$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $pharmacistId);
$stmt->execute();
$result = $stmt->get_result();
?>