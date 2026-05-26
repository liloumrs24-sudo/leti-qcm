<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user = null;
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

// Modification email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $newEmail = trim($_POST['new_email']);
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$newEmail, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            $code = genererCode();
            $_SESSION['email_change'] = ['new_email' => $newEmail, 'code' => $code, 'expires' => time() + 600];
            envoyerEmail($newEmail, $code);
            $_SESSION['pending_email_change'] = true;
            $success = "Un code a été envoyé à $newEmail";
        }
    }
}

// Vérification code email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email'])) {
    $code = trim($_POST['code']);
    if (!isset($_SESSION['email_change']) || time() > $_SESSION['email_change']['expires']) {
        $error = "Code expiré";
        unset($_SESSION['email_change']);
    } elseif ($code != $_SESSION['email_change']['code']) {
        $error = "Code incorrect";
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET email = ? WHERE id = ?");
        $stmt->execute([$_SESSION['email_change']['new_email'], $_SESSION['user_id']]);
        $_SESSION['user_email'] = $_SESSION['email_change']['new_email'];
        unset($_SESSION['email_change']);
        unset($_SESSION['pending_email_change']);
        $success = "Email modifié !";
    }
}

// Modification mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Tous les champs sont requis";
    } elseif (strlen($newPassword) < 4) {
        $error = "Mot de passe trop court";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentHash = $stmt->fetch()['mot_de_passe'];
        if (!password_verify($currentPassword, $currentHash)) {
            $error = "Mot de passe actuel incorrect";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$newHash, $_SESSION['user_id']]);
            $success = "Mot de passe modifié !";
        }
    }
}

// Statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalQuiz = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT AVG(score) as moyenne FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$moyenne = $stmt->fetch()['moyenne'];
$moyenne = $moyenne ? number_format(($moyenne / 10) * 20, 1) : 0;

$stmt = $pdo->prepare("SELECT MAX(score) as meilleur FROM tentatives WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$meilleur = $stmt->fetch()['meilleur'];
$meilleur = $meilleur ? number_format(($meilleur / 10) * 20, 1) : 0;

// Historique récent
$stmt = $pdo->prepare("SELECT * FROM tentatives WHERE utilisateur_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recentes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - LETI QCM</title>
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
            overflow-x: hidden;
        }

        /* ========== ANIMATIONS ========== */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-up {
            animation: slideUp 0.6s ease-out forwards;
        }

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
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
            transition: transform 0.3s;
        }

        .logo-icon span {
            color: white;
            font-weight: 800;
            font-size: 1.2rem;
        }

        .logo:hover .logo-icon {
            transform: scale(1.05);
        }

        .logo-text {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
        }

        .nav-links {
            display: flex;
            align-items: center;
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
            background: linear-gradient(135deg, #7C5CFF, #FF6B81);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 40px;
        }

        /* ========== MAIN ========== */
        .main-wrapper {
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #b6465f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: #a1a1aa;
        }

        /* ========== STATS GRID ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(20, 20, 30, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(182, 70, 95, 0.3);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #b6465f;
        }

        .stat-label {
            color: #a1a1aa;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* ========== PROFILE GRID ========== */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .profile-card {
            background: rgba(20, 20, 30, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .profile-card h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-label {
            color: #a1a1aa;
            font-size: 0.85rem;
        }

        .info-value {
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            color: #a1a1aa;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #71717a;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            background: rgba(10, 10, 10, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #b6465f;
        }

        .btn-primary {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(182, 70, 95, 0.4);
        }

        /* ========== ALERTES ========== */
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border-left: 3px solid #ef4444;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border-left: 3px solid #10b981;
        }

        .code-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ========== DERNIERS QCM ========== */
        .recent-card {
            background: rgba(20, 20, 30, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            grid-column: span 2;
        }

        .recent-card h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recent-list {
            list-style: none;
        }

        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-date {
            color: #a1a1aa;
            font-size: 0.8rem;
        }

        .recent-score {
            font-weight: 600;
        }

        .recent-score.good { color: #10b981; }
        .recent-score.medium { color: #f59e0b; }
        .recent-score.bad { color: #ef4444; }

        .empty-recent {
            text-align: center;
            padding: 1rem;
            color: #a1a1aa;
        }

        /* ========== FOOTER ========== */
        footer {
            text-align: center;
            padding: 2rem;
            background: #0a0a0a;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #71717a;
            font-size: 0.75rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            .nav-links {
                display: none;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .recent-card {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

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
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn-admin">
                        <i class="fas fa-crown"></i> Administration
                    </a>
                <?php endif; ?>
                <a href="auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <div class="container">

        <div class="welcome-section slide-up">
            <h1>Mon profil</h1>
            <p>Gérez vos informations personnelles</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="stats-grid slide-up">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalQuiz; ?></div>
                <div class="stat-label">QCM complétés</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $moyenne; ?></div>
                <div class="stat-label">Moyenne générale /20</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $meilleur; ?></div>
                <div class="stat-label">Meilleur score /20</div>
            </div>
        </div>

        <div class="profile-grid slide-up">
            <!-- Mes informations -->
            <div class="profile-card">
                <h2><i class="fas fa-user"></i> Mes informations</h2>
                <div class="info-row">
                    <span class="info-label">Nom</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['nom']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Prénom</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['prenom']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Membre depuis</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>

            <!-- Modifier email -->
            <div class="profile-card">
                <h2><i class="fas fa-envelope"></i> Modifier mon email</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nouvel email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="new_email" required placeholder="nouveau@email.com">
                        </div>
                    </div>
                    <button type="submit" name="update_email" class="btn-primary">Envoyer le code</button>
                </form>

                <?php if (isset($_SESSION['pending_email_change'])): ?>
                    <div class="code-form">
                        <form method="POST">
                            <div class="form-group">
                                <label>Code de vérification (4 chiffres)</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-key"></i>
                                    <input type="text" name="code" maxlength="4" placeholder="1234" required>
                                </div>
                            </div>
                            <button type="submit" name="verify_email" class="btn-primary">Vérifier et modifier</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modifier mot de passe -->
            <div class="profile-card">
                <h2><i class="fas fa-lock"></i> Modifier mon mot de passe</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Mot de passe actuel</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="current_password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="new_password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <div class="input-wrapper">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" name="update_password" class="btn-primary">Modifier le mot de passe</button>
                </form>
            </div>

            <!-- Derniers QCM -->
            <div class="recent-card">
                <h2><i class="fas fa-history"></i> Derniers QCM</h2>
                <?php if (empty($recentes)): ?>
                    <div class="empty-recent">
                        <p>Aucun QCM complété pour le moment.</p>
                        <a href="qcm/choisir_categories.php" style="color: #b6465f;">🎯 Commencer un QCM</a>
                    </div>
                <?php else: ?>
                    <ul class="recent-list">
                        <?php foreach ($recentes as $t):
                            $note = ($t['score'] / $t['total_questions']) * 20;
                            $scoreClass = $note >= 15 ? 'good' : ($note >= 10 ? 'medium' : 'bad');
                        ?>
                            <li class="recent-item">
                                <span class="recent-date"><?php echo date('d/m/Y H:i', strtotime($t['date'])); ?></span>
                                <span class="recent-score <?php echo $scoreClass; ?>"><?php echo number_format($note, 1); ?>/20</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="margin-top: 1rem; text-align: center;">
                        <a href="user/history.php" style="color: #b6465f;">Voir tout l'historique →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>© 2026 LETI QCM - Tous droits réservés</p>
    </div>
</footer>

</body>
</html>