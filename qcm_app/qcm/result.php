<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config.php';
// Nettoyer les catégories sélectionnées après le QCM
unset($_SESSION['selected_categories']);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_SESSION['qcm_questions']) || !isset($_SESSION['reponses'])) {
    header('Location: start.php');
    exit;
}

$questions = $_SESSION['qcm_questions'];
$reponses = $_SESSION['reponses'];
$score = $_SESSION['score'];
$total = 10;
$note = ($score / $total) * 20;

// Sauvegarder en base de données
try {
    $stmt = $pdo->prepare("INSERT INTO tentatives (utilisateur_id, score, total_questions, date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $score, $total]);
    $tentativeId = $pdo->lastInsertId();
    
    foreach ($reponses as $index => $rep) {
        $questionId = $questions[$index]['id'];
        $estCorrecte = $rep['is_correct'] ? 1 : 0;
        $stmt = $pdo->prepare("INSERT INTO reponses_utilisateur (tentative_id, question_id, reponse_utilisateur, est_correcte) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tentativeId, $questionId, $rep['user_answer'], $estCorrecte]);
    }
} catch(PDOException $e) {
    // Silencieux
}

// Nettoyer la session
unset($_SESSION['qcm_questions']);
unset($_SESSION['current_index']);
unset($_SESSION['score']);
unset($_SESSION['reponses']);
unset($_SESSION['start_time']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - LETI QCM</title>
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

        /* Main content */
        .results-wrapper {
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }

        /* Score card */
        .score-card {
            background: linear-gradient(135deg, rgba(20, 20, 30, 0.8), rgba(30, 30, 40, 0.9));
            border-radius: 32px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .score-circle {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 0 30px rgba(182, 70, 95, 0.3);
        }

        .score-number {
            font-size: 2.5rem;
            font-weight: 800;
        }

        .score-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .score-details {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .score-detail {
            text-align: center;
        }

        .score-detail-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #b6465f;
        }

        .score-detail-label {
            font-size: 0.75rem;
            color: #a1a1aa;
        }

        .message {
            margin-top: 1rem;
            padding: 0.8rem;
            border-radius: 50px;
            display: inline-block;
        }

        .message.excellent {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .message.bien {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .message.passable {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .message.a_revoir {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        /* Questions details */
        .questions-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .question-item {
            background: rgba(20, 20, 30, 0.6);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .question-item:hover {
            transform: translateX(5px);
        }

        .question-item.correct {
            border-left: 4px solid #10b981;
        }

        .question-item.wrong {
            border-left: 4px solid #ef4444;
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 0.8rem;
            font-size: 1rem;
        }

        .answer-user {
            font-size: 0.85rem;
            color: #a1a1aa;
            margin-bottom: 0.3rem;
        }

        .answer-correct {
            font-size: 0.85rem;
            color: #10b981;
            margin-top: 0.3rem;
            padding-top: 0.3rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .answer-wrong {
            font-size: 0.85rem;
            color: #ef4444;
        }

        /* Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #b6465f, #9a3a50);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(182, 70, 95, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            border: 1px solid #333;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            border-color: #b6465f;
            color: #b6465f;
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: #71717a;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            .nav-links {
                display: none;
            }
            .score-details {
                gap: 1rem;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-primary, .btn-secondary {
                justify-content: center;
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
<a href="quitter.php" class="btn-quit">
    <i class="fas fa-home"></i> Retour à l'accueil
</a>
            <div class="nav-links">
                <a href="../dashboard.php">Accueil</a>
                <a href="start.php">Nouveau QCM</a>
                <a href="../user/history.php">Historique</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="results-wrapper">
    <div class="container">
        
        <!-- Score Card -->
        <div class="score-card">
            <div class="score-circle">
                <div class="score-number"><?php echo number_format($note, 1); ?></div>
                <div class="score-label">/ 20</div>
            </div>
            
            <div class="score-details">
                <div class="score-detail">
                    <div class="score-detail-value"><?php echo $score; ?> / <?php echo $total; ?></div>
                    <div class="score-detail-label">Bonnes réponses</div>
                </div>
                <div class="score-detail">
                    <div class="score-detail-value"><?php echo $total - $score; ?> / <?php echo $total; ?></div>
                    <div class="score-detail-label">Mauvaises réponses</div>
                </div>
                <div class="score-detail">
                    <div class="score-detail-value"><?php echo round(($score / $total) * 100); ?>%</div>
                    <div class="score-detail-label">Taux de réussite</div>
                </div>
            </div>
            
            <div>
                <?php 
                if ($note >= 16): 
                    $message = "🏆 Excellent ! Vous maîtrisez parfaitement le sujet !";
                    $class = "excellent";
                elseif ($note >= 12): 
                    $message = "👍 Très bien ! Continuez comme ça !";
                    $class = "bien";
                elseif ($note >= 10): 
                    $message = "👌 Passable. Vous pouvez faire mieux !";
                    $class = "passable";
                else: 
                    $message = "📚 À revoir. N'hésitez pas à réessayer !";
                    $class = "a_revoir";
                endif;
                ?>
                <div class="message <?php echo $class; ?>"><?php echo $message; ?></div>
            </div>
        </div>
        
        <!-- Questions Details -->
        <div class="questions-title">
            <i class="fas fa-list-check"></i>
            Détail des réponses
        </div>
        
        <?php foreach ($reponses as $index => $rep): 
            $isCorrect = $rep['is_correct'];
            $userAnswer = $rep['user_answer'];
            $userAnswerText = $rep['user_answer_text'] ?? ($userAnswer > 0 ? $questions[$index]['reponse' . $userAnswer] : 'Non répondue');
            $correctAnswerText = $rep['correct_answer_text'] ?? $questions[$index]['reponse' . $questions[$index]['bonne_reponse']];
        ?>
            <div class="question-item <?php echo $isCorrect ? 'correct' : 'wrong'; ?>">
                <div class="question-text">
                    <strong>Question <?php echo $index + 1; ?> :</strong> <?php echo htmlspecialchars($questions[$index]['question']); ?>
                </div>
                
                <div class="answer-user">
                    <?php if ($isCorrect): ?>
                        <i class="fas fa-check-circle" style="color: #10b981;"></i> Votre réponse : <?php echo htmlspecialchars($userAnswerText); ?>
                    <?php else: ?>
                        <i class="fas fa-times-circle" style="color: #ef4444;"></i> Votre réponse : <?php echo htmlspecialchars($userAnswerText); ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!$isCorrect): ?>
                    <div class="answer-correct">
                        <i class="fas fa-check-circle"></i> Bonne réponse : <?php echo htmlspecialchars($correctAnswerText); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="start.php" class="btn-primary">
                <i class="fas fa-redo-alt"></i> Refaire un QCM
            </a>
            <a href="../dashboard.php" class="btn-secondary">
                <i class="fas fa-home"></i> Tableau de bord
            </a>
            <a href="../user/history.php" class="btn-secondary">
                <i class="fas fa-chart-line"></i> Voir historique
            </a>
        </div>
        
    </div>
</div>

// Nettoyer les catégories sélectionnées après le QCM
unset($_SESSION['selected_categories']);
<footer>
    <p>© 2026 LETI QCM - Tous droits réservés</p>
</footer>

</body>
</html>