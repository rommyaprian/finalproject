<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$view = $_GET['view'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f4f6fb;
    font-family:"Segoe UI", sans-serif;
}
.wrapper{
    min-height:100vh;
    display:flex;
    padding:28px;
    gap:28px;
}
.sidebar{
    width:260px;
    background:linear-gradient(180deg,#1e3a8a,#2563eb);
    border-radius:22px;
    padding:26px;
    color:#fff;
    display:flex;
    flex-direction:column;
    box-shadow:0 25px 60px rgba(30,58,138,.35);
}
.sidebar h5{
    font-weight:800;
    text-align:center;
    margin-bottom:32px;
}
.sidebar a{
    color:#e0e7ff;
    text-decoration:none;
    padding:12px 16px;
    border-radius:12px;
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
    font-weight:600;
}
.sidebar a:hover,
.sidebar a.active{
    background:rgba(255,255,255,.18);
}
.content{
    flex:1;
    background:#fff;
    border-radius:26px;
    overflow:hidden;
}
iframe{
    width:100%;
    height:100%;
    border:none;
}
</style>
</head>

<body>
<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">
    <h5>ADMIN DASHBOARD</h5>

    <a href="dashboard.php" class="<?= $view==''?'active':'' ?>">
        <i class="bi bi-house"></i> Dashboard
    </a>

    <a href="dashboard.php?view=events" class="<?= $view=='events'?'active':'' ?>">
        <i class="bi bi-calendar-event"></i> Kelola Event
    </a>

    <a href="dashboard.php?view=scan" class="<?= $view=='scan'?'active':'' ?>">
        <i class="bi bi-qr-code-scan"></i> Verifikasi Tiket
    </a>

    <a href="dashboard.php?view=manage_tickets" class="<?= $view=='manage_tickets'?'active':'' ?>">
        <i class="bi bi-ticket-detailed"></i> Manajemen Tiket
    </a>

    <div class="mt-auto">
        <hr class="opacity-50">
        <a href="../auth/logout.php"
           onclick="window.top.location.href=this.href; return false;">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- CONTENT -->
<div class="content">
<?php if ($view): ?>
    <!-- FILE ASLI, TIDAK DIUBAH -->
    <iframe src="<?= htmlspecialchars($view) ?>.php?embed=1"></iframe>
<?php else: ?>
    <!-- HALAMAN UTAMA -->
    <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center">
        <h1 class="fw-bold text-primary mb-2">Selamat Datang Admin 👋</h1>
        <p class="text-muted">
            Kelola event, tiket, dan verifikasi dalam satu dashboard.
        </p>
    </div>
<?php endif; ?>
</div>

</div>
</body>
</html>
