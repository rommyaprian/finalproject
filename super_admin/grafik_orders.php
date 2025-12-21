<?php
// super_admin/manage_events.php
require_once '../config/database.php';
checkAuth('super_admin'); // Memastikan akses hanya untuk super_admin

// Ambil Filter Tahun (Default tahun sekarang)
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// 1. Ambil Data Ringkasan Statistik
$summary = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN date >= CURDATE() THEN 1 ELSE 0 END) as upcoming,
    SUM(CASE WHEN date < CURDATE() THEN 1 ELSE 0 END) as past
    FROM events")->fetch();

// 2. Ambil Data untuk Grafik (Event per Bulan)
$stmt = $pdo->prepare("SELECT MONTH(date) as bulan_num, COUNT(*) as jumlah 
                       FROM events WHERE YEAR(date) = ? 
                       GROUP BY MONTH(date) ORDER BY MONTH(date)");
$stmt->execute([$selectedYear]);
$chartRaw = $stmt->fetchAll();

$counts = array_fill(0, 12, 0);
foreach ($chartRaw as $row) { $counts[$row['bulan_num'] - 1] = $row['jumlah']; }

// 3. Ambil Data Tabel Utama
$events = $pdo->query("SELECT * FROM events ORDER BY date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Laporan Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body{
            background:#f4f6fb;
            font-family:"Segoe UI", sans-serif;
        }

        /* ===== PAGE HEADER ===== */
        .page-title{
            font-weight:800;
            color:#1e3a8a;
        }

        /* ===== BUTTON ===== */
        .btn-rounded{
            border-radius:999px;
            font-weight:600;
            padding:8px 18px;
        }

        /* ===== STAT CARD ===== */
        .stat-card{
            border:none;
            border-radius:18px;
            padding:22px;
            box-shadow:0 15px 35px rgba(0,0,0,.12);
            transition:.3s;
            height:100%;
        }

        .stat-card:hover{
            transform:translateY(-5px);
        }

        .stat-primary{
            background:linear-gradient(135deg,#2563eb,#1d4ed8);
            color:#fff;
        }

        .stat-secondary{
            background:linear-gradient(135deg,#64748b,#475569);
            color:#fff;
        }

        /* ===== CARD ===== */
        .card-custom{
            border:none;
            border-radius:20px;
            box-shadow:0 20px 50px rgba(0,0,0,.1);
        }

        /* ===== TABLE ===== */
        .table thead{
            background:#1e3a8a;
            color:#fff;
        }

        .table td{
            vertical-align:middle;
        }

        /* ===== PRINT ===== */
        @media print{
            .d-print-none{
                display:none !important;
            }
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <!-- ===== HEADER ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3 class="page-title">Analisis & Data Event</h3>
        <div class="d-flex gap-2">
            <a href="export_excel.php" class="btn btn-outline-success btn-rounded">
                Export Excel
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-rounded">
                Cetak PDF
            </button>
            <a href="../admin/event_create.php" class="btn btn-primary btn-rounded">
                Tambah Event
            </a>
        </div>
    </div>

    <!-- ===== STAT ===== -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="stat-card bg-white">
                <small class="text-muted fw-bold">TOTAL EVENT</small>
                <h2 class="fw-bold"><?= $summary['total'] ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card stat-primary">
                <small class="fw-bold">EVENT MENDATANG</small>
                <h2 class="fw-bold"><?= $summary['upcoming'] ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card stat-secondary">
                <small class="fw-bold">EVENT SELESAI</small>
                <h2 class="fw-bold"><?= $summary['past'] ?></h2>
            </div>
        </div>
    </div>

    <!-- ===== CHART ===== -->
    <div class="card card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Tren Event Per Bulan</h5>
            <form method="GET" class="d-print-none">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php for($y=date('Y'); $y>=2023; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                            <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <canvas id="eventChart" style="max-height:260px;"></canvas>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="card card-custom p-4">
        <h5 class="fw-bold mb-3">Rincian Data Event</h5>

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Harga</th>
                    <th class="d-print-none">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $e): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($e['title']) ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($e['date'])) ?><br>
                        <small class="text-muted"><?= $e['time'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($e['location']) ?></td>
                    <td>Rp <?= number_format($e['price'], 0, ',', '.') ?></td>
                    <td class="d-print-none">
                        <a href="../admin/event_edit.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning rounded-pill">
                            Edit
                        </a>
                        <a href="../admin/event_delete.php?id=<?= $e['id'] ?>"
                           class="btn btn-sm btn-danger rounded-pill"
                           onclick="return confirm('Hapus event ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
const ctx = document.getElementById('eventChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: 'Jumlah Event <?= $selectedYear ?>',
            data: <?= json_encode($counts) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.15)',
            fill: true,
            tension: 0.35
        }]
    }
});
</script>

</body>
</html>
