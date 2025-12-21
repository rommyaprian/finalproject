<?php
// process_payment.php
require_once 'config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['proof'])) {
    $ticket_id = $_POST['ticket_id'];
    $bank = $_POST['bank'];
    $file = $_FILES['proof'];

    // 1. Validasi File
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        die("Format file tidak didukung.");
    }

    // 2. Buat Folder Jika Belum Ada
    $upload_dir = 'uploads/proofs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // 3. Pindahkan File
    $new_filename = "PROOF_" . time() . "_" . $ticket_id . "." . $file_ext;
    $target_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // 4. Update Database
        $stmt = $pdo->prepare("UPDATE tickets SET payment_method = ?, payment_proof = ?, status = 'PENDING' WHERE id = ?");
        $stmt->execute([$bank, $new_filename, $ticket_id]);

        echo "<script>alert('Bukti pembayaran terkirim! Admin akan segera memverifikasi.'); window.location='my_tickets.php';</script>";
    } else {
        echo "Gagal mengunggah file. Pastikan folder uploads tersedia.";
    }
}