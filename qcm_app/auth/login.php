<?php
require_once '../config.php';

// Si déjà connecté, rediriger vers accueil.php
if (estConnecte()) {
    header('Location: ../accueil.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Email et mot de passe requis";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // ⚠️ REDIRECTION VERS accueil.php ⚠️
            header('Location: ../accueil.php');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LETI QCM</title>
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
            min-height: 100vh;
        }

        /* ========== NAVBAR ========== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(10, 10, 10, 0.8);
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
        }

        .logo-icon span {
            color: white;
            font-weight: 800;
            font-size: 1.2rem;
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

        .nav-links a.active {
            color: #b6465f;
        }

        .btn-nav {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white !important;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
        }

        /* ========== LAYOUT PRINCIPAL ========== */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 2rem 4rem;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1300px;
            width: 100%;
            background: rgba(20, 20, 30, 0.4);
            border-radius: 48px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        /* ========== PARTIE GAUCHE - BRANDING ========== */
        .branding-section {
            background: linear-gradient(135deg, rgba(182, 70, 95, 0.15), rgba(155, 58, 80, 0.05));
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .branding-section::before {
            content: '';
            position: absolute;
            top: -30%;
            left: -30%;
            width: 160%;
            height: 160%;
            background: radial-gradient(circle, rgba(182, 70, 95, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(182, 70, 95, 0.15);
            padding: 0.4rem 1rem;
            border-radius: 40px;
            width: fit-content;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(182, 70, 95, 0.3);
        }

        .badge span {
            width: 8px;
            height: 8px;
            background: #b6465f;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .badge p {
            font-size: 0.8rem;
            color: #b6465f;
            font-weight: 500;
        }

        .branding-section h1 {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff, #b6465f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .branding-section .subtitle {
            color: #a1a1aa;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .mini-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .mini-stat {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .mini-stat:hover {
            transform: translateY(-3px);
            border-color: rgba(182, 70, 95, 0.3);
        }

        .mini-stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #b6465f;
        }

        .mini-stat-label {
            font-size: 0.65rem;
            color: #71717a;
        }

        .category-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .category-tag {
            background: rgba(255, 255, 255, 0.03);
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.75rem;
            color: #a1a1aa;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .category-tag:hover {
            border-color: #b6465f;
            color: #b6465f;
        }

        .dashboard-preview {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .preview-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .preview-card {
            background: rgba(182, 70, 95, 0.1);
            border-radius: 12px;
            padding: 0.8rem;
            text-align: center;
            flex: 1;
        }

        /* ========== PARTIE DROITE - FORMULAIRE ========== */
        .form-section {
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .form-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }

        .form-header p {
            color: #71717a;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
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
            font-size: 1rem;
            transition: color 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 2.8rem;
            background: rgba(20, 20, 30, 0.8);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            font-size: 0.95rem;
            color: white;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #b6465f;
            box-shadow: 0 0 0 4px rgba(182, 70, 95, 0.15);
        }

        .form-group input:focus + i {
            color: #b6465f;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border: none;
            border-radius: 50px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(182, 70, 95, 0.4);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #71717a;
            font-size: 0.75rem;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        }

        .social-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-social {
            flex: 1;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50px;
            color: #a1a1aa;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-social:hover {
            background: rgba(182, 70, 95, 0.1);
            border-color: #b6465f;
            color: #b6465f;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.85rem;
            color: #71717a;
        }

        .register-link a {
            color: #b6465f;
            text-decoration: none;
            font-weight: 600;
        }

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

        footer {
            text-align: center;
            padding: 2rem;
            background: #0a0a0a;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #71717a;
            font-size: 0.75rem;
        }

        @media (max-width: 1024px) {
            .auth-grid {
                grid-template-columns: 1fr;
                max-width: 550px;
            }
            .branding-section {
                text-align: center;
            }
            .badge {
                margin: 0 auto 1.5rem;
            }
            .mini-stats {
                justify-content: center;
            }
            .category-tags {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            .nav-links {
                display: none;
            }
            .branding-section, .form-section {
                padding: 2rem;
            }
            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<nav class="navbar">
    <div class="container">
        <div class="nav-content">
            <a href="../index.html" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM</span>
            </a>
            <div class="nav-links">
                <a href="../index.html">Accueil</a>
                <a href="login.php" class="active">Connexion</a>
                <a href="register.php" class="btn-nav">Inscription</a>
            </div>
        </div>
    </div>
</nav>

<!-- ========== PAGE CONNEXION ========== -->
<div class="auth-container">
    <div class="auth-grid">
        
        <!-- PARTIE GAUCHE - BRANDING -->
        <div class="branding-section">
            <div class="badge">
                <span></span>
                <p>Plateforme QCM</p>
            </div>
            <h1>Bon retour parmi nous</h1>
            <p class="subtitle">Continuez votre progression et testez vos connaissances avec des quiz modernes et interactifs.</p>
            
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-stat-value">50+</div>
                    <div class="mini-stat-label">Questions</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value">5</div>
                    <div class="mini-stat-label">Catégories</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-value">+32%</div>
                    <div class="mini-stat-label">Progression</div>
                </div>
            </div>
            
            <div class="category-tags">
                <span class="category-tag">📐 Mathématiques</span>
                <span class="category-tag">💻 Informatique</span>
                <span class="category-tag">🌍 Culture générale</span>
                <span class="category-tag">🔬 Sciences</span>
                <span class="category-tag">🏛️ Histoire</span>
            </div>
            
            <div class="dashboard-preview">
                <div class="preview-stats">
                    <div class="preview-card">
                        <div style="font-size: 0.7rem; color: #71717a;">Score moyen</div>
                        <div style="font-size: 1.2rem; font-weight: 700; color: #b6465f;">14.5/20</div>
                    </div>
                    <div class="preview-card">
                        <div style="font-size: 0.7rem; color: #71717a;">Taux réussite</div>
                        <div style="font-size: 1.2rem; font-weight: 700; color: #b6465f;">+32%</div>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                    <span style="background: rgba(182,70,95,0.15); padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.65rem;">📊 Top 15%</span>
                    <span style="background: rgba(182,70,95,0.15); padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.65rem;">🏆 10 sessions</span>
                </div>
            </div>
        </div>
        
        <!-- PARTIE DROITE - FORMULAIRE -->
        <div class="form-section">
            <div class="login-card">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                    </div>
                    <h2>Connexion</h2>
                    <p>Accédez à votre espace LETI QCM</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" required placeholder="votre@email.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" required placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">Se connecter</button>
                </form>
                
                <div class="divider">
                    <div class="divider-line"></div>
                    <span>OU</span>
                    <div class="divider-line"></div>
                </div>
                
                <div class="social-buttons">
                    <button class="btn-social"><i class="fab fa-google"></i> Google</button>
                    <button class="btn-social"><i class="fab fa-microsoft"></i> Microsoft</button>
                </div>
                
                <div class="register-link">
                    Pas encore de compte ? <a href="register.php">Créer un compte</a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<footer>
    <p>© 2026 LETI QCM - Tous droits réservés</p>
</footer>

</body>
</html>