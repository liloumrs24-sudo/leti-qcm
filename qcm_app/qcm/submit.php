<?php
require_once '../config.php';
requireLogin();

if (!isset($_SESSION['questions_30']) || !isset($_SESSION['reponses_utilisateur'])) {
    header('Location: start.php');
    exit;
}

$score = $_SESSION['current_score'];
$reponses = $_SESSION['reponses_utilisateur'];
$questions = $_SESSION['questions_30'];
$tempsTotal = time() - ($_SESSION['start_time'] ?? time());

// Sauvegarder la tentative en base
$stmt = $pdo->prepare("INSERT INTO tentatives (utilisateur_id, score, total_questions, date, temps_total) VALUES (?, ?, 30, NOW(), ?)");
$stmt->execute([$_SESSION['user_id'], $score, $tempsTotal]);
$tentativeId = $pdo->lastInsertId();

// Sauvegarder les réponses détaillées
foreach ($reponses as $index => $rep) {
    $questionId = $questions[$index]['id'];
    $stmt = $pdo->prepare("INSERT INTO reponses_utilisateur (tentative_id, question_id, reponse_utilisateur, est_correcte, temps_reponse) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$tentativeId, $questionId, $rep['reponse'], $rep['correcte'] ? 1 : 0, $rep['temps']]);
}

// Nettoyer la progression
$stmt = $pdo->prepare("UPDATE progression_utilisateur SET tentative_en_cours = 0 WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Stocker les résultats pour affichage
$_SESSION['final_score'] = $score;
$_SESSION['final_reponses'] = $reponses;
$_SESSION['final_questions'] = $questions;

// Nettoyer la session
unset($_SESSION['questions_30']);
unset($_SESSION['current_question_index']);
unset($_SESSION['current_score']);
unset($_SESSION['reponses_utilisateur']);
unset($_SESSION['start_time']);

header('Location: result.php');
exit;
?>