<?php
require_once 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!estConnecte()) {
    header('Location: auth/login.php');
    exit;
}

// ========== RÉCUPÉRATION DES STATISTIQUES ==========
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalQuiz = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT AVG(score) as moyenne FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$moyenne = $stmt->fetch()['moyenne'] ?? 0;
$moyenne = $moyenne ? number_format(($moyenne / 10) * 20, 1) : 0;

$stmt = $pdo->prepare("SELECT MAX(score) as meilleur FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$meilleur = $stmt->fetch()['meilleur'] ?? 0;
$meilleur = $meilleur ? number_format(($meilleur / 10) * 20, 1) : 0;

$stmt = $pdo->prepare("SELECT MIN(score) as moins_bon FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$moinsBon = $stmt->fetch()['moins_bon'] ?? 0;
$moinsBon = $moinsBon ? number_format(($moinsBon / 10) * 20, 1) : 0;

// Récupérer les dernières tentatives
$stmt = $pdo->prepare("SELECT * FROM tentatives WHERE utilisateur_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recentes = $stmt->fetchAll();

// Récupérer toutes les catégories
$stmt = $pdo->query("SELECT DISTINCT categorie, COUNT(*) as nb FROM questions GROUP BY categorie");
$categories = $stmt->fetchAll();

// Récupérer le top 5 des meilleurs scores globaux
$stmt = $pdo->query("SELECT u.prenom, u.nom, t.score, t.total_questions 
                     FROM tentatives t 
                     JOIN utilisateurs u ON t.utilisateur_id = u.id 
                     ORDER BY t.score DESC LIMIT 5");
$topScores = $stmt->fetchAll();

// ========== CALCUL DU NIVEAU ET PROGRESSION ==========
if ($moyenne >= 16) {
    $niveau = "🏆 Expert";
    $progression = 100;
} elseif ($moyenne >= 12) {
    $niveau = "📚 Avancé";
    $progression = 75;
} elseif ($moyenne >= 8) {
    $niveau = "📖 Intermédiaire";
    $progression = 50;
} elseif ($moyenne >= 4) {
    $niveau = "🌱 Débutant";
    $progression = 25;
} else {
    $niveau = "🌱 Débutant";
    $progression = 0;
}

if ($totalQuiz == 0) {
    $progression = 0;
    $niveau = "🌱 Débutant";
}

// Message de motivation
if ($moyenne >= 16) {
    $messageMotivation = "🏆 Excellent niveau ! Continuez comme ça !";
    $messageClass = "excellent";
} elseif ($moyenne >= 12) {
    $messageMotivation = "👍 Très bien ! Vous êtes sur la bonne voie !";
    $messageClass = "bien";
} elseif ($moyenne >= 8) {
    $messageMotivation = "📚 Continuez à vous entraîner, vous allez progresser !";
    $messageClass = "medium";
} else {
    $messageMotivation = "💪 Chaque début est difficile, persévérez !";
    $messageClass = "low";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - LETI QCM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            line-height: 1.5;
        }

        /* ========== SCROLLBAR ========== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1a1a1a; }
        ::-webkit-scrollbar-thumb { background: #b6465f; border-radius: 10px; }

        /* ========== NAVBAR ========== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(182, 70, 95, 0.2);
        }

        .container {
            max-width: 1280px;
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
            gap: 0.6rem;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon span {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #a1a1aa;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: #b6465f;
        }

        .btn-admin {
            position: fixed;
            top: 0.8rem;
            right: 1rem;
            z-index: 1001;
            background: #b6465f;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .btn-admin:hover {
            opacity: 0.8;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            padding-top: 6rem;
        }

        /* ========== LEVEL CARD ========== */
        .level-card {
            background: rgba(20, 20, 30, 0.5);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(182, 70, 95, 0.2);
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .level-badge {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border-radius: 40px;
            padding: 0.3rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .level-label {
            color: #a1a1aa;
            font-size: 0.75rem;
        }

        .level-progress-bar {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            height: 8px;
            overflow: hidden;
            margin: 0.8rem 0;
        }

        .level-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #b6465f, #9a3a50);
            border-radius: 20px;
            width: <?php echo $progression; ?>%;
            transition: width 0.5s;
        }

        .level-steps {
            display: flex;
            justify-content: space-between;
            font-size: 0.65rem;
            color: #71717a;
        }

        /* ========== HERO ========== */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-bottom: 2rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(182, 70, 95, 0.1);
            padding: 0.3rem 1rem;
            border-radius: 40px;
            margin-bottom: 1rem;
            border: 1px solid rgba(182, 70, 95, 0.2);
        }

        .hero-badge span {
            width: 6px;
            height: 6px;
            background: #b6465f;
            border-radius: 50%;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            line-height: 1.2;
        }

        .hero p {
            color: #a1a1aa;
            margin-bottom: 1.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .hero-stat {
            background: rgba(255, 255, 255, 0.03);
            padding: 0.6rem 1.2rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .hero-stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #b6465f;
        }

        .hero-stat-label {
            font-size: 0.65rem;
            color: #71717a;
        }

        .btn-start {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(182, 70, 95, 0.3);
        }

        .quiz-illustration {
            background: rgba(20, 20, 30, 0.5);
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid rgba(182, 70, 95, 0.15);
        }

        .quiz-question {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .quiz-options {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .quiz-option {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .quiz-option.correct {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }

        .quiz-progress {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .progress-bar {
            width: 60%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, #b6465f, #9a3a50);
            border-radius: 10px;
        }

        /* ========== MOTIVATION ========== */
        .motivation-card {
            background: rgba(20, 20, 30, 0.5);
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            border: 1px solid rgba(182, 70, 95, 0.15);
            margin-bottom: 2rem;
        }

        .motivation-card.excellent { border-color: #10b981; }
        .motivation-card.bien { border-color: #3b82f6; }
        .motivation-card.medium { border-color: #f59e0b; }
        .motivation-card.low { border-color: #ef4444; }

        .motivation-icon { font-size: 1.8rem; margin-bottom: 0.3rem; }
        .motivation-text { font-size: 1rem; font-weight: 500; }

        /* ========== STATS GRID ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(20, 20, 30, 0.5);
            border: 1px solid rgba(182, 70, 95, 0.1);
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            transition: all 0.2s;
        }

        .stat-card:hover {
            border-color: #b6465f;
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(182, 70, 95, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .stat-icon i {
            font-size: 1.4rem;
            color: #b6465f;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #b6465f;
        }

        .stat-label {
            color: #a1a1aa;
            font-size: 0.75rem;
        }

        /* ========== CATÉGORIES - VERSION AMÉLIORÉE ATTRACTIVE ========== */
        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            background: linear-gradient(135deg, #ffffff, #b6465f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .section-title p {
            color: #a1a1aa;
            font-size: 0.85rem;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .category-card {
            background: rgba(20, 20, 30, 0.6);
            border: 1px solid rgba(182, 70, 95, 0.2);
            border-radius: 24px;
            padding: 1.8rem 1.2rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(182, 70, 95, 0.1), transparent);
            transition: left 0.5s;
        }

        .category-card:hover::before {
            left: 100%;
        }

        .category-card:hover {
            transform: translateY(-6px);
            border-color: #b6465f;
            box-shadow: 0 15px 30px rgba(182, 70, 95, 0.2);
            background: rgba(182, 70, 95, 0.05);
        }

        .category-icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(182, 70, 95, 0.2), rgba(154, 58, 80, 0.1));
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s;
        }

        .category-card:hover .category-icon-wrapper {
            transform: scale(1.1);
            background: linear-gradient(135deg, #b6465f, #9a3a50);
        }

        .category-icon {
            font-size: 2.2rem;
            transition: all 0.3s;
        }

        .category-card:hover .category-icon {
            transform: scale(1.05);
        }

        .category-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .category-card p {
            font-size: 0.75rem;
            color: #a1a1aa;
        }

        .category-stats {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid rgba(182, 70, 95, 0.2);
        }

        .category-stats span {
            font-size: 0.7rem;
            color: #71717a;
        }

        .category-stats i {
            color: #b6465f;
            margin-right: 0.3rem;
        }

        /* ========== RANKING & HISTORY ========== */
        .ranking-card, .history-card {
            background: rgba(20, 20, 30, 0.5);
            border: 1px solid rgba(182, 70, 95, 0.1);
            border-radius: 20px;
            padding: 1.2rem;
            margin-bottom: 2rem;
        }

        .ranking-item, .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .ranking-item:last-child, .history-item:last-child {
            border-bottom: none;
        }

        .ranking-rank {
            font-weight: 700;
            color: #b6465f;
            width: 35px;
        }

        .ranking-score {
            font-weight: 600;
            color: #f59e0b;
        }

        .history-date {
            color: #a1a1aa;
            font-size: 0.75rem;
        }

        .history-score.good { color: #10b981; }
        .history-score.medium { color: #f59e0b; }
        .history-score.bad { color: #ef4444; }

        .empty-history {
            text-align: center;
            padding: 1.5rem;
            color: #71717a;
        }

        .empty-history a {
            color: #b6465f;
            text-decoration: none;
        }

        /* ========== CTA ========== */
        .cta-section {
            text-align: center;
            padding: 3rem 0;
            background: linear-gradient(135deg, rgba(182, 70, 95, 0.05), transparent);
            border-radius: 24px;
            margin-bottom: 2rem;
        }

        .cta-section h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .cta-section p {
            color: #a1a1aa;
            margin-bottom: 1.5rem;
        }

        .btn-large {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(182, 70, 95, 0.3);
        }

        /* ========== FOOTER ========== */
        footer {
            text-align: center;
            padding: 1.5rem;
            border-top: 1px solid rgba(182, 70, 95, 0.1);
            color: #71717a;
            font-size: 0.7rem;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .hero h1 { font-size: 2rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 600px) {
            .container { padding: 0 1rem; }
            .nav-links { display: none; }
            .stats-grid { grid-template-columns: 1fr; }
            .categories-grid { grid-template-columns: 1fr; }
            .hero-stats { gap: 0.5rem; }
            .hero-stat { padding: 0.4rem 0.8rem; }
            .hero-stat-value { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="navbar">
    <div class="container">
        <div class="nav-content">
            <a href="accueil.php" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM</span>
            </a>
            <div class="nav-links">
                <a href="accueil.php">Accueil</a>
                <a href="dashboard.php">Mon profil</a>
                <a href="user/history.php">Historique</a>
                <a href="auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="admin/dashboard.php" class="btn-admin">
        <i class="fas fa-crown"></i> Admin
    </a>
<?php endif; ?>

<main class="main-content">
    <div class="container">

        <!-- NIVEAU & PROGRESSION -->
        <div class="level-card">
            <div class="level-header">
                <div class="level-badge"><?php echo $niveau; ?></div>
                <div class="level-label">Progression</div>
            </div>
            <div class="level-progress-bar">
                <div class="level-progress-fill"></div>
            </div>
            <div class="level-steps">
                <span>🌱 Débutant</span>
                <span>📖 Intermédiaire</span>
                <span>📚 Avancé</span>
                <span>🏆 Expert</span>
            </div>
        </div>

        <!-- HERO -->
        <div class="hero">
            <div class="hero-content">
                <div class="hero-badge">
                    <span></span>
                    <p>Plateforme de tests intelligents</p>
                </div>
                <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?> !</h1>
                <p>Prêt à tester vos connaissances ? Chaque QCM est une nouvelle opportunité d'apprendre.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $totalQuiz; ?></div>
                        <div class="hero-stat-label">Quiz complétés</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $moyenne; ?></div>
                        <div class="hero-stat-label">Moyenne /20</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $meilleur; ?></div>
                        <div class="hero-stat-label">Meilleur score</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $moinsBon; ?></div>
                        <div class="hero-stat-label">À améliorer</div>
                    </div>
                </div>
                <a href="qcm/choisir_categories.php" class="btn-start">
                    <i class="fas fa-play-circle"></i> Commencer un QCM
                </a>
            </div>
            <div class="quiz-illustration">
                <div class="quiz-question">
                    <p style="font-weight: 600; margin-bottom: 0.5rem;">Question 1/10</p>
                    <p>Quelle est la capitale de la France ?</p>
                </div>
                <div class="quiz-options">
                    <div class="quiz-option">Berlin</div>
                    <div class="quiz-option">Madrid</div>
                    <div class="quiz-option correct">Paris ✓</div>
                    <div class="quiz-option">Londres</div>
                </div>
                <div class="quiz-progress">
                    <span style="font-size: 0.7rem; color: #a1a1aa;">Progression</span>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOTIVATION -->
        <div class="motivation-card <?php echo $messageClass; ?>">
            <div class="motivation-icon">
                <?php if ($moyenne >= 16): ?>🏆
                <?php elseif ($moyenne >= 12): ?>👍
                <?php elseif ($moyenne >= 8): ?>📚
                <?php else: ?>💪
                <?php endif; ?>
            </div>
            <div class="motivation-text"><?php echo $messageMotivation; ?></div>
        </div>

        <!-- STATS RAPIDES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                <div class="stat-number">50+</div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
                <div class="stat-number"><?php echo count($categories); ?></div>
                <div class="stat-label">Catégories</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-number"><?php echo $moyenne; ?></div>
                <div class="stat-label">Moyenne</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-number"><?php echo $meilleur; ?></div>
                <div class="stat-label">Record</div>
            </div>
        </div>

        <!-- CATÉGORIES - VERSION ATTRACTIVE AVEC BELLES ICÔNES -->
        <div class="section-title">
            <h2>📚 Explorez nos catégories</h2>
            <p>Choisissez votre thème et testez vos connaissances</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): 
                // Association des icônes Font Awesome par catégorie
                $iconMap = [
                    'Mathématiques' => 'fa-calculator',
                    'Histoire' => 'fa-landmark',
                    'Informatique' => 'fa-laptop-code',
                    'Culture générale' => 'fa-globe-europe',
                    'Sciences' => 'fa-flask',
                    'Géographie' => 'fa-map-location-dot',
                    'Art' => 'fa-palette',
                    'Sport' => 'fa-futbol',
                    'Musique' => 'fa-music',
                    'Littérature' => 'fa-book',
                    'Cinéma' => 'fa-film',
                    'Philosophie' => 'fa-brain',
                ];
                $iconClass = $iconMap[$cat['categorie']] ?? 'fa-question-circle';
            ?>
            <div class="category-card">
                <div class="category-icon-wrapper">
                    <i class="fas <?php echo $iconClass; ?> category-icon"></i>
                </div>
                <h3><?php echo htmlspecialchars($cat['categorie']); ?></h3>
                <div class="category-stats">
                    <span><i class="fas fa-question-circle"></i> <?php echo $cat['nb']; ?> questions</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- TOP SCORES -->
        <div class="section-title">
            <h2>🏆 Top 5 des scores</h2>
            <p>Les meilleurs résultats</p>
        </div>
        <div class="ranking-card">
            <?php if (empty($topScores)): ?>
                <div class="empty-history"><p>Aucun score pour le moment</p></div>
            <?php else: ?>
                <?php foreach ($topScores as $index => $score): 
                    $note = ($score['score'] / $score['total_questions']) * 20;
                ?>
                    <div class="ranking-item">
                        <span class="ranking-rank">#<?php echo $index + 1; ?></span>
                        <span><?php echo htmlspecialchars($score['prenom']); ?></span>
                        <span class="ranking-score"><?php echo number_format($note, 1); ?>/20</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- DERNIERS QCM -->
        <div class="section-title">
            <h2>📋 Dernière activité</h2>
            <p>Vos 5 derniers QCM</p>
        </div>
        <div class="history-card">
            <?php if (empty($recentes)): ?>
                <div class="empty-history">
                    <p>Aucun QCM complété</p>
                    <a href="qcm/choisir_categories.php">Commencer →</a>
                </div>
            <?php else: ?>
                <?php foreach ($recentes as $t): 
                    $note = ($t['score'] / $t['total_questions']) * 20;
                    $scoreClass = $note >= 15 ? 'good' : ($note >= 10 ? 'medium' : 'bad');
                ?>
                    <div class="history-item">
                        <span class="history-date"><?php echo date('d/m/Y H:i', strtotime($t['date'])); ?></span>
                        <span class="history-score <?php echo $scoreClass; ?>"><?php echo number_format($note, 1); ?>/20</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- CTA -->
        <div class="cta-section">
            <h2>🎯 Prêt à relever un défi ?</h2>
            <p>Testez vos connaissances maintenant</p>
            <a href="qcm/choisir_categories.php" class="btn-large">
                <i class="fas fa-play-circle"></i> Commencer un QCM
            </a>
        </div>

    </div>
</main>

<footer>
    <div class="container">
        <p>© 2026 LETI QCM - Plateforme de tests intelligents</p>
    </div>
</footer>

</body>
</html>