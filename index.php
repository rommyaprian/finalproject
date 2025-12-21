<?php
session_start();
// Redirect ke dashboard yang sesuai jika sudah login, atau tampilkan halaman landing
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Concert App</title>
</head>
<body>
    <h1>Selamat Datang di Aplikasi Konser</h1>
    <p>Silakan login atau daftar untuk mulai membeli tiket.</p>
    <ul>
        <li><a href="auth/login.php">Login</a></li>
        <li><a href="auth/register.php">Daftar</a></li>
    </ul>
</body>
</html>