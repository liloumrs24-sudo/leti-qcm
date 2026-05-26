<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'questionnaire pro';
$username = 'root';
$password = 'root';  // Sur Windows : $password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estConnecte() {
    return isset($_SESSION['user_id']);
}

function estAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function estBloque($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT est_bloque FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['est_bloque'] == 1;
}

function requireLogin() {
    $currentFile = basename($_SERVER['PHP_SELF']);
    if (!estConnecte() && $currentFile !== 'login.php' && $currentFile !== 'register.php') {
        header('Location: auth/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!estAdmin()) {
        header('Location: ../index.html');
        exit;
    }
}

function genererCode() {
    return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

function envoyerEmail($email, $code) {
    return true;
}
?>