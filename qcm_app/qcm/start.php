<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
// start.php
$stmt = $pdo->prepare("UPDATE tentatives SET start_time = NOW() WHERE id = ?"); // ou insérer tentative en cours
$stmt->execute([$_SESSION['tentative_id']]);

// Vérifier si des catégories sont sélectionnées
if (!isset($_SESSION['selected_categories']) || empty($_SESSION['selected_categories'])) {
    header('Location: choisir_categories.php');
    exit;
}


$selectedCategories = $_SESSION['selected_categories'];

// Construire la requête SQL
$placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
$sql = "SELECT * FROM questions WHERE categorie IN ($placeholders) ORDER BY RAND() LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute($selectedCategories);
$questions = $stmt->fetchAll();

// Si pas assez de questions, prendre toutes les catégories
if (count($questions) < 10) {
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY RAND() LIMIT 10");
    $questions = $stmt->fetchAll();
}

if (count($questions) == 0) {
    die("❌ Aucune question trouvée.");
}

$_SESSION['qcm_questions'] = $questions;
$_SESSION['current_index'] = 0;
$_SESSION['score'] = 0;
$_SESSION['reponses'] = [];

header('Location: take.php');
// Créer une session d'examen dans la BDD
$stmt = $pdo->prepare("INSERT INTO examens_sessions (user_id, start_time) VALUES (?, NOW())");
$stmt->execute([$_SESSION['user_id']]);
$_SESSION['exam_session_id'] = $pdo->lastInsertId();

exit;

?>