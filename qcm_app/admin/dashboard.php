<?php
require_once '../config.php';
requireAdmin();

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM questions");
$totalQuestions = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tentatives");
$totalTentatives = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT AVG(score) as moyenne FROM tentatives");
$moyenneGenerale = $stmt->fetch()['moyenne'];
$moyenneGenerale = $moyenneGenerale ? number_format(($moyenneGenerale / 10) * 20, 1) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LETI QCM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0F1117;
            color: #F5F7FA;
        }

        /* Dégradé animé */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 0% 0%, rgba(255, 107, 129, 0.12), transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(124, 92, 255, 0.12), transparent 40%);
            z-index: -2;
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1A1D26;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #FF6B81;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #e55a6f;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(15, 17, 23, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 107, 129, 0.2);
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .container {
            max-width: 1400px;
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
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            opacity: 0.8;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #FF6B81, #7C5CFF);
            border-radius: 12px;
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
            color: #F5F7FA;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #A0A5B5;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #FF6B81, #7C5CFF);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: #FF6B81;
        }

        .admin-wrapper {
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }

        .page-header {
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #F5F7FA, #FF6B81);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-header p {
            color: #A0A5B5;
        }

        .admin-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        .admin-nav a {
            padding: 0.8rem 1.5rem;
            background: rgba(26, 29, 38, 0.6);
            border: 1px solid rgba(255, 107, 129, 0.15);
            border-radius: 50px;
            text-decoration: none;
            color: #F5F7FA;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background: rgba(255, 107, 129, 0.15);
            border-color: #FF6B81;
            transform: translateY(-2px);
        }

        .admin-nav a.active {
            background: linear-gradient(135deg, #FF6B81, #e55a6f);
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(255, 107, 129, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(26, 29, 38, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 107, 129, 0.15);
            border-radius: 24px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease both;
            animation-delay: calc(0.1s * var(--i, 0));
        }

        .stat-card:nth-child(1) { --i: 1; }
        .stat-card:nth-child(2) { --i: 2; }
        .stat-card:nth-child(3) { --i: 3; }
        .stat-card:nth-child(4) { --i: 4; }

        .stat-card:hover {
            transform: translateY(-6px);
            border-color: #FF6B81;
            box-shadow: 0 15px 40px rgba(255, 107, 129, 0.15);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #FF6B81;
        }

        .stat-label {
            color: #A0A5B5;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(255, 107, 129, 0.1);
            color: #A0A5B5;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .container { padding: 0 1rem; }
            .nav-links { display: none; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-value { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="nav-content">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM Admin</span>
            </a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="users.php">Utilisateurs</a>
                <a href="questions.php">Questions</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <div class="container">
        <div class="page-header">
            <h1>👑 Tableau de bord administrateur</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?></p>
        </div>

        <div class="admin-nav">
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="users.php">👥 Utilisateurs</a>
            <a href="questions.php">❓ Questions</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalQuestions; ?></div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalTentatives; ?></div>
                <div class="stat-label">QCM passés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $moyenneGenerale; ?></div>
                <div class="stat-label">Moyenne générale</div>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>© 2026 LETI QCM - Administration</p>
    </div>
</footer>

</body>
</html>