<?php
// super_admin/export_excel.php
require_once '../config/database.php';
// Pastikan user adalah super_admin
checkAuth('super_admin');

// 1. Tentukan Header agar browser mengenali ini sebagai file Excel (CSV)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Event_Seluruh_' . date('d-m-Y') . '.csv');

// 2. Buka output stream
$output = fopen('php://output', 'w');

// 3. Tulis Header Kolom di file Excel
fputcsv($output, array('No', 'ID Event', 'Nama Event', 'Tanggal', 'Waktu', 'Lokasi', 'Harga (Rp)', 'Status'));

// 4. Ambil data dari database
// Kita urutkan berdasarkan tanggal terbaru
$query = $pdo->query("SELECT id, title, date, time, location, price FROM events ORDER BY date DESC");

$no = 1;
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // Menentukan status secara dinamis
    $status = ($row['date'] >= date('Y-m-d')) ? 'Upcoming' : 'Passed';
    
    // Tulis data ke baris Excel
    fputcsv($output, [
        $no++,
        $row['id'],
        $row['title'],
        date('d/m/Y', strtotime($row['date'])),
        $row['time'],
        $row['location'],
        $row['price'],
        $status
    ]);
}

// 5. Tutup stream
fclose($output);
exit;