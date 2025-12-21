<?php
// user/buy_ticket.php
require_once '../config/database.php';
checkAuth();

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) { 
    header("Location: events.php"); 
    exit(); 
}

$event_id = $_GET['event_id']; 
$user_id = $_SESSION['user_id']; 
$message = '';

// Ambil data event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?"); 
$stmt->execute([$event_id]); 
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) { 
    header("Location: events.php"); 
    exit(); 
}

/**
 * Fungsi untuk menghasilkan kode tiket unik
 * @param PDO $pdo Koneksi PDO
 * @return string Kode tiket unik (16 karakter alfanumerik)
 */
function generateTicketCode($pdo) {
    do {
        // Gabungan 4 set 4 karakter (total 16 karakter)
        $code = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4)
              . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4)
              . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4)
              . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
        
        // Cek apakah kode sudah ada di database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetchColumn() > 0); // Ulangi jika kode sudah ada
    return $code;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = (int)$_POST['quantity'];
    
    // Validasi input
    if ($quantity < 1 || $quantity > 10) { 
        $message = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i> Jumlah tiket harus antara 1 sampai 10.</div>"; 
    } else {
        $total_price = $event['price'] * $quantity;
        
        try {
            $pdo->beginTransaction();
            
            // Loop untuk membuat dan memasukkan setiap tiket
            for ($i = 0; $i < $quantity; $i++) {
                $ticket_code = generateTicketCode($pdo);
                $stmt_insert = $pdo->prepare("INSERT INTO tickets (user_id, event_id, ticket_code, price, purchase_date, status) VALUES (?, ?, ?, ?, NOW(), 'PENDING')");
                $stmt_insert->execute([$user_id, $event_id, $ticket_code, $event['price']]);
            }
            
            $pdo->commit();
            
            // --- REDIRECT KE HALAMAN CHECKOUT UNTUK PEMBAYARAN ---
            $_SESSION['message'] = "<div class='alert alert-success'>Berhasil memesan {$quantity} tiket! Total pembayaran: Rp " . number_format($total_price, 0, ',', '.') . ". Silakan lanjutkan ke proses pembayaran.</div>";
            header("Location: checkout.php?event_id=" . $event_id); 
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'><i class='bi bi-x-octagon-fill me-2'></i> Gagal memproses pembelian: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beli Tiket: <?= htmlspecialchars($event['title']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body{
        background:linear-gradient(135deg,#f5faff,#eef6ff);
        font-family:"Segoe UI",sans-serif;
    }

    /* BACK LINK (SAMA SEPERTI HALAMAN SEBELUMNYA) */
    .back-link{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:8px 16px;
        border-radius:999px;
        background:#ffffffcc;
        backdrop-filter: blur(8px);
        color:#0369a1;
        font-weight:600;
        text-decoration:none;
        box-shadow:0 6px 18px rgba(0,0,0,.12);
        transition:.25s;
    }

    .back-link:hover{
        transform:translateX(-3px);
        box-shadow:0 10px 24px rgba(0,0,0,.18);
        color:#075985;
    }

    /* MAIN CARD */
    .ticket-shell{
        background:#fff;
        border-radius:22px;
        box-shadow:0 18px 40px rgba(0,0,0,.12);
        padding:20px 24px;
    }

    /* HEADER */
    .header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:1px solid #e5e7eb;
        padding-bottom:12px;
        margin-bottom:16px;
    }

    .event-title{
        font-weight:800;
        color:#1d4ed8;
        font-size:1.3rem;
    }

    .event-meta{
        color:#64748b;
        font-size:.9rem;
    }

    .price-badge{
        background:linear-gradient(135deg,#ecfeff,#e0f2fe);
        color:#0369a1;
        font-weight:800;
        padding:10px 16px;
        border-radius:999px;
        white-space:nowrap;
    }

    /* FORM AREA */
    .form-label{
        font-weight:700;
        margin-bottom:4px;
    }

    .form-control-lg{
        padding:.5rem .75rem;
        font-size:1rem;
    }

    /* TOTAL */
    .total-box{
        background:#f8fafc;
        border-radius:16px;
        padding:14px;
        text-align:center;
        border:1px dashed #c7d2fe;
    }

    .total-box small{
        font-size:.7rem;
        font-weight:800;
        letter-spacing:1px;
        color:#64748b;
    }

    /* BUTTON */
    .btn-order{
        background:linear-gradient(135deg,#2563eb,#38bdf8);
        border:none;
        border-radius:16px;
        font-weight:800;
        padding:14px;
        transition:.25s;
    }

    .btn-order:hover{
        transform:translateY(-2px);
        box-shadow:0 12px 28px rgba(37,99,235,.4);
    }
</style>
</head>

<body>

<div class="container mt-4">
<div class="row justify-content-center">

    <div class="col-lg-11 col-xl-10">

        <!-- 🔽 TOMBOL KEMBALI ELEGAN (SAMA DENGAN SEBELUMNYA) -->
        <div class="mb-3">
            <a href="events.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <?= $message ?>

        <div class="ticket-shell">

            <div class="header">
                <div>
                    <div class="event-title">
                        <?= htmlspecialchars($event['title']) ?>
                    </div>
                    <div class="event-meta">
                        <?= date('d F Y', strtotime($event['date'])) ?> • <?= htmlspecialchars($event['location']) ?>
                    </div>
                </div>

                <div class="price-badge">
                    Rp <?= number_format($event['price'], 0, ',', '.') ?>
                </div>
            </div>

            <form action="buy_ticket.php?event_id=<?= $event['id'] ?>" method="POST">

                <div class="row align-items-end g-3">

                    <div class="col-md-4">
                        <label for="quantity" class="form-label">
                            Jumlah Tiket
                        </label>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               class="form-control form-control-lg rounded-3"
                               value="1"
                               min="1"
                               max="10"
                               required>
                        <small class="text-muted">Maksimal 10 tiket</small>
                    </div>

                    <div class="col-md-4">
                        <div class="total-box">
                            <small>TOTAL PEMBAYARAN</small>
                            <div id="total_price_display" class="fs-4 fw-bolder text-danger">
                                Rp <?= number_format($event['price'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-order w-100 text-white">
                            <i class="bi bi-cart-check-fill me-2"></i>
                            Pesan & Bayar
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const pricePerTicket = <?= $event['price'] ?>;
    const quantityInput = document.getElementById('quantity');
    const totalDisplay = document.getElementById('total_price_display');

    function updateTotalPrice(){
        let quantity = parseInt(quantityInput.value) || 0;
        if(quantity < 1) quantity = 1;
        if(quantity > 10) quantity = 10;
        quantityInput.value = quantity;

        const total = quantity * pricePerTicket;
        totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    quantityInput.addEventListener('input', updateTotalPrice);
    updateTotalPrice();
</script>

</body>
</html>

