<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['pharmacist'])) {
    header("Location: login.php");
    exit();
}

// รวมการเชื่อมต่อฐานข้อมูล
include 'connectdb.php';

$message = "";

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่ามีการส่งข้อมูลในฟอร์ม
    if (isset($_POST['itemName'], $_POST['itemDescription'], $_POST['itemType'], $_POST['itemPrice'], $_POST['itemStock'])) {
        $itemName = $_POST['itemName'];
        $itemDescription = $_POST['itemDescription'];
        $itemType = $_POST['itemType'];
        $itemPrice = $_POST['itemPrice'];
        $itemStock = $_POST['itemStock'];

        // จัดการการอัปโหลดรูปภาพ
        if (isset($_FILES['file']) && isset($_FILES['file']['error']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($_FILES['file']['tmp_name']);
            if (strpos($fileType, 'image/') === 0) {
                // อ่านข้อมูลรูปภาพ
                $imgData = file_get_contents($_FILES['file']['tmp_name']);

                // สร้างภาพจากข้อมูล
                $imageResource = imagecreatefromstring($imgData);

                // ตั้งค่าขนาดภาพใหม่ (ลดขนาด)
                $newWidth = 800;  // ความกว้างใหม่
                $newHeight = 600; // ความสูงใหม่
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                // ปรับขนาดภาพ
                imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($imageResource), imagesy($imageResource));

                // บันทึกภาพในรูปแบบ JPEG และลดคุณภาพ (0-100)
                ob_start(); // เริ่มการบันทึกข้อมูลเอาต์พุต
                imagejpeg($resizedImage, null, 75); // ลดคุณภาพที่ 75%
                $compressedData = ob_get_contents(); // รับข้อมูลที่บันทึก
                ob_end_clean(); // ล้างข้อมูลที่บันทึก

                // แปลงข้อมูลเป็น Base64
                $image = base64_encode($compressedData);

                // ปิดการใช้งานทรัพยากร
                imagedestroy($imageResource);
                imagedestroy($resizedImage);

                // เพิ่มข้อมูลลงในฐานข้อมูล
                $sql = "INSERT INTO medicine (medicine_name, description, type, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssdis', $itemName, $itemDescription, $itemType, $itemPrice, $itemStock, $image);

                if ($stmt->execute()) {
                    $medicine_id = $stmt->insert_id;

                    // Handle the timings
                    $itemTiming = isset($_POST['itemTiming']) ? $_POST['itemTiming'] : [];
                    if (!empty($itemTiming)) {
                        $timingSql = "INSERT INTO medicine_time (name_time, medicine_id) VALUES (?, ?)";
                        $timingStmt = $conn->prepare($timingSql);

                        foreach ($itemTiming as $timing) {
                            if ($timing === 'other') {
                                $otherTiming = isset($_POST['otherTimingInput']) ? trim($_POST['otherTimingInput']) : '';
                                $otherTiming = htmlspecialchars($otherTiming);
                                if (!empty($otherTiming)) {
                                    $timingStmt->bind_param('si', $otherTiming, $medicine_id);
                                    $timingStmt->execute();
                                }
                            } else {
                                $timing = htmlspecialchars($timing);
                                $timingStmt->bind_param('si', $timing, $medicine_id);
                                $timingStmt->execute();
                            }
                        }
                        $timingStmt->close();
                    }
                    $message = "เพิ่มรายการ '$itemName' สำเร็จ!";
                } else {
                    $message = "ไม่สามารถเพิ่มรายการได้: " . $conn->error;
                }
                $stmt->close();
            } else {
                $message = "ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ.";
            }
        } else {
            $message = "โปรดอัปโหลดรูปภาพ.";
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql = "DELETE FROM medicine WHERE medicine_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $message = "ลบรายการสำเร็จ!";

        // ดึงข้อมูลใหม่หลังจากทำการลบ
        echo "<script>
                alert('$message');
                window.location.href = 'medicine.php'; // รีเฟรชหน้าเพจหลังจากลบเสร็จ
              </script>";
    } else {
        $message = "ไม่สามารถลบรายการได้: " . $conn->error;
    }

    $stmt->close();
    exit();
}

// ตรวจสอบว่ามีการสร้าง `$result` แล้วก่อนใช้งาน
if (isset($result) && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // ดำเนินการแสดงข้อมูลที่ดึงมา
    }
} else {
    echo "ไม่พบข้อมูลยา";
}


// ค้นหายา
$searchQuery = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchQuery = " WHERE m.medicine_name LIKE '%$search%' ";
}

// Retrieve medicine and their timings
$sql = "SELECT m.*, GROUP_CONCAT(mt.name_time SEPARATOR ', ') AS timings
        FROM medicine m
        LEFT JOIN medicine_time mt ON m.medicine_id = mt.medicine_id
        $searchQuery
        GROUP BY m.medicine_id";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            margin-top: 35;


        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-left: 220px;

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

        .medicine-item {
            margin-bottom: 20px;
        }

        .medicine-item img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table img {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            /* หรือขนาดที่คุณต้องการ */
            object-fit: cover;
            object-position: center;
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
        <i class="fa-solid fa-pills pharmacist-image"></i>
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

    <div class="container">
        <div class="mb-4">
            <h2>เพิ่มรายการยา</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="itemName" class="form-label">ชื่อยา</label>
                    <input type="text" class="form-control" id="itemName" name="itemName" required>
                </div>
                <div class="mb-3">
                    <label for="itemDescription" class="form-label">รายละเอียด</label>
                    <input type="text" class="form-control" id="itemDescription" name="itemDescription" required>
                </div>
                <div class="mb-3">
                    <label for="itemType" class="form-label">ประเภทยา</label>
                    <input type="text" class="form-control" id="itemType" name="itemType" required>
                </div>
                <div class="mb-3">
                    <label for="itemPrice" class="form-label">ราคา</label>
                    <input type="number" step="0.01" class="form-control" id="itemPrice" name="itemPrice" required>
                </div>
                <div class="mb-3">
                    <label for="itemStock" class="form-label">จำนวนสินค้า</label>
                    <input type="number" class="form-control" id="itemStock" name="itemStock" required>
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">เลือกรูปภาพ</label>
                    <input type="file" class="form-control" name="file" id="file" accept="image/*">
                </div>
                <label class="form-label">เวลาที่ใช้ยา</label><br>
                <div>
                    <input type="checkbox" id="beforeBreakfast" name="itemTiming[]" value="ก่อนอาหารเช้า">
                    <label for="beforeBreakfast">ก่อนอาหารเช้า</label>
                    <input type="checkbox" id="afterBreakfast" name="itemTiming[]" value="หลังอาหารเช้า">
                    <label for="afterBreakfast">หลังอาหารเช้า</label>
                    <input type="checkbox" id="beforeLunch" name="itemTiming[]" value="ก่อนอาหารกลางวัน">
                    <label for="beforeLunch">ก่อนอาหารกลางวัน</label>
                    <input type="checkbox" id="afterLunch" name="itemTiming[]" value="หลังอาหารกลางวัน">
                    <label for="afterLunch">หลังอาหารกลางวัน</label>
                    <input type="checkbox" id="beforeDinner" name="itemTiming[]" value="ก่อนอาหารเย็น">
                    <label for="beforeDinner">ก่อนอาหารเย็น</label>
                    <input type="checkbox" id="afterDinner" name="itemTiming[]" value="หลังอาหารเย็น">
                    <label for="afterDinner">หลังอาหารเย็น</label>
                    <input type="checkbox" id="beforesleep" name="itemTiming[]" value="ก่อนนอน">
                    <label for="beforesleep">ก่อนนอน</label>
                    <div>
                        <input type="checkbox" id="otherTiming" name="itemTiming[]" value="other" onclick="toggleOtherTiming()">
                        <label for="otherTiming">อื่นๆ</label>
                        <input type="text" id="otherTimingInput" name="otherTimingInput" style="display:none;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">เพิ่มยา</button>
            </form>
        </div>

        <div class="mb-4">
            <h2>ค้นหารายการยา</h2>
            <form method="get" action="medicine.php">
                <div class="mb-3">
                    <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อยา">
                </div>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </form>
        </div>

        <h2>รายการยา</h2>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6 medicine-item">
                    <div class="card text-black h-100">
                        <img src="<?php
                                    $image = $row['image'];
                                    echo (strpos($image, 'http') === 0 || strpos($image, 'https') === 0)
                                        ? $image // ถ้าเป็น URL
                                        : 'data:image/*;base64,' . $image;
                                    ?>"
                            class="card-img-top"
                            alt="<?php echo $row['medicine_name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row["medicine_name"]; ?></h5>
                            <p class="card-text">
                                <strong>รายละเอียด:</strong>
                                <span class="short-description"><?php echo mb_substr(htmlspecialchars($row["description"]), 0, 50); ?>...</span>
                                <span class="full-description" style="display:none;"><?php echo htmlspecialchars($row["description"]); ?></span>
                                <button class="btn btn-link read-more" onclick="toggleDescription(this)">อ่านเพิ่มเติม</button>
                            </p>



                            <p class="card-text"><strong>ประเภทยา:</strong> <?php echo $row["type"]; ?></p>
                            <p class="card-text"><strong>ราคา:</strong> <?php echo number_format($row["price"], 2); ?> บาท</p>
                            <p class="card-text"><strong>จำนวนสต็อก:</strong> <?php echo $row["stock"]; ?> ชิ้น</p>
                            <div>
                                <strong>เวลาใช้ยา:</strong>
                                <?php if (!empty($row["timings"])): ?>
                                    <ul>
                                        <?php
                                        $timings = explode(', ', $row["timings"]);
                                        foreach ($timings as $timing):
                                            // ตรวจสอบและข้ามการแสดงผล 'other'
                                            if ($timing !== 'other'):
                                        ?>
                                                <li><?php echo htmlspecialchars($timing); ?></li>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </ul>
                                <?php else: ?>
                                    <p>ไม่มีข้อมูล</p>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-warning mt-2 edit-button"
                                data-id="<?php echo $row["medicine_id"]; ?>"
                                data-name="<?php echo htmlspecialchars($row["medicine_name"]); ?>"
                                data-description="<?php echo htmlspecialchars($row["description"]); ?>"
                                data-type="<?php echo htmlspecialchars($row["type"]); ?>"
                                data-price="<?php echo $row["price"]; ?>"
                                data-stock="<?php echo $row["stock"]; ?>"
                                data-timings="<?php echo htmlspecialchars($row["timings"]); ?>">
                                แก้ไข
                            </button>
                            <a href="medicine.php?delete=<?php echo $row['medicine_id']; ?>" class="btn btn-danger mt-2">ลบ</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editMedicineForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูลยา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="medicineId" name="medicineId">
                        <div class="mb-3">
                            <label for="editItemName" class="form-label">ชื่อยา</label>
                            <input type="text" class="form-control" id="editItemName" name="editItemName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editItemDescription" class="form-label">รายละเอียด</label>
                            <input type="text" class="form-control" id="editItemDescription" name="editItemDescription" required>
                        </div>
                        <div class="mb-3">
                            <label for="editItemType" class="form-label">ประเภทยา</label>
                            <input type="text" class="form-control" id="editItemType" name="editItemType" required>
                        </div>
                        <div class="mb-3">
                            <label for="editItemPrice" class="form-label">ราคา</label>
                            <input type="number" step="0.01" class="form-control" id="editItemPrice" name="editItemPrice" required>
                        </div>
                        <div class="mb-3">
                            <label for="editItemStock" class="form-label">จำนวนสินค้า</label>
                            <input type="number" class="form-control" id="editItemStock" name="editItemStock" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลาที่ใช้ยา</label><br>
                            <div id="editTimings">
                                <input type="checkbox" id="editBeforeBreakfast" name="editItemTiming[]" value="ก่อนอาหารเช้า">
                                <label for="editBeforeBreakfast">ก่อนอาหารเช้า</label>
                                <input type="checkbox" id="editAfterBreakfast" name="editItemTiming[]" value="หลังอาหารเช้า">
                                <label for="editAfterBreakfast">หลังอาหารเช้า</label>
                                <input type="checkbox" id="editBeforLaunch" name="editItemTiming[]" value="ก่อนอาหารกลางวัน">
                                <label for="editBeforLaunch">ก่อนอาหารกลางวัน</label>
                                <input type="checkbox" id="editAfterLaunch" name="editItemTiming[]" value="หลังอาหารกลางวัน">
                                <label for="editAfterLaunch">หลังอาหารกลางวัน</label>
                                <input type="checkbox" id="editBeforBreakfast" name="editItemTiming[]" value="ก่อนอาหารเย็น">
                                <label for="editBeforBreakfast">ก่อนอาหารเย็น</label>
                                <input type="checkbox" id="editAfterDinnerS" name="editItemTiming[]" value="หลังอาหารเย็น">
                                <label for="editAfterDinner">หลังอาหารเย็น</label>
                                <input type="checkbox" id="editBeforSleep" name="editItemTiming[]" value="ก่อนนอน">
                                <label for="editBeforSleep">ก่อนนอน</label>
                                <div>
                                    <input type="checkbox" id="editOtherTiming" name="editItemTiming[]" value="other" onclick="toggleEditOtherTiming()">
                                    <label for="editOtherTiming">อื่นๆ</label>
                                    <input type="text" id="editOtherTimingInput" name="editOtherTimingInput" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
        </div>



        <script>
            $(document).ready(function() {
                $(document).on('click', '.edit-button', function() {
                    console.log('Edit button clicked');
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var description = $(this).data('description');
                    var type = $(this).data('type');
                    var price = $(this).data('price');
                    var stock = $(this).data('stock');
                    var timings = $(this).data('timings').split(', ');

                    $('#medicineId').val(id);
                    $('#editItemName').val(name);
                    $('#editItemDescription').val(description);
                    $('#editItemType').val(type);
                    $('#editItemPrice').val(price);
                    $('#editItemStock').val(stock);

                    // รีเซ็ตการเลือกเวลาใช้ยา
                    $('input[name="editItemTiming[]"]').prop('checked', false);
                    $('#editOtherTimingInput').val('').hide();

                    // ตั้งค่าการเลือกเวลาใช้ยาตามข้อมูลที่มีอยู่
                    timings.forEach(function(timing) {
                        var checkbox = $('input[name="editItemTiming[]"][value="' + timing + '"]');
                        if (checkbox.length) {
                            checkbox.prop('checked', true);
                        } else if (timing !== '') {
                            $('#editOtherTiming').prop('checked', true);
                            $('#editOtherTimingInput').val(timing).show();
                        }
                    });

                    $('#editModal').modal('show');
                });

                $('#editMedicineForm').on('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: 'update_medicine.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            console.log(response);
                            alert('การแก้ไขสำเร็จ');
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                            alert('เกิดข้อผิดพลาด: ' + error);
                        }
                    });
                });
            });

            function toggleEditOtherTiming() {
                const otherInput = document.getElementById("editOtherTimingInput");
                const otherCheckbox = document.getElementById("editOtherTiming");
                otherInput.style.display = otherCheckbox.checked ? "inline-block" : "none";
                if (!otherCheckbox.checked) {
                    otherInput.value = '';
                }
            }

            function toggleOtherTiming() {
                const otherInput = document.getElementById("otherTimingInput");
                const otherCheckbox = document.getElementById("otherTiming");
                if (otherCheckbox.checked) {
                    otherInput.style.display = "inline-block";
                } else {
                    otherInput.style.display = "none";
                    otherInput.value = '';
                }
            }

            function toggleDescription(button) {
                const shortDescription = button.parentElement.querySelector('.short-description');
                const fullDescription = button.parentElement.querySelector('.full-description');

                if (fullDescription.style.display === 'none') {
                    fullDescription.style.display = 'inline';
                    shortDescription.style.display = 'none';
                    button.textContent = 'ย่อ';
                } else {
                    fullDescription.style.display = 'none';
                    shortDescription.style.display = 'inline';
                    button.textContent = 'อ่านเพิ่มเติม';
                }
            }
        </script>
</body>

</html>