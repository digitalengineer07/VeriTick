<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT t.*, e.title, e.date, e.location 
    FROM Tickets t 
    JOIN Events e ON t.event_id = e.event_id 
    WHERE t.user_id = ? 
    ORDER BY e.date ASC
");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

require_once 'includes/header.php'; 
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<div style="max-width: 900px; margin: 2rem auto;">
    <h2 style="margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; font-size: 2rem;">My Digital Wallet</h2>
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'success'): ?>
            <div class="alert alert-success">
                <span style="font-size: 1.2rem; margin-right: 8px;">🎉</span> Ticket booked successfully!
            </div>
        <?php elseif ($_GET['status'] == 'exists'): ?>
            <div class="alert alert-warning">
                <span style="font-size: 1.2rem; margin-right: 8px;">ℹ️</span> You already have a ticket for this event.
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <?php if (count($tickets) > 0): ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card">
                    <div class="ticket-details" style="padding: 1.5rem;">
                        <h3 style="font-size: 1.5rem; margin-bottom: 10px;"><?= htmlspecialchars($ticket['title']) ?></h3>
                        <p style="margin-bottom: 5px; font-weight: 500; font-size: 0.95rem;"><strong class="text-primary" style="margin-right: 5px;">👤</strong> Attendee: <?= htmlspecialchars($ticket['attendee_name'] ?? $_SESSION['name']) ?></p>
                        <p style="margin-bottom: 5px; font-weight: 500; font-size: 0.95rem;"><strong class="text-success" style="margin-right: 5px;">📅</strong> Date: <?= date('F j, Y, g:i a', strtotime($ticket['date'])) ?></p>
                        <p style="margin-bottom: 5px; font-weight: 500; font-size: 0.95rem;"><strong class="text-danger" style="margin-right: 5px;">📍</strong> Venue: <?= htmlspecialchars($ticket['location']) ?></p>
                        <p style="margin-bottom: 15px; font-weight: 500; font-size: 0.95rem;"><strong class="text-primary" style="margin-right: 5px;">🎟️</strong> Ticket ID: #<?= str_pad($ticket['ticket_id'], 6, '0', STR_PAD_LEFT) ?></p>
                        <div>
                            <?php if ($ticket['used'] == 1): ?>
                                <span class="badge badge-danger">Scanned / Used</span>
                            <?php else: ?>
                                <span class="badge badge-success">Valid Entry</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ticket-qr" style="background: rgba(0, 0, 0, 0.4); border-left: 2px dashed rgba(255,255,255,0.1); padding: 1.5rem;">
                        <p style="margin-bottom: 10px; font-size: 0.8rem; font-weight: 700; color: #cbd5e1; letter-spacing: 1px;">SCAN AT GATE</p>
                        <div class="qrcode-container" data-payload="<?= htmlspecialchars($ticket['qr_code']) ?>" style="background: #ffffff; padding: 5px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 10px;"></div>
                        <p style="margin-bottom: 10px; font-size: 1rem; color: var(--text-main); font-family: monospace; font-weight: 700; letter-spacing: 3px; background: rgba(0,0,0,0.5); padding: 3px 8px; border-radius: 6px;">
                            <?= htmlspecialchars($ticket['qr_code']) ?>
                        </p>
                        <?php if ($ticket['used'] == 1): ?>
                            <div style="position: absolute; inset: 0; background: rgba(11, 15, 25, 0.85); display: flex; align-items: center; justify-content: center; color: var(--accent); font-weight: 800; font-size: 2rem; transform: rotate(-15deg); letter-spacing: 2px;">VOID</div>
                        <?php endif; ?>
                        <button onclick="downloadTicket(this.closest('.ticket-card'))" class="btn btn-outline pdf-ignore" style="padding: 8px; font-size: 0.85rem; width: 100%; border-color: rgba(255,255,255,0.2);">⬇️ Save as PDF</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card"><p class="text-muted" style="text-align: center; font-weight: 500; font-size: 1.1rem;">No tickets yet. Go find some events!</p></div>
        <?php endif; ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const qrContainers = document.querySelectorAll('.qrcode-container');
        qrContainers.forEach(container => {
            const payload = container.getAttribute('data-payload');
            new QRCode(container, {
                text: payload,
                width: 120,
                height: 120,
                colorDark : "#000F08",
                colorLight : "#FFFFFF",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    });

    function downloadTicket(element) {
        const btn = element.querySelector('button');
        if (btn) btn.style.display = 'none';

        // Scale ticket up slightly before capture so resolution is high, format to standard digital ticket dimensions
        const opt = {
            margin:       0,
            filename:     'VeriTick_Pass.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 2, backgroundColor: '#0b0f19', useCORS: true },
            jsPDF:        { unit: 'mm', format: [100, 200], orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            if (btn) btn.style.display = 'block';
        });
    }
</script>
</body>
</html>