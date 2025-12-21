<?php
// user/events.php
require_once '../config/database.php';
checkAuth();

// Ambil hanya event yang tanggalnya belum lewat
$stmt = $pdo->query("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event Mendatang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body{
            background:#f4f6fb;
            font-family:"Segoe UI", sans-serif;
        }

        /* NAVBAR */
        .navbar{
            background:linear-gradient(90deg,#1e3a8a,#2563eb);
        }

        /* BACK BUTTON */
        .back-link{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:8px 16px;
            border-radius:999px;
            background:#ffffff;
            color:#1e3a8a;
            font-weight:600;
            text-decoration:none;
            box-shadow:0 6px 16px rgba(0,0,0,.08);
            transition:.2s;
        }
        .back-link:hover{
            transform:translateX(-3px);
        }

        /* TITLE */
        .page-title{
            font-weight:800;
            color:#1e3a8a;
        }

        /* EVENT CARD */
        .event-card{
            border:none;
            border-radius:20px;
            background:#ffffff;
            box-shadow:0 18px 40px rgba(0,0,0,.12);
            transition:.25s;
            height:100%;
        }

        .event-card:hover{
            transform:translateY(-6px);
            box-shadow:0 26px 55px rgba(0,0,0,.18);
        }

        .event-card h4{
            font-weight:700;
            color:#1e3a8a;
        }

        .event-info i{
            color:#2563eb;
        }

        /* PRICE */
        .price-badge{
            background:#eef2ff;
            color:#1e40af;
            border-radius:14px;
            padding:8px 14px;
            font-weight:800;
            font-size:1rem;
            display:inline-block;
        }

        /* BUTTON */
        .btn-buy{
            background:linear-gradient(135deg,#2563eb,#1d4ed8);
            border:none;
            border-radius:14px;
            font-weight:700;
            transition:.25s;
        }

        .btn-buy:hover{
            transform:translateY(-1px);
            box-shadow:0 10px 24px rgba(37,99,235,.35);
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-music-note-beamed me-2"></i>Event Konser
        </a>
        <span class="navbar-text text-white fw-semibold">
            Halo, <?= htmlspecialchars($_SESSION['username']) ?>
        </span>
    </div>
</nav>

<div class="container mt-4">

    <h2 class="mb-5 text-center page-title">
        Daftar Event Mendatang
    </h2>

    <?php if (empty($events)): ?>
        <div class="alert alert-warning text-center rounded-4 shadow-sm">
            Belum ada event konser yang tersedia saat ini.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($events as $event): ?>
            <div class="col">
                <div class="card event-card">
                    <div class="card-body d-flex flex-column">

                        <h4 class="mb-3">
                            <?= htmlspecialchars($event['title']) ?>
                        </h4>

                        <div class="event-info mb-3 text-muted">
                            <p class="mb-1">
                                <i class="bi bi-calendar-event me-2"></i>
                                <?= date('d F Y', strtotime($event['date'])) ?>
                            </p>
                            <p class="mb-1">
                                <i class="bi bi-clock me-2"></i>
                                <?= htmlspecialchars($event['time']) ?>
                            </p>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt me-2"></i>
                                <?= htmlspecialchars($event['location']) ?>
                            </p>
                        </div>

                        <div class="mt-auto">
                            <div class="price-badge mb-3">
                                Rp <?= number_format($event['price'], 0, ',', '.') ?>
                            </div>

                            <a href="buy_ticket.php?event_id=<?= $event['id'] ?>"
                               class="btn btn-buy w-100 text-white">
                                <i class="bi bi-ticket-perforated-fill me-2"></i>
                                Beli Tiket
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
