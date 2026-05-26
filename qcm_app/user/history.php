<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer toutes les tentatives
$stmt = $pdo->prepare("SELECT * FROM tentatives WHERE utilisateur_id = ? ORDER BY date DESC");
$stmt->execute([$_SESSION['user_id']]);
$tentatives = $stmt->fetchAll();

// Moyenne et stats
$stmt = $pdo->prepare("SELECT AVG(score) as moyenne, COUNT(*) as total FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
$moyenne = $stats['moyenne'] ? number_format(($stats['moyenne'] / 10) * 20, 1) : 0;
$totalQCM = $stats['total'];

$stmt = $pdo->prepare("SELECT MAX(score) as meilleur FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$meilleur = $stmt->fetch()['meilleur'];
$meilleur = $meilleur ? number_format(($meilleur / 10) * 20, 1) : 0;

// Détail d'une tentative
$detailTentative = null;
$detailReponses = [];
if (isset($_GET['detail']) && is_numeric($_GET['detail'])) {
    $stmt = $pdo->prepare("SELECT * FROM tentatives WHERE id = ? AND utilisateur_id = ?");
    $stmt->execute([$_GET['detail'], $_SESSION['user_id']]);
    $detailTentative = $stmt->fetch();
    
    if ($detailTentative) {
        $stmt = $pdo->prepare("
            SELECT ru.*, q.question, q.reponse1, q.reponse2, q.reponse3, q.reponse4, q.bonne_reponse 
            FROM reponses_utilisateur ru 
            JOIN questions q ON ru.question_id = q.id 
            WHERE ru.tentative_id = ?
        ");
        $stmt->execute([$_GET['detail']]);
        $detailReponses = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon historique - LETI QCM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0a;
            color: #ffffff;
        }
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(10,10,10,0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-icon span { color: white; font-weight: 800; font-size: 1.2rem; }
        .logo-text { font-size: 1.3rem; font-weight: 700; color: white; }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { text-decoration: none; color: #a1a1aa; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #b6465f; }
        
        .history-wrapper { min-height: 100vh; padding: 6rem 2rem 4rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; background: linear-gradient(135deg, #ffffff, #b6465f); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .page-header p { color: #a1a1aa; }
        
        .stats-boxes { display: flex; justify-content: center; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .stat-box {
            background: rgba(20,20,30,0.6);
            border-radius: 20px;
            padding: 1.2rem 2rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
        .stat-value { font-size: 2rem; font-weight: 800; color: #b6465f; }
        .stat-label { font-size: 0.8rem; color: #a1a1aa; }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(20,20,30,0.6);
            border-radius: 20px;
            overflow: hidden;
        }
        .history-table th {
            text-align: left;
            padding: 1rem;
            background: rgba(0,0,0,0.3);
            color: #a1a1aa;
            font-weight: 500;
        }
        .history-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .history-table tr:hover { background: rgba(255,255,255,0.02); }
        
        .score-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .score-badge.excellent { background: rgba(16,185,129,0.2); color: #10b981; }
        .score-badge.bien { background: rgba(59,130,246,0.2); color: #3b82f6; }
        .score-badge.passable { background: rgba(245,158,11,0.2); color: #f59e0b; }
        .score-badge.a_revoir { background: rgba(239,68,68,0.2); color: #ef4444; }
        
        .detail-link { color: #b6465f; text-decoration: none; font-size: 0.85rem; }
        .detail-link:hover { text-decoration: underline; }
        
        .empty-state { text-align: center; padding: 3rem; color: #a1a1aa; }
        .detail-section {
            background: rgba(20,20,30,0.8);
            border-radius: 24px;
            padding: 1.5rem;
            margin-top: 2rem;
            scroll-margin-top: 80px;
        }
        @keyframes highlight {
            0% { background: rgba(182,70,95,0); }
            50% { background: rgba(182,70,95,0.3); }
            100% { background: rgba(182,70,95,0); }
        }
        .highlight { animation: highlight 1s ease-out; }
        
        .question-detail {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 16px;
            background: rgba(0,0,0,0.2);
        }
        .question-detail.correct { border-left: 3px solid #10b981; }
        .question-detail.wrong { border-left: 3px solid #ef4444; }
        
        footer { text-align: center; padding: 2rem; border-top: 1px solid rgba(255,255,255,0.05); color: #71717a; font-size: 0.75rem; margin-top: 2rem; }
        @media (max-width: 768px) {
            .container { padding: 0 1rem; }
            .nav-links { display: none; }
            .history-table th, .history-table td { padding: 0.75rem 0.5rem; font-size: 0.8rem; }
            .stats-boxes { gap: 1rem; }
            .stat-box { padding: 0.8rem 1.2rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="admin/dashboard.php" class="btn-admin">
        <i class="fas fa-crown"></i> Administration
    </a>
<?php endif; ?>
    <div class="container">
        <div class="nav-content">
            <a href="../accueil.php" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM</span>
            </a>
            <div class="nav-links">
                <a href="../accueil.php">Accueil</a>
                <a href="../dashboard.php">Mon profil</a>
                <a href="history.php">Historique</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="history-wrapper">
    <div class="container">
        
        <div class="page-header">
            <h1>📜 Mon historique</h1>
            <p>Retrouvez toutes vos tentatives et votre progression</p>
        </div>

        <div class="stats-boxes">
            <div class="stat-box">
                <div class="stat-value"><?php echo $moyenne; ?></div>
                <div class="stat-label">Moyenne générale /20</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $totalQCM; ?></div>
                <div class="stat-label">QCM complétés</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $meilleur; ?></div>
                <div class="stat-label">Meilleur score /20</div>
            </div>
        </div>

        <?php if (empty($tentatives)): ?>
            <div class="empty-state">
                <p>Aucun QCM complété pour le moment.</p>
                <a href="../qcm/choisir_categories.php" style="color: #b6465f;">Commencer mon premier QCM →</a>
            </div>
        <?php else: ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tentatives as $t):
                        $note = ($t['score'] / $t['total_questions']) * 20;
                        if ($note >= 16): $class = "excellent";
                        elseif ($note >= 12): $class = "bien";
                        elseif ($note >= 10): $class = "passable";
                        else: $class = "a_revoir";
                        endif;
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($t['date'])); ?></td>
                            <td><?php echo $t['score']; ?>/<?php echo $t['total_questions']; ?></td>
                            <td><span class="score-badge <?php echo $class; ?>"><?php echo number_format($note, 1); ?>/20</span></td>
                            <td><a href="?detail=<?php echo $t['id']; ?>" class="detail-link">Voir détail →</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($detailTentative): 
            $noteDetail = ($detailTentative['score'] / $detailTentative['total_questions']) * 20;
        ?>
            <div id="detailSection" class="detail-section">
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <h3>📝 Détail du QCM du <?php echo date('d/m/Y à H:i', strtotime($detailTentative['date'])); ?></h3>
                    <a href="history.php" style="color: #a1a1aa;">✖ Fermer</a>
                </div>
                <div style="margin-bottom: 1rem; padding: 0.8rem; background: rgba(0,0,0,0.2); border-radius: 12px;">
                    Score : <?php echo $detailTentative['score']; ?>/<?php echo $detailTentative['total_questions']; ?> 
                    (<span class="score-badge <?php 
                        if ($noteDetail >= 16) echo 'excellent';
                        elseif ($noteDetail >= 12) echo 'bien';
                        elseif ($noteDetail >= 10) echo 'passable';
                        else echo 'a_revoir';
                    ?>"><?php echo number_format($noteDetail, 1); ?>/20</span>)
                </div>
                <?php foreach ($detailReponses as $index => $d): 
                    $isCorrect = $d['est_correcte'] == 1;
                    $userAnswerText = $d['reponse_utilisateur'] > 0 ? $d['reponse' . $d['reponse_utilisateur']] : 'Non répondue';
                    $correctAnswerText = $d['reponse' . $d['bonne_reponse']];
                ?>
                    <div class="question-detail <?php echo $isCorrect ? 'correct' : 'wrong'; ?>">
                        <strong>Question <?php echo $index + 1; ?> :</strong> <?php echo htmlspecialchars($d['question']); ?><br>
                        <?php if ($isCorrect): ?>
                            ✅ Votre réponse : <?php echo htmlspecialchars($userAnswerText); ?>
                        <?php else: ?>
                            ❌ Votre réponse : <?php echo htmlspecialchars($userAnswerText); ?><br>
                            ✅ Bonne réponse : <?php echo htmlspecialchars($correctAnswerText); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function scrollToDetail() {
        const detailSection = document.getElementById('detailSection');
        if (detailSection) {
            const offset = 80;
            const elementPosition = detailSection.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
            
            detailSection.classList.add('highlight');
            setTimeout(() => {
                detailSection.classList.remove('highlight');
            }, 1000);
        }
    }

    <?php if (isset($_GET['detail'])): ?>
        window.addEventListener('load', function() {
            setTimeout(scrollToDetail, 300);
        });
    <?php endif; ?>
</script>

<footer>
    <p>© 2026 LETI QCM - Tous droits réservés</p>
</footer>

</body>
</html>