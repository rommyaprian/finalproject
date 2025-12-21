<?php
// admin/event_edit.php
require_once '../config/database.php';
checkAuth('admin');

$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$event_id = $_GET['id'];

// 1. Ambil data event saat ini
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    $message = "<div class='alert alert-danger'>Event tidak ditemukan.</div>";
    // Jika event tidak ditemukan, tidak perlu melanjutkan form
    $event = ['title' => '', 'date' => '', 'time' => '', 'location' => '', 'price' => 0];
}

// 2. Logika Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && $event) {
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $location = trim($_POST['location']);
    $price = (int)$_POST['price'];

    try {
        $stmt_update = $pdo->prepare("UPDATE events SET title = ?, date = ?, time = ?, location = ?, price = ? WHERE id = ?");
        $stmt_update->execute([$title, $date, $time, $location, $price, $event_id]);
        
        // Refresh data event setelah update berhasil
        $event['title'] = $title;
        $event['date'] = $date;
        $event['time'] = $time;
        $event['location'] = $location;
        $event['price'] = $price;

        $message = "<div class='alert alert-success'>Event ".htmlspecialchars($title)." berhasil diperbarui!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Gagal memperbarui event: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>

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
            color:#d97706; /* warning tone */
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
        .btn-warning{
            border-radius:999px;
            font-weight:600;
            padding:10px;
            box-shadow:0 10px 24px rgba(217,119,6,.35);
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
                Edit Event: <?= htmlspecialchars($event['title']) ?>
            </h3>

            <!-- MESSAGE -->
            <?= $message ?>

            <?php if ($event): ?>
            <!-- FORM CARD -->
            <div class="card card-custom p-4">
                <form action="event_edit.php?id=<?= $event_id ?>" method="POST">

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Event</label>
                        <input type="text"
                               name="title"
                               id="title"
                               class="form-control"
                               value="<?= htmlspecialchars($event['title']) ?>"
                               required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date"
                                   name="date"
                                   id="date"
                                   class="form-control"
                                   value="<?= htmlspecialchars($event['date']) ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="time" class="form-label">Waktu</label>
                            <input type="time"
                                   name="time"
                                   id="time"
                                   class="form-control"
                                   value="<?= htmlspecialchars($event['time']) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text"
                               name="location"
                               id="location"
                               class="form-control"
                               value="<?= htmlspecialchars($event['location']) ?>"
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="price" class="form-label">Harga Tiket (Rp)</label>
                        <input type="number"
                               name="price"
                               id="price"
                               class="form-control"
                               value="<?= htmlspecialchars($event['price']) ?>"
                               required
                               min="1000">
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        Perbarui Event
                    </button>

                </form>
            </div>
            <?php endif; ?>

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
