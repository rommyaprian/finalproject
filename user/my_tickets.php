<?php
// user/my_tickets.php
session_start();
require_once '../config/database.php';

// Pastikan fungsi checkAuth() sudah tersedia di database.php atau helper Anda
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// --- LOGIKA PEMBAYARAN SIMULASI ---
if (isset($_GET['action']) && $_GET['action'] == 'pay' && isset($_GET['ticket_id'])) {
    $ticket_id = $_GET['ticket_id'];
    
    $stmt_check = $pdo->prepare("SELECT status FROM tickets WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$ticket_id, $user_id]);
    $ticket_status = $stmt_check->fetchColumn();

    if ($ticket_status === 'PENDING') {
        try {
            $stmt_update = $pdo->prepare("UPDATE tickets SET status = 'PAID' WHERE id = ?");
            $stmt_update->execute([$ticket_id]);
            
            $_SESSION['message'] = "<div class='alert alert-success alert-dismissible fade show'>
                <i class='bi bi-check-circle-fill me-2'></i>Pembayaran berhasil! Tiket Anda kini lunas (**PAID**).
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
            header("Location: my_tickets.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Gagal memproses pembayaran: " . $e->getMessage() . "</div>";
            header("Location: my_tickets.php");
            exit();
        }
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// --- AMBIL SEMUA TIKET PENGGUNA ---
// Menambahkan e.price ke dalam SELECT agar tidak error saat dipanggil
$stmt = $pdo->prepare("
    SELECT t.*, e.title AS event_title, e.date AS event_date, e.time AS event_time, e.location, e.price 
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id = ?
    ORDER BY t.purchase_date DESC
");
$stmt->execute([$user_id]);
$my_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tiket Saya</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
body{
    background:#f4f6fb;
    font-family:"Segoe UI",sans-serif;
}

/* BACK */
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

/* CARD */
.ticket-card{
    border:none;
    border-radius:22px;
    background:#ffffff;
    box-shadow:0 18px 40px rgba(0,0,0,.12);
    transition:.25s;
    overflow:hidden;
}
.ticket-card:hover{
    transform:translateY(-4px);
    box-shadow:0 26px 55px rgba(0,0,0,.18);
}

/* STATUS BORDER */
.status-paid{ border-left:6px solid #16a34a; }
.status-pending{ border-left:6px solid #f59e0b; }
.status-used{ border-left:6px solid #2563eb; }
.status-canceled{ border-left:6px solid #dc2626; }

/* HEADER */
.ticket-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:10px;
}

.ticket-title{
    font-weight:700;
    color:#0f172a;
}

.ticket-meta{
    font-size:.8rem;
    color:#64748b;
}

/* INFO */
.info-box{
    background:#f8fafc;
    border-radius:14px;
    padding:14px;
    font-size:.85rem;
}

/* CODE */
.code-box{
    background:#f0fdf4;
    border-radius:18px;
    padding:18px;
    text-align:center;
    border:1px dashed #86efac;
}

.code-box h4{
    letter-spacing:2px;
}

/* BUTTON */
.btn-pay{
    background:linear-gradient(135deg,#facc15,#f59e0b);
    border:none;
    font-weight:700;
}
.btn-pay:hover{
    opacity:.95;
}
</style>
</head>

<body>

<div class="container mt-4 mb-5">

    <h2 class="mb-4 text-center page-title">
        <i class="bi bi-ticket-perforated me-2"></i> Tiket Saya
    </h2>

    <div class="row justify-content-center">
    <div class="col-lg-10">

        <?= $message ?>

        <div class="row g-4">

        <?php if (empty($my_tickets)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5 rounded-4 shadow-sm">
                    <i class="bi bi-ticket-detailed fs-1 d-block mb-2"></i>
                    Anda belum memiliki riwayat pembelian tiket.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($my_tickets as $ticket):
                $status_class = strtolower('status-' . $ticket['status']);
                $badge_color = match($ticket['status']) {
                    'PAID' => 'bg-success',
                    'PENDING' => 'bg-warning text-dark',
                    'USED' => 'bg-primary',
                    default => 'bg-danger'
                };
            ?>
            <div class="col-md-6">
                <div class="ticket-card h-100 <?= $status_class ?>">
                    <div class="card-body">

                        <div class="ticket-header">
                            <div>
                                <div class="ticket-title">
                                    <?= htmlspecialchars($ticket['event_title']) ?>
                                </div>
                                <div class="ticket-meta">
                                    <i class="bi bi-clock me-1"></i>
                                    Dibeli <?= date('d M Y H:i', strtotime($ticket['purchase_date'])) ?>
                                </div>
                            </div>
                            <span class="badge <?= $badge_color ?> px-3 py-2 rounded-pill">
                                <?= $ticket['status'] ?>
                            </span>
                        </div>

                        <div class="info-box my-3 text-muted">
                            <div>
                                <i class="bi bi-geo-alt me-1"></i>
                                <?= htmlspecialchars($ticket['location']) ?>
                            </div>
                            <div>
                                <i class="bi bi-calendar-event me-1"></i>
                                <?= date('d F Y', strtotime($ticket['event_date'])) ?>
                                • <?= $ticket['event_time'] ?>
                            </div>
                        </div>

                        <?php if ($ticket['status'] == 'PENDING'): ?>
                            <div class="alert alert-warning border-0 small mb-0 rounded-3">
                                <p class="mb-1 fw-bold">
                                    Total Tagihan:
                                    Rp <?= number_format($ticket['price'], 0, ',', '.') ?>
                                </p>
                                <p class="mb-2">
                                    Transfer ke Bank XYZ (12345678)
                                </p>
                                <a href="my_tickets.php?action=pay&ticket_id=<?= $ticket['id'] ?>"
                                   onclick="return confirm('Simulasi: Konfirmasi pembayaran sekarang?')"
                                   class="btn btn-pay btn-sm w-100 text-dark">
                                    <i class="bi bi-credit-card me-1"></i>
                                    Konfirmasi Pembayaran
                                </a>
                            </div>

                        <?php elseif ($ticket['status'] == 'PAID'): ?>
                            <div class="code-box mt-3">
                                <small class="fw-bold text-success d-block mb-1">
                                    KODE TIKET
                                </small>
                                <h4 class="fw-bold text-success mb-3">
                                    <?= htmlspecialchars($ticket['ticket_code']) ?>
                                </h4>
                                <a href="download_ticket.php?id=<?= $ticket['id'] ?>"
                                   class="btn btn-outline-success w-100">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                    Download E-Ticket
                                </a>
                            </div>

                        <?php elseif ($ticket['status'] == 'USED'): ?>
                            <div class="alert alert-primary text-center mb-0 rounded-3">
                                <i class="bi bi-check2-all me-1"></i>
                                Tiket sudah digunakan
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        </div>

    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
