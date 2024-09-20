<?php
session_start();

if (!isset($_SESSION["pharmacist"])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php';

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$userQuery = "SELECT COUNT(user_id) AS user_count FROM users";
$userResult = mysqli_query($conn, $userQuery);
$userCount = mysqli_fetch_assoc($userResult)['user_count'];

// คำนวณรายเดือน
$revenueQuery = "SELECT SUM(total_price) AS monthly_total 
                 FROM orders 
                 WHERE DATE_FORMAT(order_time, '%Y-%m') = '$selectedMonth'";
$revenueResult = $conn->query($revenueQuery);
$monthlyTotal = $revenueResult ? $revenueResult->fetch_assoc()['monthly_total'] : 0;

// คำนวณรายปี
$yearsQuery = "SELECT SUM(total_price) as year_total
               FROM orders
               WHERE DATE_FORMAT(order_time, '%Y') = '$selectedYear'";
$yearsResult = $conn->query($yearsQuery);
$yearstotal = $yearsResult ? $yearsResult->fetch_assoc()['year_total'] : 0;

$monthlyQuery = "SELECT DATE_FORMAT(order_time, '%Y-%m') AS order_month, SUM(total_price) AS monthly_total 
                 FROM orders 
                 WHERE DATE_FORMAT(order_time, '%Y') = '$selectedYear' 
                 GROUP BY order_month 
                 ORDER BY order_month ASC";
$monthlyResult = $conn->query($monthlyQuery);

if (!$monthlyResult) {
    die("Query failed: " . $conn->error);
}

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
    <style>
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
        input[type="month"] {
            width: 180px;
        }
        input[type="number"] {
            width: 80px;
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
        <a href="medicine.php" class="btn btn-secondary me-2">ยา</a>
        <a href="buy.php" class="btn btn-secondary me-2">ร้านค้า</a>
        <a href="users.php" class="btn btn-secondary me-2">ผู้ใช้งาน</a>
        <a href="pharmacist.php" class="btn btn-secondary me-2">ข้อมูลส่วนตัว</a>
        <a href="online.php" class="btn btn-secondary me-2">แชท</a>
        <a href="status.php" class="btn btn-secondary me-2">สถานะ</a>
    </aside>

    <main>
        <h2>จำนวนผู้ใช้งานในระบบ</h2>
        <p>ยอดผู้ใช้งาน: <?php echo $userCount; ?> คน</p>

        <div class="chart-container">
            <div class="chart-wrapper">
                <h2>สถิติการสั่งซื้อรายวัน</h2>
                <label for="monthSelector">เลือกเดือนรายวัน:</label>
                <input type="month" id="monthSelector" class="form-control" value="<?php echo $selectedMonth; ?>">
                <p>รายได้รวมของเดือน <?php echo $monthName . ' ' . $year; ?> : <?php echo number_format($monthlyTotal, 2); ?> บาท</p>
                <canvas id="dailyChart"></canvas>
            </div>

            <div class="chart-wrapper">
                <h2>สถิติรายได้รวมแต่ละเดือน</h2>
                <label for="yearSelector">เลือกปี:</label>
                <input type="number" id="yearSelector" class="form-control" value="<?php echo $selectedYear; ?>" min="2000" max="<?php echo date('Y'); ?>">
                <p>รายได้รวมของปี <?php echo $selectedYear; ?> : <?php echo number_format($yearstotal, 2); ?> บาท</p>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="container">
            <h2>สรุปรายได้รวมของแต่ละเดือน</h2>
            <button onclick="printPage()">Print Page</button>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>เดือน</th>
                        <th>ยอดรวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $monthlyResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_month']; ?></td>
                            <td><?php echo number_format($row['monthly_total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.getElementById('monthSelector').addEventListener('change', function(event) {
            var selectedMonth = this.value;
            updateChart('daily', selectedMonth);
        });

        document.getElementById('yearSelector').addEventListener('change', function(event) {
            var selectedYear = this.value;
            updateChart('monthly', null, selectedYear);
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
                        document.querySelector('p').innerText = 'รายได้รวมของเดือน ' + new Date(month).toLocaleString('default', { month: 'long' }) + ': ' + monthlyTotal.toFixed(2) + ' บาท';
                    } else if (type === 'monthly') {
                        console.log('Monthly Data:', data);
                        var months = data.map(item => item.month);
                        var monthlyTotals = data.map(item => item.total);

                        monthlyChart.data.labels = months;
                        monthlyChart.data.datasets[0].data = monthlyTotals;
                        monthlyChart.update();
                    }
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

        function printPage() {
            window.print();
        }
    </script>
</body>
</html>
