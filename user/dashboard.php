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
<title>User Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f4f6fb;
    font-family:"Segoe UI", sans-serif;
}

/* ===== LAYOUT ===== */
.wrapper{
    min-height:100vh;
    display:flex;
    padding:28px;
    gap:28px;
}

/* ===== SIDEBAR ===== */
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
    letter-spacing:.5px;
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
    transition:.25s;
}

.sidebar a:hover{
    background:rgba(255,255,255,.18);
    transform:translateX(4px);
}

/* ===== CONTENT ===== */
.content{
    flex:1;
    background:#ffffff;
    border-radius:26px;
    box-shadow:0 30px 80px rgba(0,0,0,.08);
    overflow:hidden;
    position:relative;
}

iframe{
    width:100%;
    height:100%;
    border:none;
}

/* ===== WELCOME DASHBOARD ===== */
.welcome-wrapper{
    height:100%;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    animation:fadeUp .8s ease;
}

.welcome-text h1{
    font-weight:800;
    font-size:2.3rem;
    margin-bottom:12px;
    color:#1e3a8a;
}

.welcome-text p{
    color:#64748b;
    max-width:420px;
    margin-bottom:42px;
    font-size:1rem;
}

/* FLOATING CARDS */
.floating-cards{
    display:flex;
    gap:26px;
}

.float-card{
    width:120px;
    height:120px;
    border-radius:24px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    color:#fff;
    font-weight:700;
    box-shadow:0 20px 40px rgba(0,0,0,.18);
    animation:float 4s ease-in-out infinite;
}

.float-card i{
    font-size:34px;
    margin-bottom:8px;
}

.float-card.blue{
    background:linear-gradient(135deg,#2563eb,#3b82f6);
}
.float-card.green{
    background:linear-gradient(135deg,#16a34a,#22c55e);
    animation-delay:1s;
}
.float-card.purple{
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    animation-delay:2s;
}

/* ANIMATION */
@keyframes float{
    0%,100%{ transform:translateY(0); }
    50%{ transform:translateY(-14px); }
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:none;
    }
}
</style>
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h5>USER DASHBOARD</h5>

        <a href="dashboard.php">
            <i class="bi bi-house"></i> Dashboard
        </a>

        <a href="dashboard.php?view=events">
            <i class="bi bi-calendar-event"></i> Semua Event
        </a>

        <a href="dashboard.php?view=my_tickets">
            <i class="bi bi-ticket-perforated"></i> Tiket Saya
        </a>

        <a href="dashboard.php?view=calendar">
            <i class="bi bi-calendar3"></i> Kalender
        </a>

        <div class="mt-auto">
            <hr class="opacity-50">

            <!-- 🔥 LOGOUT FIX (PARENT WINDOW) -->
            <a href="../auth/logout.php"
               onclick="window.top.location.href=this.href; return false;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="content">
        <?php if ($view): ?>
            <!-- IFRAME: ALUR TIDAK DIUBAH -->
            <iframe src="<?= htmlspecialchars($view) ?>.php?embed=1"></iframe>
        <?php else: ?>
            <!-- WELCOME DASHBOARD -->
            <div class="welcome-wrapper">
                <div class="welcome-text">
                    <h1>Selamat Datang 👋</h1>
                    <p>
                        Kelola event, tiket, dan kalender konser
                        dengan mudah melalui satu dashboard terpadu.
                    </p>
                </div>

                <div class="floating-cards">
                    <div class="float-card blue">
                        <i class="bi bi-calendar-event"></i>
                        <span>Event</span>
                    </div>
                    <div class="float-card green">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Tiket</span>
                    </div>
                    <div class="float-card purple">
                        <i class="bi bi-calendar3"></i>
                        <span>Kalender</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
