<?php
session_start();

if (!isset($_SESSION["pharmacist"])) {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/html; charset=utf-8');
include 'connectdb.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ฟังก์ชันสำหรับดึงข้อมูลออเดอร์
function getOrders($search = '')
{
    global $conn;
    $sql = "SELECT * FROM orders";
    if (!empty($search)) {
        $sql .= " WHERE order_id LIKE ?";
    }
    $sql .= " ORDER BY order_time DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param("s", $searchParam);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function deleteOrder($order_id)
{
    global $conn;
    $sql = "DELETE FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    return $stmt->execute();
}

// ฟังก์ชันสำหรับนับจำนวนออเดอร์ตามสถานะ
function getOrderCounts()
{
    global $conn;
    $sql = "
        SELECT 
            status_payment, 
            COUNT(*) as count 
        FROM 
            orders 
        GROUP BY 
            status_payment";

    $result = $conn->query($sql);
    $counts = [];

    while ($row = $result->fetch_assoc()) {
        $counts[$row['status_payment']] = $row['count'];
    }

    return $counts;
}

if (isset($_POST['delete_order_ids'])) {
    $order_ids = $_POST['delete_order_ids']; // เป็นอาร์เรย์ของ ID
    $deletedOrders = 0;

    foreach ($order_ids as $order_id) {
        if (deleteOrder(intval($order_id))) {
            $deletedOrders++;
        }
    }

    if ($deletedOrders > 0) {
        echo "<script>alert('ลบออเดอร์ $deletedOrders รายการสำเร็จ');</script>";
        // รีเฟรชหน้าเพื่อป้องกันการทำซ้ำของ POST
        echo "<script>window.location.href = 'status.php';</script>";
        exit(); // ป้องกันการทำงานต่อหลังจากลบ
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบออเดอร์');</script>";
    }
}



if (isset($_POST['delete_order_id'])) {
    $order_id = intval($_POST['delete_order_id']);
    if (deleteOrder($order_id)) {
        echo "<script>alert('ลบออเดอร์สำเร็จ');</script>";
        // รีเฟรชหน้าเพื่อป้องกันการทำซ้ำของ POST
        echo "<script>window.location.href = 'status.php';</script>";
        exit(); // ป้องกันการทำงานต่อหลังจากลบ
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบออเดอร์');</script>";
    }
}


function getOrderDetails($order_id)
{
    global $conn;

    // เริ่มการบันทึก error
    error_log("Starting getOrderDetails for order_id: " . $order_id);

    $sql = "SELECT o.*, 
                   COALESCE(u.full_name, 'ไม่มี') AS full_name, 
                   GROUP_CONCAT(CONCAT(od.quantity, 'x ', m.medicine_name) SEPARATOR ', ') AS items
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            JOIN order_details od ON o.order_id = od.order_id
            JOIN medicine m ON od.medicine_id = m.medicine_id
            WHERE o.order_id = ?
            GROUP BY o.order_id";

    error_log("SQL Query: " . $sql);

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("No rows found for order_id: " . $order_id);
        return false;
    }

    $order_details = $result->fetch_assoc();
    if (!$order_details) {
        error_log("Failed to fetch order details for order_id: " . $order_id);
        return false;
    }

    error_log("Successfully retrieved order details for order_id: " . $order_id);
    error_log("Order details: " . print_r($order_details, true));

    return $order_details;
}

// ตรวจสอบการส่งฟอร์มค้นหา
$search = isset($_GET['search']) ? $_GET['search'] : '';
$orders = getOrders($search);
$orderCounts = getOrderCounts(); // ดึงข้อมูลการนับออเดอร์
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <title>Order Tracking</title>
    <style>
        body {
            background: #f8f9fa;
            font-family: Arial, sans-serif;
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
            max-width: 1200px;
        }

        .navbar-info {
            background-color: #17a2b8;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 10px;
        }

        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            width: 220px;
            height: calc(100% - 56px);
            background-color: rgba(23, 162, 184, 0.9);
            border-right: 1px solid #ddd;
            z-index: 1000;
            overflow-y: auto;
            padding-top: 20px;
        }

        .sidebar .btn {
            background-color: #17a2b8;
            border: none;
            color: #fff;
            margin: 10px;
            width: calc(100% - 20px);
        }

        .sidebar .btn:hover {
            background-color: #138496;
        }

        @keyframes flipY {
            0% {
                transform: rotateY(0deg);
            }

            100% {
                transform: rotateY(360deg);
            }
        }

        .pharmacist-image {
            font-size: 100px;
            color: #fff;
            display: block;
            margin: 0 auto 20px;
            text-align: center;
            animation: flipY 3s infinite;
            /* หมุน 5 วินาที และสั่นทุกๆ 0.5 วินาที */
        }

        .status-card {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: white;
        }

        .pending {
            background-color: #dc3545;
        }

        .preparing {
            background-color: #0d6efd;
        }

        .shipping {
            background-color: #ffc107;
        }

        .completed {
            background-color: #198754;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        h1 {
            text-align: center;
        }

        @media print {

            /* Center the content */
            body {
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .order-details {
                width: 80%;
                /* Set the width you want */
                text-align: center;
                /* Center the text */
            }

            /* Hide buttons and unnecessary elements */
            button,
            .icons,
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
            <div>
                <a href="logout.php" class="btn btn-light">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <aside class="sidebar">
        <i class="fa-solid fa-truck pharmacist-image"></i>
        <a href="index.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-home"></i> หน้าหลัก
        </a>
        <a href="medicine.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-pills"></i> ยา
        </a>
        <a href="buy.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-store"></i> ร้านค้า
        </a>
        <a href="users.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-users"></i> ผู้ใช้งาน
        </a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-user"></i> ข้อมูลส่วนตัว
        </a>
        <a href="online.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-comment-dots"></i> แชท
        </a>
        <a href="status.php" class="btn btn-secondary me-2">
            <i class="fa-solid fa-truck"></i> สถานะ
        </a>
    </aside>

    <div class="container mt-4">
        <h2 class="mb-4">ติดตามรายการสินค้า</h2>

        <div class="row">
            <div class="col-md-3">
                <div class="status-card pending">
                    <h5>รอการอนุมัติ</h5>
                    <p class="mb-0" id="pending-count"><?php echo isset($orderCounts['รอการอนุมัติ']) ? $orderCounts['รอการอนุมัติ'] : 0; ?> ออเดอร์</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="status-card preparing">
                    <h5>กำลังจัดเตรียมสินค้า</h5>
                    <p class="mb-0" id="preparing-count"><?php echo isset($orderCounts['กำลังจัดเตรียมสินค้า']) ? $orderCounts['กำลังจัดเตรียมสินค้า'] : 0; ?> ออเดอร์</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="status-card shipping">
                    <h5>กำลังจัดส่ง</h5>
                    <p class="mb-0" id="shipping-count"><?php echo isset($orderCounts['กำลังจัดส่ง']) ? $orderCounts['กำลังจัดส่ง'] : 0; ?> ออเดอร์</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="status-card completed">
                    <h5>จัดส่งเสร็จสิ้น</h5>
                    <p class="mb-0" id="completed-count"><?php echo isset($orderCounts['จัดส่งเสร็จสิ้น']) ? $orderCounts['จัดส่งเสร็จสิ้น'] : 0; ?> ออเดอร์</p>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">ค้นหาออเดอร์</h5>
                <form action="" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="ค้นหาด้วยไอดีออเดอร์" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">รายการออเดอร์</h5>
                <form method="POST" id="deleteOrdersForm">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th> <!-- Checkbox to select all -->
                                    <th>รหัสออเดอร์</th>
                                    <th>เวลาสั่งซื้อ</th>
                                    <th>สถานะการชำระเงิน</th>
                                    <th>ราคา</th>
                                    <th>การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $orders->fetch_assoc()): ?>
                                    <tr class="order-row" data-order-id="<?php echo $row['order_id']; ?>">
                                        <td><input type="checkbox" name="delete_order_ids[]" value="<?php echo $row['order_id']; ?>"></td> <!-- Checkbox for each order -->
                                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['order_time']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_info']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                                        <td>
                                            <select class="form-select" id="status-<?php echo $row['order_id']; ?>">
                                                <option value="รอการอนุมัติ" <?php echo $row['status_payment'] == 'รอการอนุมัติ' ? 'selected' : ''; ?>>รอการอนุมัติ</option>
                                                <option value="กำลังจัดเตรียมสินค้า" <?php echo $row['status_payment'] == 'กำลังจัดเตรียมสินค้า' ? 'selected' : ''; ?>>กำลังจัดเตรียมสินค้า</option>
                                                <option value="กำลังจัดส่ง" <?php echo $row['status_payment'] == 'กำลังจัดส่ง' ? 'selected' : ''; ?>>กำลังจัดส่ง</option>
                                                <option value="จัดส่งเสร็จสิ้น" <?php echo $row['status_payment'] == 'จัดส่งเสร็จสิ้น' ? 'selected' : ''; ?>>จัดส่งเสร็จสิ้น</option>
                                            </select>
                                            <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo $row['order_id']; ?>)">อัปเดตสถานะ</button>
                                            <button type="button" class="btn btn-info btn-sm" onclick="showOrderDetails(<?php echo $row['order_id']; ?>)">ดูรายละเอียด</button>

                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">ลบที่เลือก</button> <!-- Button to delete selected orders -->
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for order details -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h1>ร้าน IT Pharmacy</h1>
                <div id="orderDetails"></div>
                <button class="no-print" onclick="printOrder()">Print</button>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function updateStatus(orderId) {
                var status = document.getElementById("status-" + orderId).value;

                // Send an AJAX request to update the status
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_order_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert("อัปเดตสถานะสำเร็จ");
                        location.reload(); // Reload the page to see the updated status
                    }
                };
                xhr.send("order_id=" + orderId + "&status=" + encodeURIComponent(status));
            }

            function showOrderDetails(orderId) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "get_orders.php?order_id=" + orderId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        try {
                            var orderDetails = JSON.parse(xhr.responseText);
                            if (orderDetails) {
                                var detailsHtml = `
                        <p><strong>ชื่อผู้สั่งซื้อ:</strong> ${orderDetails.full_name || 'ไม่มี'}</p>
                        <p><strong>รหัสออเดอร์:</strong> ${orderDetails.order_id}</p>
                        <p><strong>เวลาสั่งซื้อ:</strong> ${orderDetails.order_time}</p>
                        <p><strong>สถานะการชำระเงิน:</strong> ${orderDetails.payment_info}</p>
                        <p><strong>ราคารวม:</strong> ${orderDetails.total_price}</p>
                        <p><strong>รายการสินค้า:</strong> ${orderDetails.items}</p>
                        <p><strong>สถานะสินค้า:</strong> ${orderDetails.status_payment}</p>`;
                                document.getElementById("orderDetails").innerHTML = detailsHtml;
                                document.getElementById("orderModal").style.display = "block";
                            } else {
                                alert("ไม่พบข้อมูลออเดอร์");
                            }
                        } catch (e) {
                            console.error("Error parsing JSON:", e.message);
                            alert("เกิดข้อผิดพลาดในการแสดงรายละเอียดออเดอร์: " + e.message);
                        }
                    }
                };
                xhr.send();
            }


            // ปิด Modal
            var modal = document.getElementById("orderModal");
            var span = document.getElementsByClassName("close")[0];
            span.onclick = function() {
                modal.style.display = "none";
            }
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            function printOrder() {
                window.print();
            }

            function confirmDelete() {
                const checkboxes = document.querySelectorAll('input[name="delete_order_ids[]"]:checked');
                if (checkboxes.length === 0) {
                    alert("กรุณาเลือกออเดอร์ที่ต้องการลบ");
                    return;
                }

                const confirmation = confirm("คุณต้องการลบออเดอร์ที่เลือกหรือไม่?");
                if (confirmation) {
                    document.getElementById('deleteOrdersForm').submit(); // Submit the form
                }
            }

            document.getElementById('selectAll').onclick = function() {
                const checkboxes = document.querySelectorAll('input[name="delete_order_ids[]"]');
                for (let checkbox of checkboxes) {
                    checkbox.checked = this.checked;
                }
            }
        </script>
</body>

</html>