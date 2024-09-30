<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION["pharmacist"])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

// ฟังก์ชันที่ใช้ในการ Query ข้อมูล
function getQueryResult($conn, $query, $params = [], $types = "")
{
    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// คำนวณจำนวนผู้ใช้
$userQuery = "SELECT COUNT(user_id) AS user_count FROM users";
$userResult = getQueryResult($conn, $userQuery);
$userCount = $userResult->fetch_assoc()['user_count'];

// คำนวณรายเดือน
$revenueQuery = "SELECT SUM(total_price) AS monthly_total 
                 FROM orders 
                 WHERE DATE_FORMAT(order_time, '%Y-%m') = ?";
$revenueResult = getQueryResult($conn, $revenueQuery, [$selectedMonth], "s");
$monthlyTotal = $revenueResult ? (float)$revenueResult->fetch_assoc()['monthly_total'] : 0.0;

// คำนวณรายปี
$yearsQuery = "SELECT SUM(total_price) as year_total
               FROM orders
               WHERE DATE_FORMAT(order_time, '%Y') = ?";
$yearsResult = getQueryResult($conn, $yearsQuery, [$selectedYear], "s");
$yearstotal = $yearsResult ? (float)$yearsResult->fetch_assoc()['year_total'] : 0.0;

// คำนวณยอดรวมรายเดือนของแต่ละเดือน
$monthlyQuery = "SELECT DATE_FORMAT(order_time, '%Y-%m') AS order_month, SUM(total_price) AS monthly_total 
                 FROM orders 
                 WHERE DATE_FORMAT(order_time, '%Y') = ? 
                 GROUP BY order_month 
                 ORDER BY order_month ASC";
$monthlyResult = getQueryResult($conn, $monthlyQuery, [$selectedYear], "s");

if (!$monthlyResult) {
    error_log("Query failed: " . $conn->error); // บันทึกข้อผิดพลาด
    echo "ไม่สามารถดึงข้อมูลได้ กรุณาลองใหม่อีกครั้ง";
    exit();
}

// จัดการวันที่เพื่อใช้ในหน้าจอ
$date = DateTime::createFromFormat('Y-m', $selectedMonth);
$monthName = $date->format('F');
$year = $date->format('Y');
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
    <style>
        body {
            margin: 0;
            padding: 0;
            padding-left: 220px;
            padding-top: 56px;
        }

        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 20px;
        }

        .chart-wrapper {
            flex: 1;
            margin: 0 10px;
        }

        .form-control {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            font-size: medium;
        }

        input[type="month"],
        input[type="number"] {
            width: 180px;
        }

        h2 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        p {
            text-align: center;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .navbar-info,
            .sidebar {
                display: none;
            }

            .chart-container {
                display: block;
                padding: 0;
            }

            .chart-wrapper {
                margin: 0;
                page-break-inside: avoid;
            }

            h2,
            p {
                text-align: left;
                margin: 0;
                padding: 0;
            }

            .form-control {
                display: none;
            }

            .button {
                margin-right: auto;
            }
        }

        .user-count-box {
            border: 2px solid #17a2b8;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            max-width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .chart-box {
            border: 2px solid #17a2b8;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php
    include('css/navSide.php');
    ?>

    <main>
        <div class="user-count-box">
            <div style="display: flex; justify-content: center;">
                <i class="fa-solid fa-users" style="color: #17a2b8; font-size: 56px; margin-bottom: 15px;"></i>
            </div>
            <p>ยอดผู้ใช้งาน: <?php echo $userCount; ?> คน</p>
        </div>

        <div class="chart-container">
            <div class="chart-wrapper">
                <div class="chart-box">
                    <h2>สถิติการสั่งซื้อรายวัน</h2>
                    <label for="monthSelector">เลือกเดือนรายวัน:</label>
                    <input type="month" id="monthSelector" class="form-control" value="<?php echo $selectedMonth; ?>">
                    <p id="monthlyTotal">รายได้รวมของเดือน <?php echo $monthName . ' ' . $year; ?> : <?php echo number_format($monthlyTotal, 2); ?> บาท</p>
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <div class="chart-wrapper">
                <div class="chart-box">
                    <h2>สถิติรายได้รวมแต่ละเดือน</h2>
                    <label for="yearSelector">เลือกปี:</label>
                    <input type="number" id="yearSelector" class="form-control" value="<?php echo $selectedYear; ?>" min="2000" max="<?php echo date('Y'); ?>">
                    <p id="yearlyTotal">รายได้รวมของปี <?php echo $selectedYear; ?> : <?php echo number_format($yearstotal, 2); ?> บาท</p>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="container">
            <h2>สรุปรายได้รวมของแต่ละเดือน</h2>
            <button onclick="downloadDailyCSV()">Daily</button>
            <button onclick="downloadMonthlyCSV()">Monthly</button>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>เดือน</th>
                        <th>ยอดรวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $monthlyResult->fetch_assoc()):
                        $date = DateTime::createFromFormat('Y-m', $row['order_month']);
                        $monthName = $date->format('F');
                        $year = $date->format('Y');
                    ?>
                        <tr>
                            <td><?php echo $monthName . ' ' . $year; ?></td>
                            <td><?php echo number_format($row['monthly_total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.getElementById('monthSelector').addEventListener('change', function(event) {
            let selectedMonth = this.value;
            let url = new URL(window.location.href);
            url.searchParams.set('month', selectedMonth);
            window.history.pushState({}, '', url); // เปลี่ยน URL โดยไม่รีเฟรชหน้า
            updateChart('daily', selectedMonth);
        });

        document.getElementById('yearSelector').addEventListener('change', function(event) {
            let selectedYear = this.value;
            let url = new URL(window.location.href);
            url.searchParams.set('year', selectedYear);
            window.history.pushState({}, '', url); // เปลี่ยน URL โดยไม่รีเฟรชหน้า
            updateChart('monthly', selectedYear);
        });

        function updateChart(type, month = null, year = null) {
            let url = 'get_chart_data.php?type=' + type;
            if (month) {
                url += '&month=' + month;
            }
            if (year) {
                url += '&year=' + year;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (type === 'daily') {
                        console.log('Daily Data:', data);
                        var days = data.map(item => item.day);
                        var totals = data.map(item => item.total);

                        dailyChart.data.labels = days;
                        dailyChart.data.datasets[0].data = totals;
                        dailyChart.update();

                        // คำนวณยอดรวมรายวัน
                        let monthlyTotal = totals.reduce((acc, cur) => acc + cur, 0);
                        document.getElementById('monthlyTotal').innerText = 'รายได้รวมของเดือน ' + new Date(month).toLocaleString('default', {
                            month: 'long'
                        }) + ': ' + monthlyTotal.toFixed(2) + ' บาท';
                    } else if (type === 'monthly') {
                        console.log('Monthly Data:', data);
                        var months = data.map(item => item.month);
                        var monthlyTotals = data.map(item => item.total);

                        monthlyChart.data.labels = months;
                        monthlyChart.data.datasets[0].data = monthlyTotals;
                        monthlyChart.update();

                        // คำนวณยอดรวมรายปี
                        let yearlyTotal = monthlyTotals.reduce((acc, cur) => acc + cur, 0);
                        document.getElementById('yearlyTotal').innerText = 'รายได้รวมของปี ' + year + ': ' + yearlyTotal.toFixed(2) + ' บาท';
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });

        }

        const dailyChartCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyChartCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'ยอดรวมรายวัน',
                    data: [],
                    borderColor: 'rgba(23, 162, 184, 1)',
                    backgroundColor: 'rgba(23, 162, 184, 0.2)',
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const monthlyChartCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyChartCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'ยอดรวมแต่ละเดือน',
                    data: [],
                    backgroundColor: 'rgba(23, 162, 184, 0.7)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // เรียกข้อมูลและอัปเดตกราฟเมื่อโหลดหน้า
        updateChart('daily', '<?php echo $selectedMonth; ?>');
        updateChart('monthly', null, '<?php echo $selectedYear; ?>');

        // function printPage() {
        //     window.print();
        // }

        function downloadDailyCSV() {
            if (dailyChart.data.labels.length === 0) {
                alert('ไม่มีข้อมูลสำหรับดาวน์โหลด');
                return;
            }
            // ดึงข้อมูลจากกราฟรายวัน
            var labels = dailyChart.data.labels;
            var data = dailyChart.data.datasets[0].data;

            // สร้างข้อมูล CSV พร้อม BOM สำหรับการเข้ารหัส UTF-8
            let csvContent = "\uFEFFวัน,ยอดรวมรายวัน\n"; // \uFEFF คือ BOM สำหรับ UTF-8
            labels.forEach((label, index) => {
                csvContent += label + "," + data[index] + "\uFEFFบาท\n";
            });

            // สร้างลิงก์สำหรับดาวน์โหลด
            const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "daily_data.csv");

            // คลิกเพื่อดาวน์โหลด
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadMonthlyCSV() {
            if (monthlyChart.data.labels.length === 0) {
                alert('ไม่มีข้อมูลสำหรับดาวน์โหลด');
                return;
            }
            // ดึงข้อมูลจากกราฟรายเดือน
            var labels = monthlyChart.data.labels;
            var data = monthlyChart.data.datasets[0].data;

            // สร้างข้อมูล CSV พร้อม BOM สำหรับการเข้ารหัส UTF-8
            let csvContent = "\uFEFFเดือน,ยอดรวมรายเดือน\n"; // \uFEFF คือ BOM สำหรับ UTF-8
            labels.forEach((label, index) => {
                csvContent += label + "," + data[index] + "\uFEFFบาท\n";
            });

            // สร้างลิงก์สำหรับดาวน์โหลด
            const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "monthly_data.csv");

            // คลิกเพื่อดาวน์โหลด
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>

</html>