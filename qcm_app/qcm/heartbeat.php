<?php
session_start();
require_once '../config.php';
if (isset($_SESSION['user_id']) && isset($_SESSION['exam_session_id'])) {
    $stmt = $pdo->prepare("UPDATE examens_sessions SET last_heartbeat = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['exam_session_id']]);
}