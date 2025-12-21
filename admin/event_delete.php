<?php
// admin/event_delete.php
require_once '../config/database.php';
checkAuth('admin');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$event_id = $_GET['id'];

try {
    // Anda mungkin ingin menambahkan logika untuk menghapus tiket yang terkait
    // Contoh: $pdo->prepare("DELETE FROM tickets WHERE event_id = ?")->execute([$event_id]);
    
    // Hapus event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    
    // Redirect kembali ke halaman events dengan pesan sukses (gunakan session flash)
    session_start();
    $_SESSION['message'] = "<div class='alert alert-success'>Event berhasil dihapus!</div>";
    header("Location: events.php");
    exit();

} catch (PDOException $e) {
    session_start();
    $_SESSION['message'] = "<div class='alert alert-danger'>Gagal menghapus event: " . $e->getMessage() . "</div>";
    header("Location: events.php");
    exit();
}
?>