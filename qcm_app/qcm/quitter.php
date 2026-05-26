<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Nettoyer toutes les données de session liées au QCM
unset($_SESSION['qcm_questions']);
unset($_SESSION['current_index']);
unset($_SESSION['score']);
unset($_SESSION['reponses']);
unset($_SESSION['start_time']);
unset($_SESSION['selected_categories']);

// Rediriger vers l'accueil
header('Location: ../accueil.php');
exit;
?>z