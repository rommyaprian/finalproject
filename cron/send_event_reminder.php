<?php
/**
 * cron/send_event_reminder.php
 * Kirim email reminder H-1 event ke user
 * Jalankan via CMD / Task Scheduler
 */

// ============================
// SETUP AWAL
// ============================
date_default_timezone_set('Asia/Jakarta');
echo "=== CRON EVENT REMINDER START ===\n";

// include database (PDO)
require_once __DIR__ . '/../config/database.php';

// validasi koneksi
if (!isset($pdo)) {
    die("ERROR: koneksi database (PDO) tidak ditemukan\n");
}

// ============================
// KONFIG EMAIL (GMAIL API / PHP MAIL)
// ============================

// sementara: pakai mail() dulu (karena Gmail API kamu sudah TEST OK)
function sendEmail($to, $subject, $message) {
    $headers  = "From: Event Konser <no-reply@eventkonser.test>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// ============================
// AMBIL DATA EVENT H-1
// ============================
$sql = "
    SELECT 
        t.id AS ticket_id,
        t.event_id,
        u.email AS email_user,
        e.title AS nama_event,
        e.date AS tanggal_event,
        e.time AS jam_event
    FROM tickets t
    JOIN events e ON e.id = t.event_id
    JOIN users u ON u.id = t.user_id
    WHERE 
        t.status = 'PAID'
        AND t.reminder_sent = 0
        AND DATE(e.date) = DATE(DATE_ADD(CURDATE(), INTERVAL 1 DAY))
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$tickets = $stmt->fetchAll();

if (!$tickets) {
    echo "Tidak ada reminder yang perlu dikirim.\n";
    exit;
}

// ============================
// KIRIM EMAIL
// ============================
foreach ($tickets as $row) {

    $email   = $row['email_user'];
    $event   = $row['nama_event'];
    $tanggal = date('d M Y', strtotime($row['tanggal_event']));
    $jam     = substr($row['jam_event'], 0, 5);

    $subject = "⏰ Reminder Event Besok: $event";
    $message = "
        <h3>Halo!</h3>
        <p>Ini pengingat bahwa event berikut akan segera dimulai:</p>
        <ul>
            <li><b>Event</b>: $event</li>
            <li><b>Tanggal</b>: $tanggal</li>
            <li><b>Jam</b>: $jam WIB</li>
        </ul>
        <p>Terima kasih telah membeli tiket.</p>
        <br>
        <small>Email ini dikirim otomatis.</small>
    ";

    if (sendEmail($email, $subject, $message)) {

        // update reminder_sent
        $update = $pdo->prepare("
            UPDATE tickets 
            SET reminder_sent = 1 
            WHERE id = ?
        ");
        $update->execute([$row['ticket_id']]);

        echo "Reminder terkirim ke $email\n";

    } else {
        echo "GAGAL kirim email ke $email\n";
    }
}

echo "=== CRON EVENT REMINDER SELESAI ===\n";
