<?php
// admin/events.php
session_start(); // Pastikan session dimulai sebelum require database.php
require_once '../config/database.php';
checkAuth('admin');

// --- LOGIKA FLASH MESSAGE ---
$flash_message = '';
if (isset($_SESSION['message'])) {
    $flash_message = $_SESSION['message'];
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}
// ----------------------------

$stmt = $pdo->query("SELECT * FROM events ORDER BY date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Event</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#f4f6fb;
            font-family:"Segoe UI", sans-serif;
        }

        /* ===== PAGE TITLE ===== */
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

        .table tbody tr:hover{
            background:#eef2ff;
        }
    </style>
</head>

<body>
    
<div class="container mt-5">

    <!-- TITLE -->
    <h3 class="page-title mb-4">Kelola Event</h3>
    
    <!-- FLASH MESSAGE -->
    <?= $flash_message ?>

    <!-- ACTION -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="event_create.php" class="btn btn-success btn-rounded">
            Tambah Event Baru
        </a>
        
    </div>
    
    <!-- TABLE -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Harga</th>
                        <th style="width:160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada event yang terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= htmlspecialchars($event['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($event['date']) ?></td>
                            <td><?= htmlspecialchars($event['location']) ?></td>
                            <td>Rp <?= number_format($event['price'], 0, ',', '.') ?></td>
                            <td>
                                <a href="event_edit.php?id=<?= $event['id'] ?>"
                                   class="btn btn-sm btn-warning rounded-pill me-1 mb-1 mb-md-0">
                                    Edit
                                </a>
                                <a href="event_delete.php?id=<?= $event['id'] ?>"
                                   onclick="return confirm('Yakin hapus event: <?= htmlspecialchars($event['title']) ?>?')"
                                   class="btn btn-sm btn-danger rounded-pill">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
