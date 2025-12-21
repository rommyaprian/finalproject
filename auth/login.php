<?php
// auth/login.php
session_start();
require_once '../config/database.php'; // Pastikan path benar

$message = '';

// Cek jika user sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'super_admin') {
        header('Location: ../super_admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/events.php');
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Ambil data user berdasarkan email
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2. Verifikasi Password (Menggunakan password_verify untuk hash yang aman)
            if (password_verify($password, $user['password'])) {
                
                // 3. Cek status aktif (dari kolom is_active)
                if ($user['is_active'] == 0) {
                     $message = "<div class='alert alert-danger'><i class='bi bi-x-octagon-fill me-2'></i> Akun Anda telah diblokir. Silakan hubungi administrator.</div>";
                } else {
                    // 4. Login Berhasil, Set Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role']; 
                    
                    // 5. Redirect berdasarkan role
                    if ($user['role'] === 'super_admin') {
                        header('Location: ../super_admin/dashboard.php');
                    } elseif ($user['role'] === 'admin') {
                        header('Location: ../admin/dashboard.php');
                    } else {
                        header('Location: ../user/dashboard.php');
                    }
                    exit();
                }
            } else {
                $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-octagon me-2'></i> Email atau Password salah.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'><i class='bi bi-exclamation-octagon me-2'></i> Email atau Password salah.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'><i class='bi bi-x-octagon-fill me-2'></i> Kesalahan Database. Silakan coba lagi.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS INLINE (UI ONLY) -->
    <style>
        body{
            background: linear-gradient(to right, #e6ecff, #eef2ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper{
            width: 1000px;
            height: 560px;
            background: #fff;
            border-radius: 28px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,.15);
        }

        /* LEFT */
        .login-left{
            width: 50%;
            padding: 70px;
        }

        .social-box{
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .social-box button{
            width: 42px;
            height: 42px;
            border-radius: 8px;
            border: 1px solid #cfd6e4;
            background: #fff;
        }

        .hint{
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .ui-input{
            height: 54px;
            border-radius: 14px;
            margin-bottom: 16px;
        }

        /* 🔥 BAGIAN YANG DIMINTA */
        .login-action-vertical{
            margin-top: 12px;
            display: flex;
            flex-direction: column;   /* ⬅️ VERTIKAL */
            align-items: flex-start;  /* kiri seperti gambar */
            gap: 14px;
        }

        .forgot-link{
            color: #2563eb;
            font-size: 15px;
            text-decoration: none;
        }

        .forgot-link:hover{
            text-decoration: underline;
        }

        .btn-signin{
            background: #2563eb;
            color: #fff;
            border: none;
            height: 52px;
            padding: 0 36px;
            border-radius: 26px;
            font-weight: 600;
        }

        /* RIGHT */
        .login-right{
            width: 50%;
            background: #4f2bd8;
            color: #fff;
            border-top-left-radius: 300px;
            border-bottom-left-radius: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }

        .btn-signup{
            border: 2px solid #fff;
            color: #fff;
            padding: 12px 36px;
            border-radius: 26px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-signup:hover{
            background: #fff;
            color: #4f2bd8;
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- LEFT -->
    <div class="login-left">
        <h2 class="fw-bold mb-3">Login</h2>

        <div class="hint">use your email password</div>

        <!-- FORM ASLI (LOGIN TETAP JALAN) -->
        <form method="POST">
            <input type="email" name="email" class="form-control ui-input" placeholder="Email" required>
            <input type="password" name="password" class="form-control ui-input" placeholder="Password" required>

            <!-- 🔽 FORGET + SIGN IN (VERTIKAL) -->
            <div class="login-action-vertical">
                <a href="forgot_password.php" class="forgot-link">
                    Forget Your Password?
                </a>

                <!-- name="login" WAJIB -->
                <button type="submit" name="login" class="btn-signin">
                    SIGN IN
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT -->
    <div class="login-right">
        <div>
            <h1 class="fw-bold">Hello, Friend!</h1>
            <p class="mt-3 mb-4">
                The stage awaits! 
                Enter now and be part of the excitement
            </p>
            <a href="register.php" class="btn-signup">
                SIGN UP
            </a>
        </div>
    </div>

</div>

</body>
</html>