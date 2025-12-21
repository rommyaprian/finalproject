<?php
// auth/reset_password.php
require_once '../config/database.php';

$message = '';
$token = $_GET['token'] ?? ''; // Ambil token dari URL
$user_id = 0; // Inisialisasi user_id

// 1. Verifikasi Token dan Waktu Kedaluwarsa
if (empty($token)) {
    $message = "<div class='alert alert-danger'>Token reset tidak ditemukan.</div>";
    $token_valid = false;
} else {
    // Ambil data user berdasarkan token
    $stmt = $pdo->prepare("SELECT id, username, token_expiry FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "<div class='alert alert-danger'>Token tidak valid.</div>";
        $token_valid = false;
    } else {
        $user_id = $user['id'];
        $expiry_time = strtotime($user['token_expiry']);
        
        // Cek apakah token sudah kedaluwarsa (1 jam)
        if ($expiry_time < time()) {
            $message = "<div class='alert alert-warning'>Token sudah kedaluwarsa. Silakan ajukan permintaan Lupa Password baru.</div>";
            $token_valid = false;
        } else {
            // Token valid
            $message = "<div class='alert alert-info'>Token valid. Silakan masukkan password baru Anda.</div>";
            $token_valid = true;
        }
    }
}

// 2. Logika Pemrosesan Form Reset Password
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Konfirmasi Password tidak cocok.</div>";
    } elseif (strlen($new_password) < 6) {
        $message = "<div class='alert alert-danger'>Password minimal harus 6 karakter.</div>";
    } else {
        try {
            // Hash password baru sebelum disimpan
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password, dan HAPUS (reset) token dan waktu kedaluwarsa
            $stmt_update = $pdo->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL, token_expiry = NULL 
                WHERE id = ?
            ");
            $stmt_update->execute([$hashed_password, $user_id]);

            $_SESSION['message'] = "<div class='alert alert-success'>Password Anda berhasil diubah! Silakan login menggunakan password baru.</div>";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Gagal menyimpan password baru: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>

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

        .reset-card{
            width: 420px;
            background: #ffffff;
            border-radius: 26px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0,0,0,.15);
            text-align: center;
        }

        .reset-card h2{
            font-weight: 700;
            margin-bottom: 10px;
        }

        .reset-card p{
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .form-control{
            height: 52px;
            border-radius: 14px;
            margin-bottom: 16px;
        }

        .btn-reset{
            background: #2563eb;
            color: #ffffff;
            border: none;
            width: 100%;
            height: 52px;
            border-radius: 26px;
            font-weight: 600;
            margin-top: 8px;
        }

        .btn-reset:hover{
            background: #1e40af;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <h2>Reset Password</h2>
    <p>
        Enter your new password below<br>
        and confirm to reset it.
    </p>

    <!-- FORM ASLI (TIDAK UBAH LOGIC) -->
    <form method="POST">

        <!-- Sesuaikan name="" dengan backend kamu -->
        <input
            type="password"
            name="password"
            class="form-control"
            placeholder="New Password"
            required
        >

        <input
            type="password"
            name="confirm_password"
            class="form-control"
            placeholder="Confirm New Password"
            required
        >

        <!-- name="reset" WAJIB jika dipakai logic -->
        <button type="submit" name="reset" class="btn-reset">
            RESET PASSWORD
        </button>
    </form>
</div>

</body>
</html>
