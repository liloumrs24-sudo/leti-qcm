<?php
session_start();
require_once '../config.php'; // Adapter le chemin selon votre structure

// Activer l'affichage des erreurs pour le debug (à retirer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Non authentifié');
}

$user_id = $_SESSION['user_id'];
$event = $_POST['event'] ?? '';
$event_data = $_POST['data'] ?? null;

if (empty($event)) {
    exit('Aucun événement spécifié');
}

// Récupérer la session d'examen active de l'utilisateur
$stmt = $pdo->prepare("SELECT id, suspicion_score FROM examens_sessions 
                       WHERE user_id = ? AND status = 'active' 
                       ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$session = $stmt->fetch();

if (!$session) {
    // Aucune session active : on peut quand même logguer mais sans session
    // On va créer une session temporaire (optionnel)
    $stmt = $pdo->prepare("INSERT INTO examens_sessions (user_id, start_time) VALUES (?, NOW())");
    $stmt->execute([$user_id]);
    $session_id = $pdo->lastInsertId();
    $suspicion_score = 0;
} else {
    $session_id = $session['id'];
    $suspicion_score = $session['suspicion_score'];
}

// Points de suspicion selon l'événement
$points = match($event) {
    'tab_switch'       => 10,
    'fullscreen_exit'  => 15,
    'copy_paste'       => 20,
    'devtools'         => 25,
    'shortcut_blocked' => 5,
    default            => 0
};

// Insérer le log dans la table anticheat_logs
$stmt = $pdo->prepare("INSERT INTO anticheat_logs (session_id, user_id, event_type, event_data) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute([$session_id, $user_id, $event, $event_data]);

// Mettre à jour le score de suspicion si nécessaire
if ($points > 0) {
    $nouveau_score = $suspicion_score + $points;
    $stmt = $pdo->prepare("UPDATE examens_sessions SET suspicion_score = ? WHERE id = ?");
    $stmt->execute([$nouveau_score, $session_id]);

    // Si le seuil est atteint, invalider la session
    if ($nouveau_score >= 100) {
        $pdo->prepare("UPDATE examens_sessions SET status = 'invalidated' WHERE id = ?")->execute([$session_id]);
        http_response_code(403);
        echo 'INVALIDATED';
    }
}

// Réponse succès
echo 'OK';