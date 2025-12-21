<?php
// user/download_ticket.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Akses ditolak.");
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT t.*, u.username, e.title, e.date, e.time, e.location 
                        FROM tickets t 
                        JOIN users u ON t.user_id = u.id 
                        JOIN events e ON t.event_id = e.id 
                        WHERE t.id = ? AND t.user_id = ? AND t.status = 'PAID'");
$stmt->execute([$ticket_id, $user_id]);
$t = $stmt->fetch();

if (!$t) {
    die("Tiket tidak ditemukan atau belum diverifikasi oleh admin.");
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Desain Tiket dengan Perbaikan Posisi Tengah
$html = "
<html>
<head>
    <style>
        /* 1. Menghilangkan margin bawaan kertas PDF */
        @page { 
            margin: 0px; 
        }
        
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 0;
            background-color: #ffffff;
        }

        /* 2. Pembungkus luar untuk memberi jarak dari atas kertas */
        .wrapper {
            width: 100%;
            padding-top: 50px;
        }

        /* 3. Container tiket dengan lebar tetap dan margin auto agar di tengah */
        .ticket-container { 
            border: 2px solid #000; 
            padding: 25px; 
            width: 520px; /* Lebar tetap supaya margin auto bisa bekerja */
            margin: 0 auto; 
            background-color: #fff;
            position: relative;
        }

        .header { text-align: center; border-bottom: 2px solid #eee; margin-bottom: 20px; padding-bottom: 10px; }
        .event-title { font-size: 24px; font-weight: bold; color: #1a73e8; margin-bottom: 10px; }
        .info-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #f2f2f2; font-size: 14px; }
        .barcode-area { margin-top: 30px; text-align: center; background: #f9f9f9; padding: 15px; border: 1px solid #ddd; }
        .ticket-code { font-family: monospace; font-size: 20px; letter-spacing: 5px; font-weight: bold; }
        .footer-note { font-size: 10px; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='ticket-container'>
            <div class='header'>
                <h1 style='margin:0;'>E-TICKET EVENT</h1>
                <p style='margin:5px 0;'>Harap simpan tiket ini untuk verifikasi masuk</p>
            </div>
            
            <div class='event-title'>{$t['title']}</div>
            
            <table class='info-table'>
                <tr>
                    <td width='35%'><strong>Nama Pemesan</strong></td>
                    <td>: " . htmlspecialchars($t['username']) . "</td>
                </tr>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td>: " . date('d M Y', strtotime($t['date'])) . "</td>
                </tr>
                <tr>
                    <td><strong>Waktu</strong></td>
                    <td>: " . htmlspecialchars($t['time']) . " WIB</td>
                </tr>
                <tr>
                    <td><strong>Lokasi</strong></td>
                    <td>: " . htmlspecialchars($t['location']) . "</td>
                </tr>
            </table>

            <div class='barcode-area'>
                <div style='font-size: 12px; margin-bottom: 5px;'>KODE UNIK TIKET:</div>
                <div class='ticket-code'>{$t['ticket_code']}</div>
            </div>
            
            <p class='footer-note'>* Tiket ini diterbitkan secara otomatis oleh Sistem Tiket Konser pada " . date('d/m/Y H:i') . "</p>
        </div>
    </div>
</body>
</html>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Tiket-" . $t['ticket_code'] . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);