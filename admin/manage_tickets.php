<?php
// admin/manage_tickets.php
session_start();
require_once '../config/database.php';
checkAuth('admin');

$message = '';
$current_event_id = $_GET['event_id'] ?? '';
$current_status = $_GET['status'] ?? '';

// --- LOGIKA UPDATE STATUS MANUAL ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['new_status'];
    
    // Pastikan status yang di-update valid
    if (in_array($new_status, ['PENDING', 'PAID', 'USED', 'CANCELED'])) {
        try {
            // Jika diubah menjadi USED, tambahkan used_at = NOW(). Jika tidak, reset ke NULL
            // payment_date juga di-reset ke NULL jika status tidak PAID
            $used_at_clause = ($new_status === 'USED') ? ', used_at = NOW()' : ', used_at = NULL';
            $payment_date_clause = ($new_status === 'PAID') ? ', payment_date = NOW()' : ', payment_date = NULL';
            
            $stmt = $pdo->prepare("UPDATE tickets SET status = ? {$used_at_clause} {$payment_date_clause} WHERE id = ?");
            $stmt->execute([$new_status, $ticket_id]);
            
            $_SESSION['message'] = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i> Status tiket ID #{$ticket_id} berhasil diubah menjadi {$new_status}!</div>";
            header("Location: manage_tickets.php");
            exit();

        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'><i class='bi bi-x-octagon me-2'></i> Gagal mengubah status: " . $e->getMessage() . "</div>";
        }
    }
}

// --- LOGIKA FLASH MESSAGE ---
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// --- LOGIKA FILTER DAN QUERY TIKET ---
$where_clauses = [];
$params = [];

if (!empty($current_event_id)) {
    $where_clauses[] = "t.event_id = ?";
    $params[] = $current_event_id;
}

if (!empty($current_status)) {
    $where_clauses[] = "t.status = ?";
    $params[] = $current_status;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "
    -- TAMBAHKAN t.transaction_code DI SINI
    SELECT t.*, t.transaction_code, e.title AS event_title, u.username 
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    JOIN users u ON t.user_id = u.id
    {$where_sql}
    ORDER BY t.purchase_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar event untuk dropdown filter
$events_stmt = $pdo->query("SELECT id, title FROM events ORDER BY title ASC");
$events_list = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

// Daftar status untuk dropdown
$status_list = ['PENDING', 'PAID', 'USED', 'CANCELED'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tiket</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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

        /* ===== FILTER CARD ===== */
        .filter-title{
            font-weight:700;
            color:#1e3a8a;
        }

        /* ===== BUTTON ===== */
        .btn-rounded{
            border-radius:999px;
            font-weight:600;
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

        /* ===== FORM ===== */
        .form-select,
        .form-control{
            border-radius:10px;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <!-- TITLE -->
    <h3 class="page-title mb-4">
        <i class="bi bi-ticket-detailed me-2"></i>
        Manajemen Tiket Event
    </h3>

    <!-- MESSAGE -->
    <?= $message ?>

    <!-- FILTER -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="filter-title mb-3">Filter Tiket</h5>

        <form action="manage_tickets.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="event_id" class="form-label fw-semibold">Event</label>
                <select name="event_id" id="event_id" class="form-select">
                    <option value="">Semua Event</option>
                    <?php foreach ($events_list as $event): ?>
                        <option value="<?= $event['id'] ?>" <?= $current_event_id == $event['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach ($status_list as $status): ?>
                        <option value="<?= $status ?>" <?= $current_status == $status ? 'selected' : '' ?>>
                            <?= ucfirst(strtolower($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-rounded">
                    Filter
                </button>
                <a href="manage_tickets.php" class="btn btn-outline-secondary btn-rounded">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="card card-custom p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Tiket</th>
                        <th>Kode Transaksi (VA)</th>
                        <th>Event</th>
                        <th>Pemilik</th>
                        <th>Tgl Beli</th>
                        <th>Status</th>
                        <th style="width:240px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Tidak ada tiket yang ditemukan berdasarkan filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= $ticket['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($ticket['ticket_code']) ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($ticket['transaction_code'] ?? '-') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($ticket['event_title']) ?></td>
                            <td><?= htmlspecialchars($ticket['username']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($ticket['purchase_date'])) ?></td>
                            <td>
                                <?php 
                                    $status_badge = match ($ticket['status']) {
                                        'PAID' => 'bg-success',
                                        'USED' => 'bg-warning text-dark',
                                        'PENDING' => 'bg-secondary',
                                        default => 'bg-danger',
                                    };
                                ?>
                                <span class="badge <?= $status_badge ?> rounded-pill px-3">
                                    <?= htmlspecialchars($ticket['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form action="manage_tickets.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                    <select name="new_status" class="form-select form-select-sm" required>
                                        <?php foreach ($status_list as $status): ?>
                                            <option value="<?= $status ?>" <?= $ticket['status'] == $status ? 'selected' : '' ?>>
                                                <?= ucfirst(strtolower($status)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"
                                            name="update_status"
                                            class="btn btn-sm btn-outline-primary btn-rounded">
                                        <i class="bi bi-arrow-clockwise"></i> Update
                                    </button>
                                </form>
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
