<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galactic Empire — Uninstall</title>
    <link rel="stylesheet" href="css/global.css">
    <style>
        .uninstall-container {
            max-width: 480px;
            margin: 4rem auto;
            padding: 2rem;
            text-align: center;
        }
        .uninstall-container h1 {
            color: var(--danger);
            margin-bottom: 1rem;
        }
        .uninstall-container p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .uninstall-container .warning {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: var(--danger);
            font-size: 0.85rem;
        }
        .uninstall-container .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .uninstall-container .btn-danger {
            background: var(--danger);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .uninstall-container .btn-danger:hover {
            background: #c0392b;
        }
        .uninstall-msg {
            margin-top: 1rem;
            padding: 0.8rem;
            border-radius: 4px;
            display: none;
        }
        .uninstall-msg.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
            display: block;
        }
        .uninstall-msg.error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
            display: block;
        }
    </style>
</head>
<body>
    <div class="uninstall-container">
        <h1>Uninstall Game</h1>
        <p>This will permanently delete all game data including users, planets, and fleet records.</p>
        <div class="warning">
            <strong>Warning:</strong> This action cannot be undone. All player data will be lost.
        </div>
        <div class="actions">
            <a href="index.php?page=login" class="btn btn-secondary">Cancel</a>
            <button class="btn-danger" id="uninstall-btn" onclick="doUninstall()">Uninstall</button>
        </div>
        <div id="uninstall-msg" class="uninstall-msg"></div>
    </div>

    <script>
        async function doUninstall() {
            if (!confirm('Are you sure you want to uninstall? All game data will be permanently deleted.')) {
                return;
            }

            const result = await fetch('api/uninstall.php', { method: 'POST' });
            const data = await result.json();

            const msg = document.getElementById('uninstall-msg');
            if (data.success) {
                msg.className = 'uninstall-msg success';
                msg.textContent = 'Uninstall complete. Redirecting to installer...';
                setTimeout(() => window.location.href = 'install.html', 1500);
            } else {
                msg.className = 'uninstall-msg error';
                msg.textContent = data.error || 'Uninstall failed';
            }
        }
    </script>
</body>
</html>
