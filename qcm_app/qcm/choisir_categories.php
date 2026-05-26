<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Nettoyer les anciennes sélections
unset($_SESSION['selected_categories']);

$error = '';

$stmt = $pdo->query("SELECT DISTINCT categorie, COUNT(*) as nb FROM questions GROUP BY categorie ORDER BY categorie");
$categories = $stmt->fetchAll();

if (empty($categories)) {
    die("❌ Aucune catégorie trouvée.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $nbSelected = count($selectedCategories);
    
    if ($nbSelected < 3) {
        $error = "Veuillez sélectionner au moins 3 catégories (vous en avez sélectionné $nbSelected)";
    } else {
        $_SESSION['selected_categories'] = $selectedCategories;
        header('Location: start.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir vos catégories - LETI QCM</title>
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
            background: linear-gradient(135deg, #0a0a0a, #1a1a2e);
            min-height: 100vh;
            color: white;
        }

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

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 2rem 4rem;
        }

        .categories-container {
            background: rgba(20, 20, 30, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            max-width: 800px;
            width: 100%;
            text-align: center;
        }

        .categories-container h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff, #b6465f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .categories-container p {
            color: #a1a1aa;
            margin-bottom: 2rem;
        }

        .selection-info {
            background: rgba(182, 70, 95, 0.15);
            border-radius: 50px;
            padding: 0.8rem 1rem;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .selection-info span {
            font-weight: 700;
            color: #b6465f;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .category-card:hover {
            background: rgba(182, 70, 95, 0.1);
            border-color: rgba(182, 70, 95, 0.3);
        }

        .category-card.selected {
            background: rgba(182, 70, 95, 0.2);
            border-color: #b6465f;
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .category-icon {
            font-size: 1.5rem;
        }

        .category-name {
            font-weight: 500;
        }

        .category-count {
            font-size: 0.7rem;
            color: #71717a;
        }

        .check-icon {
            color: #10b981;
            font-size: 1.2rem;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border-left: 3px solid #ef4444;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #f87171;
        }

        .btn-validate {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-validate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(182, 70, 95, 0.4);
        }

        .btn-validate:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        footer {
            text-align: center;
            padding: 2rem;
            background: #0a0a0a;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #71717a;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            .nav-links {
                display: none;
            }
            .categories-container {
                padding: 1.5rem;
            }
            .categories-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="nav-content">
           <a href="../accueil.php" class="logo">
    <div class="logo-icon"><span>Q</span></div>
    <span class="logo-text">LETI QCM</span>
</a>
            <div class="nav-links">
                <a href="../accueil.php">Accueil</a>
                <a href="../dashboard.php">Mon profil</a>
                <a href="../user/history.php">Historique</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <div class="categories-container">
        <h1>📚 Choisissez vos catégories</h1>
        <p>Sélectionnez au moins 3 catégories pour personnaliser votre QCM</p>

        <div class="selection-info">
            🎯 <span id="selectedCount">0</span> catégorie(s) sélectionnée(s) (minimum 3)
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="categoriesForm">
            <div class="categories-grid">
                <?php 
                $icons = [
                    'Mathématiques' => '📐',
                    'Histoire' => '🏛️',
                    'Informatique' => '💻',
                    'Culture générale' => '🌍',
                    'Sciences' => '🔬',
                    'Géographie' => '🗺️',
                    'Art' => '🎨',
                    'Sport' => '⚽'
                ];
                foreach ($categories as $cat): 
                    $icon = $icons[$cat['categorie']] ?? '📚';
                ?>
                <div class="category-card" data-category="<?php echo htmlspecialchars($cat['categorie']); ?>">
                    <div class="category-info">
                        <span class="category-icon"><?php echo $icon; ?></span>
                        <div>
                            <div class="category-name"><?php echo htmlspecialchars($cat['categorie']); ?></div>
                            <div class="category-count"><?php echo $cat['nb']; ?> questions</div>
                        </div>
                    </div>
                    <div class="check-icon" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($cat['categorie']); ?>" style="display: none;">
                </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" id="validateBtn" class="btn-validate" disabled>
                🎯 Valider et commencer le QCM
            </button>
        </form>
    </div>
</div>

<script>
    const categoryCards = document.querySelectorAll('.category-card');
    const selectedCountSpan = document.getElementById('selectedCount');
    const validateBtn = document.getElementById('validateBtn');
    const MIN_CATEGORIES = 3;

    function updateSelection() {
        const checkboxes = document.querySelectorAll('input[name="categories[]"]');
        let count = 0;
        
        checkboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                count++;
                categoryCards[index].classList.add('selected');
                categoryCards[index].querySelector('.check-icon').style.display = 'flex';
            } else {
                categoryCards[index].classList.remove('selected');
                categoryCards[index].querySelector('.check-icon').style.display = 'none';
            }
        });
        
        selectedCountSpan.textContent = count;
        
        if (count >= MIN_CATEGORIES) {
            validateBtn.disabled = false;
        } else {
            validateBtn.disabled = true;
        }
    }

    categoryCards.forEach((card) => {
        card.addEventListener('click', (e) => {
            e.stopPropagation();
            const checkbox = card.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            updateSelection();
        });
    });

    updateSelection();
</script>

<footer>
    <div class="container">
        <p>© 2026 LETI QCM - Plateforme de tests intelligents</p>
    </div>
</footer>
<script>
// Sélectionner tous les formulaires et boutons de validation
document.querySelectorAll('.btn-validate, button[type="submit"], form').forEach(function(element) {
    element.addEventListener('click', function(e) {
        // Demander le plein écran
        let elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        }
        // Petit délai pour que le plein écran s'active avant de continuer
        setTimeout(function() {
            // Laisser le formulaire se soumettre normalement
        }, 200);
    });
});
</script>
</body>
</html>