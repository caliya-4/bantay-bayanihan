<?php
session_start();
require '../db_connect.php';
include '../includes/header.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}

// If opened with a specific report id, mark related notifications as read for this admin
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $nid = (int)$_GET['id'];
    $upd = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND url LIKE ?");
    $upd->execute([$_SESSION['user_id'], "%id={$nid}%"]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Emergencies | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* ── Tooltip system ───────────────────────────────────────── */
        .btn-wrap {
            position: relative;
            display: inline-block;
        }

        .btn-wrap .tooltip {
            visibility: hidden;
            opacity: 0;
            background: #1e293b;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 12px;
            border-radius: 8px;
            white-space: nowrap;
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            pointer-events: none;
            transition: opacity .2s, transform .2s;
            box-shadow: 0 4px 14px rgba(0,0,0,.25);
            line-height: 1.4;
            text-align: center;
            max-width: 200px;
            white-space: normal;
        }

        .btn-wrap .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #1e293b;
        }

        .btn-wrap:hover .tooltip {
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(-3px);
        }

        /* ── Row fade-out animation ──────────────────────────────── */
        @keyframes rowFadeOut {
            0%   { opacity: 1; transform: translateX(0);    background: transparent; }
            30%  { opacity: 1; transform: translateX(0);    background: rgba(16,185,129,.08); }
            100% { opacity: 0; transform: translateX(40px); background: rgba(16,185,129,.08); }
        }

        tr.removing {
            animation: rowFadeOut .55s ease forwards;
            pointer-events: none;
        }

        /* ── Toast notification ──────────────────────────────────── */
        #em-toast {
            display: none;
            position: fixed;
            top: 24px;
            right: 28px;
            z-index: 50000;
            background: #fff;
            border-radius: 12px;
            padding: 16px 22px;
            box-shadow: 0 10px 35px rgba(97,97,255,.22);
            border-left: 5px solid #10b981;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            max-width: 340px;
            animation: toastIn .3s ease;
        }

        #em-toast.error { border-left-color: #ff0065; }

        @keyframes toastIn {
            from { transform: translateX(100px); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }

        /* ── Empty state ─────────────────────────────────────────── */
        #empty-state {
            display: none;
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        #empty-state i  { font-size: 48px; margin-bottom: 16px; opacity: .4; display: block; }
        #empty-state p  { font-size: 16px; font-weight: 600; margin: 0; }

        /* ── Status badges ───────────────────────────────────────── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-pending    { background: #fef3c7; color: #d97706; }
        .status-responding { background: #dbeafe; color: #2563eb; }
        .status-resolved   { background: #d1fae5; color: #059669; }
        .status-handled    { background: #d1fae5; color: #059669; }
        .status-spam       { background: #fee2e2; color: #dc2626; }

        /* ── Action buttons ──────────────────────────────────────── */
        .action-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .btn { cursor: pointer; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-header" style="margin-bottom:28px;">
        <div class="page-header-content">
            <h1>🚨 Manage Emergencies</h1>
            <p>Review, respond to, and resolve emergency reports from Baguio City residents.</p>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="background:#eef6ee;border:1px solid #d4eedb;padding:12px;border-radius:8px;color:#064e3b;margin-bottom:12px;">
            ✅ Update successful.
        </div>
    <?php endif; ?>

    <div style="overflow-x:auto;">
        <table class="table" id="emergencyTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reporter</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="emergencyTbody">
                <?php
                $stmt = $pdo->query("
                    SELECT e.*, u.name as reporter 
                    FROM emergencies e 
                    LEFT JOIN users u ON e.user_id = u.id 
                    WHERE e.status NOT IN ('handled','spam')
                    ORDER BY e.created_at DESC
                ");
                $rows = $stmt->fetchAll();
                foreach($rows as $r):
                    $statusClass = match($r['status']) {
                        'pending'    => 'status-pending',
                        'responding' => 'status-responding',
                        'resolved'   => 'status-resolved',
                        'handled'    => 'status-handled',
                        'spam'       => 'status-spam',
                        default      => ''
                    };
                    $statusIcon = match($r['status']) {
                        'pending'    => '🕐',
                        'responding' => '🔵',
                        'resolved'   => '✅',
                        'handled'    => '✅',
                        'spam'       => '🚫',
                        default      => ''
                    };
                ?>
                <tr id="row-<?= $r['id'] ?>">
                    <td><strong>#<?= $r['id'] ?></strong></td>
                    <td><?= htmlspecialchars($r['reporter'] ?: 'Anonymous') ?></td>
                    <td><?= htmlspecialchars($r['type']) ?></td>
                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($r['description']) ?>">
                        <?= htmlspecialchars($r['description']) ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusIcon ?> <?= ucfirst($r['status']) ?>
                        </span>
                    </td>
                    <td><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></td>
                    <td>
                        <div class="action-cell">

                            <!-- View Report -->
                            <div class="btn-wrap">
                                <button class="btn btn-secondary viewReportBtn"
                                    data-id="<?= $r['id'] ?>"
                                    data-type="<?= htmlspecialchars($r['type']) ?>"
                                    data-description="<?= htmlspecialchars($r['description']) ?>"
                                    data-address="<?= htmlspecialchars($r['address'] ?? '') ?>"
                                    data-photo="<?= htmlspecialchars($r['photo'] ?? '') ?>"
                                    data-lat="<?= htmlspecialchars($r['latitude'] ?? $r['lat'] ?? '') ?>"
                                    data-lng="<?= htmlspecialchars($r['longitude'] ?? $r['lng'] ?? '') ?>"
                                    data-reporter="<?= htmlspecialchars($r['reporter'] ?: 'Anonymous') ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <span class="tooltip">See full report details,<br>photo, and location</span>
                            </div>

                            <!-- Start Handling -->
                            <div class="btn-wrap">
                                <button class="btn btn-handle action-btn"
                                    data-id="<?= $r['id'] ?>"
                                    data-status="responding">
                                    <i class="fas fa-bolt"></i> Respond
                                </button>
                                <span class="tooltip">Mark as actively being<br>handled by a responder</span>
                            </div>

                            <!-- Mark Handled -->
                            <div class="btn-wrap">
                                <button class="btn btn-success action-btn"
                                    data-id="<?= $r['id'] ?>"
                                    data-status="handled"
                                    style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;padding:8px 14px;border-radius:8px;font-weight:700;">
                                    <i class="fas fa-check-circle"></i> Handled
                                </button>
                                <span class="tooltip">Emergency is fully resolved —<br>removes it from this list</span>
                            </div>

                            <!-- Mark Spam -->
                            <div class="btn-wrap">
                                <button class="btn btn-spam action-btn"
                                    data-id="<?= $r['id'] ?>"
                                    data-status="spam">
                                    <i class="fas fa-ban"></i> Spam
                                </button>
                                <span class="tooltip">Flag as false alarm or spam —<br>removes it from this list</span>
                            </div>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Empty state shown when all rows are removed -->
        <div id="empty-state">
            <i class="fas fa-check-shield"></i>
            <p>All emergencies have been handled. Great work! 🎉</p>
        </div>
    </div>
</div>

<!-- ── Report Detail Modal ──────────────────────────────────────────── -->
<div id="reportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:20000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;max-width:800px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.4);position:relative;max-height:90vh;overflow-y:auto;">
        <button id="closeReportModal" style="position:absolute;right:14px;top:14px;border:none;background:#f3f4f6;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:700;font-size:16px;">×</button>

        <h2 id="modalType" style="margin:0 0 8px;color:#ff0065;font-size:22px;"></h2>
        <p style="margin:0 0 4px;color:#374151;"><strong>Reporter:</strong> <span id="modalReporter"></span></p>
        <p id="modalAddress" style="margin:0 0 16px;color:#6b7280;font-size:14px;"></p>

        <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <p style="margin:0 0 8px;color:#111;font-weight:700;">Description</p>
                <p id="modalDescription" style="background:#f8fafc;padding:14px;border-radius:10px;color:#111;margin:0 0 12px;line-height:1.6;"></p>
                <p style="margin:0;color:#6b7280;font-size:13px;"><strong>Coordinates:</strong> <span id="modalCoords"></span></p>
            </div>
            <div id="modalPhotoWrapper" style="width:260px;flex-shrink:0;text-align:center;border-radius:10px;overflow:hidden;background:#f3f4f6;padding:8px;">
                <img id="modalPhoto" src="" alt="Report Photo" style="max-width:100%;height:auto;display:block;margin:0 auto;cursor:zoom-in;border-radius:6px;" />
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
            <a id="openInMap" href="#" target="_blank"
               style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;background:#2563eb;color:white;border-radius:8px;text-decoration:none;font-weight:700;font-size:13px;">
               <i class="fas fa-map-marker-alt"></i> Open in Map
            </a>
            <button onclick="closeReportModal()"
                    style="padding:10px 16px;background:#e2e8f0;border-radius:8px;border:none;font-weight:700;cursor:pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ── Toast ─────────────────────────────────────────────────────────── -->
<div id="em-toast"></div>

<script>
// ── Toast helper ──────────────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = 'success') {
    const t = document.getElementById('em-toast');
    t.innerHTML = msg;
    t.className   = type === 'error' ? 'error' : '';
    t.style.display = 'block';
    t.style.borderLeftColor = type === 'error' ? '#ff0065' : '#10b981';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.style.display = 'none'; }, 3500);
}

// ── Check if table is empty ───────────────────────────────────────────────────
function checkEmpty() {
    const rows = document.querySelectorAll('#emergencyTbody tr:not(.removing)');
    const empty = document.getElementById('empty-state');
    const table = document.getElementById('emergencyTable');
    if (rows.length === 0) {
        table.style.display = 'none';
        empty.style.display = 'block';
    }
}

// ── Action buttons (Respond / Handled / Spam) ─────────────────────────────────
document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id     = btn.dataset.id;
        const status = btn.dataset.status;

        const labels = {
            responding: 'responding',
            handled:    'handled',
            spam:       'spam'
        };

        // Optimistic UI: animate row out immediately for handled/spam
        const removeFromTable = (status === 'handled' || status === 'spam');

        // Disable all buttons on this row while request is in-flight
        const row = document.getElementById('row-' + id);
        row.querySelectorAll('button').forEach(b => b.disabled = true);

        fetch('update_emergency_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`
        })
        .then(r => r.ok ? r : Promise.reject(r))
        .then(() => {
            if (removeFromTable) {
                // Animate row out, then remove from DOM
                row.classList.add('removing');
                row.addEventListener('animationend', () => {
                    row.remove();
                    checkEmpty();
                }, { once: true });
                const msg = status === 'handled'
                    ? '✅ Emergency marked as handled and removed.'
                    : '🚫 Report flagged as spam and removed.';
                showToast(msg, 'success');
            } else {
                // Just update the status badge in-place for "responding"
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    badge.className = 'status-badge status-responding';
                    badge.innerHTML = '🔵 Responding';
                }
                row.querySelectorAll('button').forEach(b => b.disabled = false);
                showToast('🔵 Emergency is being handled — view it on the Emergency Map.', 'success');
            }
        })
        .catch(() => {
            row.querySelectorAll('button').forEach(b => b.disabled = false);
            showToast('❌ Failed to update status. Please try again.', 'error');
        });
    });
});

// ── View report modal ─────────────────────────────────────────────────────────
function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}

document.getElementById('closeReportModal').addEventListener('click', closeReportModal);

document.querySelectorAll('.viewReportBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('modalType').textContent        = '🚨 ' + (btn.dataset.type || '');
        document.getElementById('modalReporter').textContent    = btn.dataset.reporter || 'Anonymous';
        document.getElementById('modalAddress').textContent     = btn.dataset.address  || 'No address provided';
        document.getElementById('modalDescription').textContent = btn.dataset.description || '—';

        const lat = btn.dataset.lat, lng = btn.dataset.lng;
        document.getElementById('modalCoords').textContent = (lat && lng) ? lat + ', ' + lng : 'N/A';

        const photoEl      = document.getElementById('modalPhoto');
        const photoWrapper = document.getElementById('modalPhotoWrapper');
        if (btn.dataset.photo) {
            photoEl.src          = '../uploads/emergency-photos/' + btn.dataset.photo;
            photoWrapper.style.display = 'block';
            photoEl.onclick      = () => window.open(photoEl.src, '_blank');
        } else {
            photoEl.src          = '';
            photoWrapper.style.display = 'none';
        }

        const mapLink = document.getElementById('openInMap');
        if (lat && lng) {
            mapLink.href         = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=18/${lat}/${lng}`;
            mapLink.style.display = 'inline-flex';
        } else {
            mapLink.style.display = 'none';
        }

        document.getElementById('reportModal').style.display = 'flex';
    });
});

// Close modal on backdrop click
document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) closeReportModal();
});
</script>

<script src="../assets/js/chatbot.js"></script>
</body>
</html>