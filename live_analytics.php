<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$event_id = (int)$_GET['id'];
$organizer_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT title, date, location, total_seats FROM Events WHERE event_id = ? AND organizer_id = ?");
$stmt->execute([$event_id, $organizer_id]);
$event = $stmt->fetch();

if (!$event) {
    die("<div style='padding: 20px; color: red;'>Unauthorized or event not found.</div>");
}

require_once 'includes/header.php'; 
?>
<div class="card" style="max-width: 1000px; margin: 2rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 25px;">
        <h2 style="margin: 0;">Live Tracker: <span class="text-accent"><?= htmlspecialchars($event['title']) ?></span></h2>
        <a href="my_events.php" class="btn btn-outline" style="width: auto; padding: 10px 20px;">Back to Events</a>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: rgba(255,255,255,0.03); padding: 25px; border-radius: 12px; border-left: 4px solid var(--primary); text-align: center;">
            <p class="text-muted" style="margin-bottom: 10px; font-weight: 500; font-size: 1.1rem;">Tickets Claimed</p>
            <h3 id="stat-claimed" style="font-size: 2.5rem; color: var(--text-main); margin: 0;">-</h3>
        </div>
        <div style="background: rgba(255,255,255,0.03); padding: 25px; border-radius: 12px; border-left: 4px solid var(--secondary); text-align: center;">
            <p class="text-muted" style="margin-bottom: 10px; font-weight: 500; font-size: 1.1rem;">Checked In</p>
            <h3 id="stat-checkedin" style="font-size: 2.5rem; color: var(--secondary); margin: 0;">-</h3>
        </div>
        <div style="background: rgba(255,255,255,0.03); padding: 25px; border-radius: 12px; border-left: 4px solid var(--accent); text-align: center;">
            <p class="text-muted" style="margin-bottom: 10px; font-weight: 500; font-size: 1.1rem;">Pending Arrival</p>
            <h3 id="stat-pending" style="font-size: 2.5rem; color: var(--text-main); margin: 0;">-</h3>
        </div>
    </div>

    <h3>Attendee Log & Overrides</h3>
    <div style="overflow-x: auto; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid var(--border);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.05);">
                    <th style="padding: 15px;">Target ID</th>
                    <th style="padding: 15px;">Name</th>
                    <th style="padding: 15px;">Contact</th>
                    <th style="padding: 15px;">Entry Status</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody id="attendee-list">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Loading live data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const eventId = <?= $event_id ?>;
    let pollInterval;

    function renderTable(data) {
        const tbody = document.getElementById('attendee-list');
        tbody.innerHTML = '';
        
        let claimed = data.length;
        let checkedin = 0;
        
        if (claimed === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No tickets sold yet.</td></tr>';
        }

        data.forEach(t => {
            if (t.used == 1) checkedin++;
            
            const tr = document.createElement('tr');
            tr.style.borderBottom = "1px solid rgba(255,255,255,0.05)";
            
            const name = t.attendee_name || t.buyer_name || 'N/A';
            const tid = t.ticket_id.toString().padStart(6, '0');
            
            let statusBadge = t.used == 1 
                ? `<span class="badge badge-success">✓ In (${t.used_at})</span>` 
                : `<span class="badge badge-neutral">Pending</span>`;
                
            let overrideBtn = t.used == 1
                ? `<button onclick="overrideStatus(${t.ticket_id}, 'reset')" style="background:none; border:none; color: var(--accent); cursor: pointer; text-decoration: underline;">Reset to Pending</button>`
                : `<button onclick="overrideStatus(${t.ticket_id}, 'checkin')" style="background:none; border:none; color: var(--secondary); cursor: pointer; text-decoration: underline;">Manual Check-In</button>`;

            tr.innerHTML = `
                <td style="padding: 15px; color: var(--text-muted);">#${tid}</td>
                <td style="padding: 15px; font-weight: 500;">${name}</td>
                <td style="padding: 15px; font-size: 0.9rem;">${t.attendee_contact || 'N/A'}</td>
                <td style="padding: 15px;">${statusBadge}</td>
                <td style="padding: 15px;">${overrideBtn}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('stat-claimed').innerText = claimed;
        document.getElementById('stat-checkedin').innerText = checkedin;
        document.getElementById('stat-pending').innerText = (claimed - checkedin);
    }

    function fetchLiveFeed() {
        fetch(`api_attendees.php?event_id=${eventId}`)
            .then(res => res.json())
            .then(data => {
                if(!data.error) renderTable(data);
            });
    }

    function overrideStatus(ticketId, action) {
        if (!confirm(`Are you sure you want to ${action} this ticket?`)) return;
        
        fetch('api_override.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ticket_id: ticketId, action: action })
        }).then(res => res.json()).then(resp => {
            if(resp.success) {
                fetchLiveFeed(); // instant sync
            } else {
                alert(resp.error || "Failed override");
            }
        });
    }

    // Start Polling every 3 seconds for Live Tracker
    fetchLiveFeed();
    pollInterval = setInterval(fetchLiveFeed, 3000);
</script>
</body>
</html>
