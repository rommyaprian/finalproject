<?php
// user/checkout.php
require_once '../config/database.php';
checkAuth('user');

$user_id = $_SESSION['user_id'];
$message = '';

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    header("Location: events.php");
    exit();
}
$event_id = $_GET['event_id'];

// Ambil data event
$stmt_event = $pdo->prepare("SELECT title, price FROM events WHERE id = ?");
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: events.php");
    exit();
}

// Hitung total harga berdasarkan tiket status PENDING yang baru dibeli untuk event ini
$stmt_tickets = $pdo->prepare("
    SELECT COUNT(*) AS total_qty, SUM(price) AS total_price 
    FROM tickets 
    WHERE user_id = ? AND event_id = ? AND status = 'PENDING'
");
$stmt_tickets->execute([$user_id, $event_id]);
$order_summary = $stmt_tickets->fetch(PDO::FETCH_ASSOC);

$total_qty = $order_summary['total_qty'] ?? 0;
$total_price = $order_summary['total_price'] ?? 0;

if ($total_qty == 0) {
    // Jika tidak ada tiket PENDING untuk event ini, alihkan ke daftar tiket
    $_SESSION['message'] = "<div class='alert alert-info'>Tidak ada pesanan tertunda untuk event ini.</div>";
    header("Location: my_tickets.php");
    exit();
}

// --- LOGIKA SIMULASI PEMBAYARAN DI SINI ---
if (isset($_GET['pay_action']) && $_GET['pay_action'] == 'confirm') {
    
    try {
        // 1. Update status semua tiket PENDING terkait menjadi PAID
        $stmt_pay = $pdo->prepare("
            UPDATE tickets 
            SET status = 'PAID', payment_date = NOW() 
            WHERE user_id = ? AND event_id = ? AND status = 'PENDING'
        ");
        $stmt_pay->execute([$user_id, $event_id]);
        
        // 2. SET NOTIFIKASI SUKSES (REVISI POP UP)
        $_SESSION['message'] = "
            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                <h4 class='alert-heading'><i class='bi bi-check-circle-fill me-2'></i> Pembayaran Sukses!</h4>
                <p>Terima kasih. Pembayaran tiket Event: " . htmlspecialchars($event['title']) . " telah kami terima.</p>
                <hr>
                <p class='mb-0'>Anda berhasil membeli {$total_qty} tiket. Status tiket Anda sekarang adalah PAID dan siap diakses di halaman Tiket Saya.</p>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        ";
        
        // 3. Redirect ke halaman my_tickets.php
        header("Location: my_tickets.php");
        exit();
        
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'><i class='bi bi-x-octagon-fill me-2'></i> Gagal memproses pembayaran: " . $e->getMessage() . "</div>";
    }
}

// --- LOGIKA FLASH MESSAGE (JIKA ADA PESAN DARI HALAMAN LAIN) ---
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout & Pembayaran</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
body{
    background:#f4f6fb;
    font-family:"Segoe UI",sans-serif;
}

/* TITLE */
.page-title{
    font-weight:800;
    color:#1e3a8a;
}

/* CARD */
.card-ui{
    border:none;
    border-radius:22px;
    background:#ffffff;
    box-shadow:0 20px 50px rgba(0,0,0,.12);
}

/* TOTAL */
.total-box{
    background:#f8fafc;
    border-radius:16px;
    padding:18px;
    text-align:end;
    border:1px dashed #c7d2fe;
}
.total-box span.label{
    display:block;
    font-size:.75rem;
    font-weight:800;
    letter-spacing:1px;
    color:#64748b;
}

/* PAYMENT */
.payment-item{
    transition:.25s;
}
.payment-item:hover{
    background:#f8fafc;
}

/* BUTTON */
.btn-confirm{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    border:none;
    font-weight:700;
    padding:12px;
}
.btn-confirm:hover{
    opacity:.95;
}

.btn-back{
    border-radius:999px;
    font-weight:600;
    padding:8px 18px;
}
</style>
</head>

<body>

<div class="container mt-4">
<div class="row justify-content-center">
<div class="col-lg-10 col-xl-9">

    <h3 class="mb-4 text-center page-title">
        <i class="bi bi-wallet2 me-2"></i> Konfirmasi Pembayaran
    </h3>

    <?= $message ?>

    <!-- RINGKASAN -->
    <div class="card card-ui mb-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h5 class="mb-0">
                <i class="bi bi-receipt me-2"></i> Ringkasan Pesanan
            </h5>
        </div>
        <div class="card-body">

            <div class="row align-items-center">
                <div class="col-md-7">
                    <p class="fs-5 mb-1 fw-bold">
                        <?= htmlspecialchars($event['title']) ?>
                    </p>
                    <p class="mb-1 text-muted">
                        Harga Tiket: Rp <?= number_format($event['price'], 0, ',', '.') ?>
                    </p>
                    <p class="mb-0">
                        Jumlah Tiket:
                        <strong><?= $total_qty ?></strong>
                    </p>
                </div>

                <div class="col-md-5">
                    <div class="total-box">
                        <span class="label">TOTAL PEMBAYARAN</span>
                        <span class="fs-3 fw-bolder text-danger">
                            Rp <?= number_format($total_price, 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- METODE PEMBAYARAN -->
    <div class="card card-ui">
        <div class="card-header bg-info text-white rounded-top-4">
            <h5 class="mb-0">
                <i class="bi bi-credit-card me-2"></i> Metode Pembayaran (Simulasi)
            </h5>
        </div>

        <ul class="list-group list-group-flush">

            <!-- BANK VA -->
            <li class="list-group-item payment-item d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-bank me-2 text-primary"></i>
                    <strong>Transfer Bank (Virtual Account)</strong>
                    <small class="d-block text-muted">
                        BCA • Mandiri • BRI • BNI
                    </small>
                </div>
                <button type="button"
                        class="btn btn-sm btn-outline-primary rounded-pill"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseVA">
                    Pilih
                </button>
            </li>

            <li class="list-group-item collapse" id="collapseVA">
                <p class="fw-bold mb-2">
                    Nomor Virtual Account:
                    <span class="text-primary">8077712345678</span>
                </p>
                <a href="checkout.php?event_id=<?= $event_id ?>&pay_action=confirm"
                   onclick="return confirm('Apakah Anda yakin sudah melakukan transfer? Pesanan akan diubah menjadi PAID.')"
                   class="btn btn-confirm w-100 text-white">
                    Konfirmasi Pembayaran
                </a>
            </li>

            <!-- SLOT KOSONG (DIBIARKAN SESUAI KODE ASLI) -->
            <li class="list-group-item"></li>
            <li class="list-group-item collapse" id="collapseEWallet">
                <a href="checkout.php?event_id=<?= $event_id ?>&pay_action=confirm"
                   onclick="return confirm('Apakah Anda yakin sudah melakukan pembayaran? Pesanan akan diubah menjadi PAID.')"
                   class="btn btn-confirm w-100 text-white">
                    Konfirmasi Pembayaran
                </a>
            </li>

        </ul>
    </div>

    <!-- BACK -->
    <div class="text-center">
        <a href="buy_ticket.php" class="btn btn-secondary btn-back mt-4">
            Batalkan & Kembali
        </a>
    </div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
