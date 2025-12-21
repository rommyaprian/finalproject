<?php
// super_admin/manage_events.php
require_once '../config/database.php';
checkAuth('super_admin');

$events = $pdo->query("SELECT * FROM events ORDER BY date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Event (Super Admin)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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

    <!-- ===== HEADER ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">Data Seluruh Event</h3>
        <div class="d-flex gap-2">
            <a href="../admin/event_create.php" class="btn btn-success btn-rounded">
                <i class="bi bi-plus-circle me-1"></i> Tambah Event
            </a>
        </div>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="card card-custom p-4">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Harga</th>
                    <th style="width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $e): ?>
                <tr>
                    <td class="fw-bold">
                        <?= htmlspecialchars($e['title']) ?>
                    </td>
                    <td>
                        <?= date('d/m/Y', strtotime($e['date'])) ?><br>
                        <small class="text-muted"><?= $e['time'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($e['location']) ?></td>
                    <td>Rp <?= number_format($e['price'], 0, ',', '.') ?></td>
                    <td>
                        <a href="../admin/event_edit.php?id=<?= $e['id'] ?>"
                           class="btn btn-sm btn-warning rounded-pill me-1">
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

</body>
</html>
