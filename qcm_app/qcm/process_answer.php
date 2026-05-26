<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_SESSION['qcm_questions'])) {
    header('Location: start.php');
    exit;
}

$answer = isset($_POST['answer']) ? intval($_POST['answer']) : 0;
$currentIndex = $_SESSION['current_index'];
$questions = $_SESSION['qcm_questions'];

if (!isset($questions[$currentIndex])) {
    header('Location: start.php');
    exit;
}

$currentQuestion = $questions[$currentIndex];

// Vérifier la réponse
$isCorrect = ($answer !== 0 && $answer === $currentQuestion['bonne_reponse']);
if ($isCorrect) {
    $_SESSION['score']++;
}

// Sauvegarder la réponse
$reponseTexte = '';
if ($answer > 0 && isset($currentQuestion['reponse' . $answer])) {
    $reponseTexte = $currentQuestion['reponse' . $answer];
}

$bonneReponseTexte = $currentQuestion['reponse' . $currentQuestion['bonne_reponse']];

$_SESSION['reponses'][] = [
    'question' => $currentQuestion['question'],
    'user_answer' => $answer,
    'user_answer_text' => $reponseTexte,
    'correct_answer' => $currentQuestion['bonne_reponse'],
    'correct_answer_text' => $bonneReponseTexte,
    'is_correct' => $isCorrect
];

// Passer à la question suivante
$_SESSION['current_index']++;

// Sauvegarder progression
$reponsesJson = json_encode($_SESSION['reponses']);
$stmt = $pdo->prepare("UPDATE progression_utilisateur SET 
                        question_actuelle = ?, 
                        score = ?, 
                        reponses_sauvegardees = ? 
                        WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['current_index'] + 1, $_SESSION['score'], $reponsesJson, $_SESSION['user_id']]);

header('Location: take.php');
exit;
?>