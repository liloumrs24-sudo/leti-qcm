<?php
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['register'])) {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $accept_terms = isset($_POST['accept_terms']);
        
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $error = "Tous les champs sont requis";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalide";
        } elseif (strlen($password) < 4) {
            $error = "Mot de passe trop court (min 4)";
        } elseif ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas";
        } elseif (!$accept_terms) {
            $error = "Vous devez accepter les conditions d'utilisation";
        } else {
            // Vérifier si email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Cet email est déjà utilisé";
            } else {
                // Créer l'utilisateur directement (sans vérification email pour le test)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, email_verifie) VALUES (?, ?, ?, ?, 1)");
                
                if ($stmt->execute([$nom, $prenom, $email, $hashedPassword])) {
                    $success = "Inscription réussie ! Redirection vers la connexion...";
                    echo '<meta http-equiv="refresh" content="2;url=login.php">';
                } else {
                    $error = "Erreur lors de l'inscription";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - LETI QCM</title>
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

        .btn-nav {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white !important;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
        }

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

        .branding-section {
            background: linear-gradient(135deg, rgba(182, 70, 95, 0.2), rgba(155, 58, 80, 0.08));
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
            background: radial-gradient(circle, rgba(182, 70, 95, 0.1) 0%, transparent 70%);
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

        .onboarding-cards {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .onboarding-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .onboarding-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .achievement-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .achievement {
            background: rgba(182, 70, 95, 0.15);
            border-radius: 40px;
            padding: 0.4rem 1rem;
            font-size: 0.7rem;
            border: 1px solid rgba(182, 70, 95, 0.2);
        }

        .progress-graph {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .graph-bars {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            height: 80px;
            margin-bottom: 0.5rem;
        }

        .graph-bar {
            flex: 1;
            background: linear-gradient(180deg, #b6465f, #9a3a50);
            border-radius: 8px 8px 4px 4px;
        }
        .graph-bar:nth-child(1) { height: 60px; }
        .graph-bar:nth-child(2) { height: 45px; }
        .graph-bar:nth-child(3) { height: 70px; }
        .graph-bar:nth-child(4) { height: 35px; }
        .graph-bar:nth-child(5) { height: 55px; }
        .graph-bar:nth-child(6) { height: 65px; }

        .form-section {
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bars {
            display: flex;
            gap: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .strength-bar {
            flex: 1;
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            transition: all 0.3s;
        }

        .strength-bar.weak { background: #ef4444; }
        .strength-bar.medium { background: #f59e0b; }
        .strength-bar.strong { background: #10b981; }

        .strength-text {
            font-size: 0.7rem;
            color: #71717a;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            margin: 1rem 0;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #b6465f;
        }

        .checkbox span {
            color: #a1a1aa;
            font-size: 0.8rem;
        }

        .checkbox a {
            color: #b6465f;
            text-decoration: none;
        }

        .btn-register {
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
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(182, 70, 95, 0.4);
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

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border-left: 3px solid #10b981;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.85rem;
            color: #71717a;
        }

        .login-link a {
            color: #b6465f;
            text-decoration: none;
            font-weight: 600;
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
            .onboarding-cards {
                justify-content: center;
            }
            .achievement-badges {
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
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="nav-content">
            <a href="../index.html" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM</span>
            </a>
            <div class="nav-links">
                <a href="../index.html">Accueil</a>
                <a href="login.php">Connexion</a>
                <a href="register.php" class="btn-nav">Inscription</a>
            </div>
        </div>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-grid">
        
        <div class="branding-section">
            <div class="badge">
                <span></span>
                <p>Commencez votre apprentissage</p>
            </div>
            <h1>Commencez votre progression</h1>
            <p class="subtitle">Créez votre compte et accédez à des quiz interactifs modernes.</p>
            
            <div class="onboarding-cards">
                <div class="onboarding-card">
                    <div class="onboarding-icon">📊</div>
                    <div style="font-size: 0.7rem;">Progression</div>
                </div>
                <div class="onboarding-card">
                    <div class="onboarding-icon">🏆</div>
                    <div style="font-size: 0.7rem;">Badges</div>
                </div>
                <div class="onboarding-card">
                    <div class="onboarding-icon">📚</div>
                    <div style="font-size: 0.7rem;">Quiz variés</div>
                </div>
            </div>
            
            <div class="achievement-badges">
                <span class="achievement">🎯 Top 15%</span>
                <span class="achievement">⭐ 50+ quiz</span>
                <span class="achievement">📈 +32% progression</span>
            </div>
            
            <div class="progress-graph">
                <div class="graph-bars">
                    <div class="graph-bar"></div>
                    <div class="graph-bar"></div>
                    <div class="graph-bar"></div>
                    <div class="graph-bar"></div>
                    <div class="graph-bar"></div>
                    <div class="graph-bar"></div>
                </div>
                <p style="font-size: 0.65rem; text-align: center; color: #71717a;">Progression des apprenants</p>
            </div>
        </div>
        
        <div class="form-section">
            <div class="register-card">
                
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Créer un compte</h2>
                    <p>Commencez votre expérience d'apprentissage</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prénom</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="prenom" required placeholder="Jean">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nom</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="nom" required placeholder="Dupont">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" required placeholder="jean.dupont@email.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" required placeholder="••••••••">
                        </div>
                        <div class="password-strength">
                            <div class="strength-bars">
                                <div class="strength-bar" id="bar1"></div>
                                <div class="strength-bar" id="bar2"></div>
                                <div class="strength-bar" id="bar3"></div>
                                <div class="strength-bar" id="bar4"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Entrez un mot de passe</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirm_password" required placeholder="••••••••">
                        </div>
                    </div>
                    
                    <label class="checkbox">
                        <input type="checkbox" name="accept_terms" required>
                        <span>J'accepte les <a href="#">conditions d'utilisation</a></span>
                    </label>
                    
                    <button type="submit" name="register" class="btn-register">Créer mon compte</button>
                </form>
                
                <div class="login-link">
                    Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const bar1 = document.getElementById('bar1');
    const bar2 = document.getElementById('bar2');
    const bar3 = document.getElementById('bar3');
    const bar4 = document.getElementById('bar4');
    const strengthText = document.getElementById('strengthText');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        const bars = [bar1, bar2, bar3, bar4];
        
        bars.forEach((bar, index) => {
            if (index < strength) {
                if (strength <= 2) bar.classList.add('weak');
                else if (strength === 3) bar.classList.add('medium');
                else bar.classList.add('strong');
            } else {
                bar.classList.remove('weak', 'medium', 'strong');
            }
        });
        
        if (strength === 0) strengthText.textContent = 'Entrez un mot de passe';
        else if (strength <= 2) strengthText.textContent = 'Faible';
        else if (strength === 3) strengthText.textContent = 'Moyen';
        else strengthText.textContent = 'Fort';
    });
</script>

<footer>
    <p>© 2026 LETI QCM - Tous droits réservés</p>
</footer>

</body>
</html>