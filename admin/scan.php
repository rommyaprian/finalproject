<?php
// admin/scan.php
require_once '../config/database.php';
checkAuth('admin');

$message = '';
$ticket_info = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_code = trim($_POST['ticket_code']);
    
    // 1. Ambil data tiket dan event terkait
    $stmt = $pdo->prepare("
        SELECT t.*, e.title AS event_title, e.date AS event_date, u.username 
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        JOIN users u ON t.user_id = u.id
        WHERE t.ticket_code = ?
    ");
    $stmt->execute([$ticket_code]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {
        $ticket_info = $ticket; // Simpan info untuk ditampilkan

        if ($ticket['status'] === 'PAID') {
            // 2. Jika status PAID, tandai sebagai USED (Terpakai)
            $stmt_update = $pdo->prepare("UPDATE tickets SET status = 'USED', used_at = NOW() WHERE id = ?");
            $stmt_update->execute([$ticket['id']]);
            
            $message = "<div class='alert alert-success mt-3'>
                            <h4 class='alert-heading'>TIKET BERHASIL DIVERIFIKASI!</h4>
                            <p>Tiket ini sekarang SAH dan telah ditandai sebagai TERPAKAI.</p>
                            <hr><p class='mb-0'>Kode Tiket: <strong>" . htmlspecialchars($ticket_code) . "</strong></p>
                        </div>";
        
        } else if ($ticket['status'] === 'USED') {
            $message = "<div class='alert alert-warning mt-3'>
                            <h4 class='alert-heading'>GAGAL VERIFIKASI: TIKET SUDAH TERPAKAI!</h4>
                            <p>Tiket ini telah digunakan pada: " . htmlspecialchars($ticket['used_at']) . "</p>
                        </div>";

        } else {
            // CANCELED, PENDING, atau status lain yang tidak diizinkan masuk
            $message = "<div class='alert alert-danger mt-3'>
                            <h4 class='alert-heading'>GAGAL VERIFIKASI: TIKET TIDAK VALID!</h4>
                            <p>Status Tiket: " . htmlspecialchars($ticket['status']) . " (Tidak dapat digunakan).</p>
                        </div>";
        }
    } else {
        $message = "<div class='alert alert-danger mt-3'>
                        <h4 class='alert-heading'>KODE TIKET TIDAK DITEMUKAN!</h4>
                        <p>Pastikan kode yang dimasukkan sudah benar.</p>
                    </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tiket</title>

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

        /* ===== INPUT ===== */
        .input-lg{
            border-radius:14px;
            padding:14px 16px;
            font-size:18px;
        }

        /* ===== BUTTON ===== */
        .btn-primary{
            border-radius:14px;
            font-weight:700;
            padding:12px 24px;
            background:linear-gradient(135deg,#2563eb,#1d4ed8);
            border:none;
            box-shadow:0 12px 28px rgba(37,99,235,.45);
        }

        /* ===== BADGE ===== */
        .badge-lg{
            font-size:14px;
            padding:6px 12px;
            border-radius:999px;
        }

        /* ===== BACK ===== */
        .btn-secondary{
            border-radius:999px;
            font-weight:600;
            padding:8px 18px;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <!-- TITLE -->
    <h3 class="page-title mb-4 text-center">
        Verifikasi Tiket Masuk
    </h3>

    <!-- SCAN FORM -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold text-primary mb-2">Masukkan Kode Tiket</h5>
        <p class="text-muted mb-4">
            Gunakan pemindai atau input manual kode tiket (16 karakter).
        </p>

        <form action="scan.php" method="POST">
            <div class="input-group">
                <input type="text"
                       name="ticket_code"
                       class="form-control input-lg"
                       placeholder="Kode Tiket (contoh: ABC123DEF456GHI7)"
                       required
                       autofocus>
                <button class="btn btn-primary" type="submit">
                    Verifikasi
                </button>
            </div>
        </form>
    </div>

    <!-- MESSAGE -->
    <?= $message ?>

    <!-- TICKET DETAIL -->
    <?php if ($ticket_info): ?>
    <div class="card card-custom mt-4
        border-start border-5 border-<?= ($ticket_info['status'] === 'PAID') ? 'success' : 'danger' ?>">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-bold">Detail Tiket</h5>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <strong>Event:</strong>
                <?= htmlspecialchars($ticket_info['event_title']) ?>
            </li>
            <li class="list-group-item">
                <strong>Tanggal:</strong>
                <?= htmlspecialchars($ticket_info['event_date']) ?>
            </li>
            <li class="list-group-item">
                <strong>Pemilik:</strong>
                <?= htmlspecialchars($ticket_info['username']) ?>
            </li>
            <li class="list-group-item">
                <strong>Status:</strong>
                <span class="badge badge-lg bg-<?= ($ticket_info['status'] === 'PAID') ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($ticket_info['status']) ?>
                </span>
            </li>
        </ul>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
