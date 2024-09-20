<?php
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

// ตรวจสอบการลบออเดอร์
if (isset($_POST['delete_order_id'])) {
    $order_id = intval($_POST['delete_order_id']);
    if (deleteOrder($order_id)) {
        echo "<script>alert('ลบออเดอร์สำเร็จ');</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบออเดอร์');</script>";
    }
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
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-info">
        <div class="container-fluid">
            <h5 class="text-white">TAKECARE</h5>
            <div>
                <a href="users.php" class="btn btn-light me-2">ย้อนกลับ</a>
                <a href="logout.php" class="btn btn-light">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <aside class="sidebar">
        <a href="index.php" class="btn btn-secondary me-2">หน้าหลัก</a>
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
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
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>รหัสออเดอร์</th>
                                <th>เวลาสั่งซื้อ</th>
                                <th>สถานะการชำระเงิน</th>
                                <th>ราคา</th>
                                <th>สถานะออเดอร์</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_info']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status_payment']); ?></td>
                                    <td>
                                        <select class="form-select" id="status-<?php echo $row['order_id']; ?>">
                                            <option value="รอการอนุมัติ" <?php echo $row['status_payment'] == 'รอการอนุมัติ' ? 'selected' : ''; ?>>รอการอนุมัติ</option>
                                            <option value="กำลังจัดเตรียมสินค้า" <?php echo $row['status_payment'] == 'กำลังจัดเตรียมสินค้า' ? 'selected' : ''; ?>>กำลังจัดเตรียมสินค้า</option>
                                            <option value="กำลังจัดส่ง" <?php echo $row['status_payment'] == 'กำลังจัดส่ง' ? 'selected' : ''; ?>>กำลังจัดส่ง</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo $row['order_id']; ?>)">อัปเดตสถานะ</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="delete_order_id" value="<?php echo $row['order_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบออเดอร์นี้หรือไม่?');">ลบ</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
    </script>
</body>

</html>
