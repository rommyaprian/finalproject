<?php
// admin/event_create.php
require_once '../config/database.php';
checkAuth('admin');

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $location = trim($_POST['location']);
    $price = (int)$_POST['price'];

    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, date, time, location, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $date, $time, $location, $price]);
        $message = "<div class='alert alert-success'>Event ".htmlspecialchars($title)." berhasil ditambahkan!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Gagal menambahkan event: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Event Baru</title>

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

        /* ===== CARD ===== */
        .card-custom{
            border:none;
            border-radius:20px;
            box-shadow:0 20px 50px rgba(0,0,0,.1);
        }

        /* ===== FORM ===== */
        .form-control{
            border-radius:12px;
            padding:10px 14px;
        }

        .form-label{
            font-weight:600;
            color:#374151;
        }

        /* ===== BUTTON ===== */
        .btn-primary{
            border-radius:999px;
            font-weight:600;
            padding:10px;
            background:linear-gradient(135deg,#2563eb,#1d4ed8);
            border:none;
            box-shadow:0 12px 28px rgba(37,99,235,.5);
        }

        .btn-primary:hover{
            opacity:.95;
        }

        .btn-secondary{
            border-radius:999px;
            font-weight:600;
            padding:8px 18px;
        }
    </style>
</head>

<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- TITLE -->
            <h3 class="page-title mb-4 text-center">
                Tambah Event Baru
            </h3>

            <!-- MESSAGE -->
            <?= $message ?>

            <!-- FORM CARD -->
            <div class="card card-custom p-4">
                <form action="event_create.php" method="POST">

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Event</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time" class="form-label">Waktu</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text" name="location" id="location" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label for="price" class="form-label">Harga Tiket (Rp)</label>
                        <input type="number"
                               name="price"
                               id="price"
                               class="form-control"
                               required
                               min="1000">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Simpan Event
                    </button>

                </form>
            </div>

            <!-- BACK BUTTON -->
            <div class="text-center">
                <a href="events.php" class="btn btn-secondary mt-4">
                    Kembali ke Daftar Event
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
