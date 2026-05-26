<?php
require_once '../config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer tous les logs avec détails utilisateur
$stmt = $pdo->query("
    SELECT 
        al.id, al.event_type, al.created_at, al.event_data,
        u.id AS user_id, u.email, u.prenom, u.nom,
        es.suspicion_score, es.status
    FROM anticheat_logs al
    JOIN utilisateurs u ON al.user_id = u.id
    LEFT JOIN examens_sessions es ON al.session_id = es.id
    ORDER BY al.created_at DESC
    LIMIT 500
");
$logs = $stmt->fetchAll();

// Top tricheurs (score cumulé)
$top = $pdo->query("
    SELECT u.id, u.email, u.prenom, u.nom, SUM(es.suspicion_score) as total_suspicion
    FROM examens_sessions es
    JOIN utilisateurs u ON es.user_id = u.id
    GROUP BY u.id
    ORDER BY total_suspicion DESC
    LIMIT 20
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Anti-triche - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: #0F1117; color: #F5F7FA; padding: 2rem; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1, h2 { margin-bottom: 1rem; }
        .stats { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .card { background: rgba(26,29,38,0.7); border-radius: 20px; padding: 1rem 2rem; border: 1px solid rgba(255,107,129,0.3); }
        table { width: 100%; border-collapse: collapse; background: rgba(26,29,38,0.5); border-radius: 20px; overflow: hidden; margin-bottom: 2rem; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
        th { background: #1A1D26; color: #FF6B81; }
        .badge { padding: 0.2rem 0.6rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .badge-high { background: #ef4444; color: white; }
        .badge-medium { background: #f59e0b; color: black; }
        .badge-low { background: #10b981; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>🛡️ Surveillance anti-triche</h1>
    <div class="stats">
        <div class="card">📊 Total événements : <?= count($logs) ?></div>
        <div class="card">🚨 Sessions invalidées : <?= $pdo->query("SELECT COUNT(*) FROM examens_sessions WHERE status='invalidated'")->fetchColumn() ?></div>
    </div>

    <h2>🏆 Top 20 des tricheurs (score suspicion)</h2>
    <table>
        <thead><tr><th>ID</th><th>Utilisateur</th><th>Score</th></tr></thead>
        <tbody>
        <?php foreach ($top as $t): ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['prenom'] . ' ' . $t['nom']) ?> (<?= $t['email'] ?>)</td>
            <td><span class="badge <?= $t['total_suspicion'] >= 100 ? 'badge-high' : ($t['total_suspicion'] >= 50 ? 'badge-medium' : 'badge-low') ?>"><?= $t['total_suspicion'] ?> pts</span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>📋 Derniers événements suspects</h2>
    <table>
        <thead><tr><th>Date</th><th>Utilisateur</th><th>Événement</th><th>Score suspicion</th><th>Session</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
            <td><?= htmlspecialchars($log['prenom'] . ' ' . $log['nom']) ?></td>
            <td><?= $log['event_type'] ?></td>
            <td><?= $log['suspicion_score'] ?? 0 ?> pts</td>
            <td><?= $log['status'] ?? 'active' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>