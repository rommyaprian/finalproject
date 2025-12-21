<?php
// config/email.php

// Memuat autoloader dari Composer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- PENGATURAN GLOBAL SMTP ---
// Gunakan 16 digit App Password dari Google (tanpa spasi)
$GLOBALS['SMTP_USERNAME'] = 'rommyaprian@gmail.com'; 
$GLOBALS['SMTP_PASSWORD'] = 'vixz jlby kjwg weex';    

/**
 * Fungsi Pengiriman Email Utama
 */
function sendEmail($recipient_email, $subject, $body_html, $alt_body_text = 'Silakan gunakan browser modern untuk melihat email ini.') {
    $mail = new PHPMailer(true);
    
    try {
        // --- KONFIGURASI DEBUG ---
        // Ubah ke 2 jika ingin melihat pesan error detail saat testing. 
        // Ubah ke 0 jika sudah masuk tahap produksi (live).
        $mail->SMTPDebug = 0; 
        
        // Konfigurasi Server SMTP
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = $GLOBALS['SMTP_USERNAME'];             
        $mail->Password   = $GLOBALS['SMTP_PASSWORD'];               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
        $mail->Port       = 587;                                    

        // Pengaturan Pengirim & Penerima
        $mail->setFrom($GLOBALS['SMTP_USERNAME'], 'Sistem Tiket Konser');
        $mail->addAddress($recipient_email);               

        // Konten Email
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = $alt_body_text;

        // Atur timeout agar tidak macet terlalu lama saat cron job berjalan
        $mail->Timeout = 30; 

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Jika gagal, tampilkan error (berguna saat debugging cron job)
        error_log("Gagal mengirim email ke {$recipient_email}: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Fungsi khusus untuk mengirim email Reset Password
 */
function sendResetEmail($recipient_email, $recipient_name, $reset_link) {
    $subject = 'Permintaan Reset Password Akun Anda';
    $body = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
        <h2 style='color: #333;'>Reset Password</h2>
        <p>Halo <strong>" . htmlspecialchars($recipient_name) . "</strong>,</p>
        <p>Klik tombol di bawah ini untuk mengatur ulang kata sandi Anda:</p>
        <a href='{$reset_link}' style='display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px;'>Reset Password</a>
        <p>Tautan ini berlaku selama 1 jam.</p>
    </div>";

    return sendEmail($recipient_email, $subject, $body);
}

/**
 * Fungsi untuk Pengingat Acara (Reminder)
 */
function sendReminderEmail($recipient_email, $recipient_name, $event_details) {
    $subject = "PENGINGAT: " . $event_details['title'] . " Dimulai Besok!";
    $body = "
    <div style='font-family: Arial, sans-serif; border: 2px solid #28a745; padding: 20px; border-radius: 10px;'>
        <h2 style='color: #28a745;'>Halo " . htmlspecialchars($recipient_name) . "!</h2>
        <p>Ini adalah pengingat untuk acara Anda besok:</p>
        <hr>
        <p><strong>Acara:</strong> {$event_details['title']}</p>
        <p><strong>Waktu:</strong> " . date('d M Y', strtotime($event_details['date'])) . " | {$event_details['time']}</p>
        <p><strong>Lokasi:</strong> {$event_details['location']}</p>
        <hr>
        <p>Siapkan Kode Tiket Anda: <strong>{$event_details['ticket_code']}</strong></p>
        <p>Sampai jumpa di lokasi!</p>
    </div>";

    return sendEmail($recipient_email, $subject, $body);
}