<?php
// user/calendar.php
session_start();
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kalender Event</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
/* =========================
   GLOBAL
========================= */
body{
    background:#f4f6fb;
    font-family:"Segoe UI",Roboto,Arial,sans-serif;
    color:#1f2937;
}

/* =========================
   CARD CONTAINER
========================= */
.calendar-wrapper{
    max-width:1100px;
    margin:auto;
}

.calendar-card{
    background:#ffffff;
    border-radius:14px;
    padding:22px 24px;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    border:1px solid #e5e7eb;
}

/* =========================
   HEADER
========================= */
.calendar-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:16px;
}

.calendar-header h4{
    margin:0;
    font-weight:700;
    font-size:18px;
    color:#111827;
}

.calendar-header span{
    font-size:13px;
    color:#6b7280;
}

/* =========================
   FULLCALENDAR HEADER
========================= */
.fc-toolbar-title{
    font-size:16px !important;
    font-weight:700;
    color:#1f2937;
}

.fc-button{
    background:#ffffff !important;
    border:1px solid #d1d5db !important;
    color:#374151 !important;
    font-size:13px !important;
    font-weight:600;
    border-radius:8px !important;
    padding:6px 12px !important;
}

.fc-button:hover{
    background:#f3f4f6 !important;
}

.fc-button-primary:not(:disabled).fc-button-active{
    background:#1e40af !important;
    border-color:#1e40af !important;
    color:#fff !important;
}

/* =========================
   CALENDAR GRID
========================= */
.fc-daygrid-day-number{
    font-size:13px;
    font-weight:600;
    color:#374151;
}

.fc-col-header-cell-cushion{
    font-size:12px;
    font-weight:700;
    color:#6b7280;
}

/* =========================
   EVENT STYLE
========================= */
.fc-event{
    border-radius:8px !important;
    padding:4px 6px;
    font-size:11.5px;
    font-weight:600;
    line-height:1.4;
    border:none !important;
    cursor:pointer;
    transition:background .15s ease, box-shadow .15s ease;
}

.fc-event:hover{
    box-shadow:0 4px 12px rgba(0,0,0,.18);
}

/* =========================
   WARNA EVENT (ALUR TETAP)
========================= */
.event-h1{
    background:#dc2626 !important;
    color:#ffffff !important;
}

.event-h3{
    background:#f59e0b !important;
    color:#ffffff !important;
}

.event-future{
    background:#2563eb !important;
    color:#ffffff !important;
}

/* =========================
   BADGE
========================= */
.badge-h1{
    font-size:9px;
    font-weight:700;
    background:#111827;
    color:#ffffff;
    padding:2px 6px;
    border-radius:6px;
    margin-left:6px;
}

/* =========================
   LIST VIEW
========================= */
.fc-list-event{
    border-radius:10px;
}

.fc-list-event-title{
    font-weight:600;
}
</style>
</head>

<body>

<div class="container my-5 calendar-wrapper">
    <div class="calendar-card">
        <div class="calendar-header">
            <h4>Kalender Event Konser</h4>
            <span>Klik event untuk melihat detail</span>
        </div>

        <div id="calendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 'auto',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },

        events: 'calendar_events.php',

        eventClassNames: function(arg) {
            const today = new Date();
            const eventDate = new Date(arg.event.start);
            const diff = Math.ceil((eventDate - today) / (1000 * 60 * 60 * 24));

            if (diff === 1) return ['event-h1'];
            if (diff <= 3) return ['event-h3'];
            return ['event-future'];
        },

        eventContent: function(arg) {
            const today = new Date();
            const eventDate = new Date(arg.event.start);
            const diff = Math.ceil((eventDate - today) / (1000 * 60 * 60 * 24));

            let label = '';
            if (diff === 1) label = '<span class="badge-h1">H-1</span>';
            else if (diff <= 3) label = '<span class="badge-h1">Soon</span>';

            return {
                html: `
                    <div>
                        ⏰ ${arg.event.title}
                        ${label}
                    </div>
                `
            };
        },

        eventClick: function(info) {
            window.location.href = 'events.php?id=' + info.event.id;
        }
    });

    calendar.render();
});
</script>

</body>
</html>
