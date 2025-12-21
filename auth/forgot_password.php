<?php
// auth/forgot_password.php
require_once '../config/database.php';
require_once '../config/email.php'; // Menggunakan file email yang Anda upload
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 1. Buat Token & Expiry (Berlaku 1 jam)
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 2. Simpan ke Database
        $stmt_update = $pdo->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE id = ?");
        $stmt_update->execute([$token, $expiry, $user['id']]);

        // 3. Kirim Email
        // Sesuaikan domain/host lokal Anda
        $reset_link = "http://localhost/apk_eventkonser/auth/reset_password.php?token=" . $token;
        
        if (sendResetEmail($email, $user['username'], $reset_link)) {
            $message = "<div class='alert alert-success'>Link reset password telah dikirim ke email Anda.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal mengirim email. Cek konfigurasi SMTP Anda.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Email tidak ditemukan di sistem kami.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background: linear-gradient(to right, #e6ecff, #eef2ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card{
            width: 420px;
            background: #fff;
            border-radius: 26px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0,0,0,.15);
            text-align: center;
        }

        .forgot-card h2{
            font-weight: 700;
            margin-bottom: 10px;
        }

        .forgot-card p{
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .form-control{
            height: 52px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .btn-reset{
            background: #2563eb;
            color: #fff;
            border: none;
            width: 100%;
            height: 52px;
            border-radius: 26px;
            font-weight: 600;
        }

        .btn-reset:hover{
            background: #1e40af;
        }

        .back-login{
            display: block;
            margin-top: 20px;
            font-size: 14px;
            color: #2563eb;
            text-decoration: none;
        }

        .back-login:hover{
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="forgot-card">
    <h2>Forgot Password</h2>
    <p>
        Enter your email address and we will<br>
        send you a link to reset your password.
    </p>

    <!-- FORM ASLI (TIDAK UBAH LOGIC) -->
    <form method="POST">
        <input
            type="email"
            name="email"
            class="form-control"
            placeholder="Enter your email"
            required
        >

        <!-- name="forgot" WAJIB JIKA DIPAKAI LOGIC -->
        <button type="submit" name="forgot" class="btn-reset">
            SEND RESET LINK
        </button>
    </form>

    <a href="login.php" class="back-login">
        ← Back to Login
    </a>
</div>

</body>
</html>
