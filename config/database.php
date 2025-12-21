<?php
// config/database.php

// Konfigurasi Database
$host = 'localhost';
$db   = 'apk_eventkonser'; // GANTI DENGAN NAMA DATABASE ASLI ANDA
$user = 'root';              // GANTI USERNAME DATABASE ANDA
$pass = '';                  // GANTI PASSWORD DATABASE ANDA (jika ada)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}


/**
 * Fungsi untuk memeriksa status autentikasi pengguna dan peran (role).
 * @param string $required_role 'user', 'admin', atau 'super_admin'.
 */
function checkAuth($required_role = 'user') {
    // START FIX: Cek jika sesi belum dimulai (Baris 18)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // END FIX

    // Jika user belum login, arahkan ke halaman login
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    $current_role = $_SESSION['role'] ?? 'user';

    // Logika otorisasi
    switch ($required_role) {
        case 'super_admin':
            if ($current_role !== 'super_admin') {
                header("Location: ../auth/unauthorized.php");
                exit();
            }
            break;
        case 'admin':
            if ($current_role !== 'admin' && $current_role !== 'super_admin') {
                header("Location: ../auth/unauthorized.php");
                exit();
            }
            break;
        // Role 'user' sudah dicover oleh pengecekan awal
    }
}
?>