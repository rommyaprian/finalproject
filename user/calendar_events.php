<?php
// user/calendar_events.php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    // REVISI: Menggunakan nama kolom "date" sesuai database Anda
    $stmt = $pdo->query("SELECT id, title, date FROM events");
    $events = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id'    => $row['id'],
            'title' => $row['title'],
            'start' => $row['date'], // FullCalendar butuh 'start' untuk tanggal
            'color' => '#0d6efd'     // Warna biru Bootstrap
        ];
    }

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}