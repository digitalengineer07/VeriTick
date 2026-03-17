<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("<div style='padding: 20px; color: red;'>Access Denied. Only organizers can scan tickets.</div>");
}

$scanner_id = $_SESSION['user_id'];
$message = '';
$message_color = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qr_payload'])) {
    $scanned_payload = trim($_POST['qr_payload']);
    $stmt = $pdo->prepare("SELECT t.*, e.title, e.organizer_id FROM Tickets t JOIN Events e ON t.event_id = e.event_id WHERE t.qr_code = ?");
    $stmt->execute([$scanned_payload]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        $message = "Invalid Ticket! This QR code does not exist.";
        $message_color = "var(--accent)";
    } else {
        if ($ticket['organizer_id'] != $scanner_id) {
            $message = "Unauthorized! Ticket is for another event.";
            $message_color = "var(--accent)";
            logCheckin($pdo, $ticket['ticket_id'], $scanner_id, 'failed', $scanned_payload);
        } elseif ($ticket['used'] == 1) {
            $used_time = date('M j, Y, g:i a', strtotime($ticket['used_at']));
            $message = "ALREADY SCANNED on " . $used_time;
            $message_color = "#f59e0b";
            logCheckin($pdo, $ticket['ticket_id'], $scanner_id, 'failed', $scanned_payload);
        } else {
            try {
                $pdo->beginTransaction();
                $updateStmt = $pdo->prepare("UPDATE Tickets SET used = 1, used_at = NOW(), used_by_scanner_id = ? WHERE ticket_id = ?");
                $updateStmt->execute([$scanner_id, $ticket['ticket_id']]);
                logCheckin($pdo, $ticket['ticket_id'], $scanner_id, 'OK', $scanned_payload);
                $pdo->commit();
                $message = "SUCCESS! Ticket valid for " . htmlspecialchars($ticket['title']);
                $message_color = "var(--secondary)";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error processing check-in: " . $e->getMessage();
                $message_color = "var(--accent)";
            }
        }
    }
}

function logCheckin($pdo, $ticket_id, $scanner_id, $result, $payload) {
    $logStmt = $pdo->prepare("INSERT INTO Checkins (ticket_id, scanner_id, result, raw_payload) VALUES (?, ?, ?, ?)");
    $logStmt->execute([$ticket_id, $scanner_id, $result, $payload]);
}

require_once 'includes/header.php'; 
?>
<script src="https://unpkg.com/html5-qrcode"></script>
<?php
// Offline Support Backend Logic
// Fetch all valid tickets for this organizer's upcoming events
$offlineStmt = $pdo->prepare("
    SELECT t.qr_code, e.title 
    FROM Tickets t 
    JOIN Events e ON t.event_id = e.event_id 
    WHERE e.organizer_id = ? AND t.used = 0
");
$offlineStmt->execute([$scanner_id]);
$valid_offline_tickets = $offlineStmt->fetchAll(PDO::FETCH_ASSOC);
$offline_cache_json = json_encode($valid_offline_tickets);
?>

<div class="card card-center" style="text-align: center; max-width: 600px; padding: 2rem;">
    <h2>Live Event Scanner</h2>
    <p class="text-muted" style="margin-bottom: 20px; font-weight: 500;">Use camera, upload image, or scanner gun</p>

    <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; flex-wrap: wrap;">
        <button type="button" id="start-camera-btn" class="btn btn-secondary">📷 Start Camera</button>
        <button type="button" id="stop-camera-btn" class="btn btn-outline" style="display: none; border-color: var(--accent); color: var(--accent);">⏹ Stop Camera</button>
        <label class="btn btn-outline" style="cursor: pointer; margin: 0;">
            📁 Upload QR
            <input type="file" id="qr-upload" accept="image/*" style="display: none;">
        </label>
    </div>
    
    <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; display: none; margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 2px dashed rgba(255,255,255,0.2);"></div>
    
    <?php if ($message): ?>
        <div style="background: rgba(255,255,255,0.05); color: <?= $message_color ?>; border: 1px solid <?= $message_color ?>; padding: 20px; border-radius: 12px; font-size: 1.2rem; font-weight: 600; margin-bottom: 25px;">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="checkin.php" id="scan-form">
        <input type="text" name="qr_payload" id="qr_payload" placeholder="Manual Code Entry..." autofocus autocomplete="off" style="text-align: center; font-size: 1.2rem; padding: 15px; border: 2px dashed rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); font-weight: 700;">
        <button type="submit" class="btn btn-primary" style="margin-top: 15px; padding: 12px; font-size: 1.1rem; text-transform: uppercase;">Manual Process</button>
    </form>
</div>

<script>
    // Offline Cache Logic
    const offlineTickets = <?= $offline_cache_json ?>;
    const offlineUsed = JSON.parse(localStorage.getItem('offline_used_tickets') || '[]');

    document.addEventListener("DOMContentLoaded", function() {
        const html5QrCode = new Html5Qrcode("reader");
        const startCamBtn = document.getElementById('start-camera-btn');
        const stopCamBtn = document.getElementById('stop-camera-btn');
        const readerDiv = document.getElementById('reader');
        const qrUpload = document.getElementById('qr-upload');
        let isScanning = false;

        let camerasList = [];

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                camerasList = devices;
                // If cameras are found, we could populate a dropdown here
                // But let's keep it simple: we will just use the integrated camera.
            }
        }).catch(err => {
            console.log("Error getting cameras", err);
        });

        startCamBtn.addEventListener('click', () => {
            readerDiv.style.display = 'block';
            startCamBtn.style.display = 'none';
            stopCamBtn.style.display = 'inline-block';
            
            // Force the integrated/front-facing user camera
            html5QrCode.start(
                { facingMode: "user" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                (errorMessage) => { /* ignore normal scan errors */ }
            ).then(() => {
                isScanning = true;
            }).catch((err) => {
                console.error("Camera start error:", err);
                
                // Fallback: If 'facingMode: user' fails, try the first available camera directly
                if (camerasList.length > 0) {
                    html5QrCode.start(
                        camerasList[0].id,
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        onScanSuccess,
                        (errorMessage) => {}
                    ).then(() => {
                        isScanning = true;
                    }).catch(fallbackErr => {
                        alert("Could not start your integrated camera. Please check your browser permissions.");
                        stopScannerUI();
                    });
                } else {
                    alert("No integrated camera found or permissions denied.");
                    stopScannerUI();
                }
            });
        });

        stopCamBtn.addEventListener('click', () => {
            if (isScanning) {
                html5QrCode.stop().then(() => {
                    stopScannerUI();
                }).catch(err => {
                    stopScannerUI();
                });
            } else {
                stopScannerUI();
            }
        });

        qrUpload.addEventListener('change', (e) => {
            if (e.target.files.length == 0) return;
            const imageFile = e.target.files[0];
            html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    onScanSuccess(decodedText);
                })
                .catch(err => {
                    alert("Could not read QR code from the provided image.");
                });
            // reset input
            e.target.value = "";
        });

        function stopScannerUI() {
            isScanning = false;
            readerDiv.style.display = 'none';
            startCamBtn.style.display = 'inline-block';
            stopCamBtn.style.display = 'none';
        }

        function onScanSuccess(decodedText) {
            if (isScanning) {
                html5QrCode.stop().then(() => {
                    stopScannerUI();
                    processPayload(decodedText);
                }).catch(() => {
                    stopScannerUI();
                    processPayload(decodedText);
                });
            } else {
                processPayload(decodedText);
            }
        }

        function processPayload(decodedText) {
            const inputField = document.getElementById('qr_payload');
            inputField.value = decodedText;

            // Check if Offline
            if (!navigator.onLine) {
                alert("You are offline. Validating locally...");
                validateLocally(decodedText);
            } else {
                document.getElementById('scan-form').submit();
            }
        }

        function validateLocally(payload) {
            if (offlineUsed.includes(payload)) {
                alert("REJECTED: Already Checked-In (Offline Cache)");
                location.reload();
                return;
            }
            
            const ticket = offlineTickets.find(t => t.qr_code === payload);
            if (ticket) {
                offlineUsed.push(payload);
                localStorage.setItem('offline_used_tickets', JSON.stringify(offlineUsed));
                alert("SUCCESS! Ticket valid for " + ticket.title + " (Offline Mode)");
                // We would sync this array when back online. For now, reload to scan next.
                location.reload();
            } else {
                alert("Invalid Ticket or Not Found in Offline Cache!");
                location.reload();
            }
        }

        // Scanner Gun Compatibility
        const inputField = document.getElementById('qr_payload');
        document.body.addEventListener('click', function(e) {
            if(e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT' && !e.target.closest('#reader')) {
                inputField.focus();
            }
        });
        inputField.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary)';
        });
        inputField.addEventListener('blur', function() {
            this.style.borderColor = 'rgba(255,255,255,0.2)';
            setTimeout(() => this.focus(), 100);
        });
    });
</script>
</body>
</html>