<?php
require_once '../config.php';
requireAdmin();

// Supprimer un utilisateur (sauf admin)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete']]);
    header('Location: users.php');
    exit;
}

// Bloquer/Débloquer un utilisateur
if (isset($_GET['block']) && is_numeric($_GET['block'])) {
    $stmt = $pdo->prepare("SELECT est_bloque FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_GET['block']]);
    $user = $stmt->fetch();
    $newStatus = $user['est_bloque'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE utilisateurs SET est_bloque = ? WHERE id = ?");
    $stmt->execute([$newStatus, $_GET['block']]);
    header('Location: users.php');
    exit;
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion utilisateurs - Admin</title>
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
            background: #0F1117;
            color: #F5F7FA;
        }

        /* Dégradé animé */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 0% 0%, rgba(255, 107, 129, 0.12), transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(124, 92, 255, 0.12), transparent 40%);
            z-index: -2;
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1A1D26;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #FF6B81;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #e55a6f;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            background: rgba(15, 17, 23, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 107, 129, 0.2);
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
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
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            opacity: 0.8;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #FF6B81, #7C5CFF);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon span {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #F5F7FA;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #A0A5B5;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #FF6B81, #7C5CFF);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: #FF6B81;
        }

        .admin-wrapper {
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }

        .page-header {
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #F5F7FA, #FF6B81);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-header p {
            color: #A0A5B5;
        }

        .admin-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.6s ease 0.1s both;
        }

        .admin-nav a {
            padding: 0.8rem 1.5rem;
            background: rgba(26, 29, 38, 0.6);
            border: 1px solid rgba(255, 107, 129, 0.15);
            border-radius: 50px;
            text-decoration: none;
            color: #F5F7FA;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .admin-nav a:hover {
            background: rgba(255, 107, 129, 0.15);
            border-color: #FF6B81;
            transform: translateY(-2px);
        }

        .admin-nav a.active {
            background: linear-gradient(135deg, #FF6B81, #e55a6f);
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(255, 107, 129, 0.3);
        }

        .table-container {
            background: rgba(26, 29, 38, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 107, 129, 0.15);
            border-radius: 24px;
            overflow-x: auto;
            padding: 1rem;
            animation: fadeInUp 0.6s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 107, 129, 0.08);
        }

        th {
            color: #A0A5B5;
            font-weight: 500;
        }

        tr {
            transition: all 0.3s;
        }

        tr:hover {
            background: rgba(255, 107, 129, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-admin {
            background: linear-gradient(135deg, #FF6B81, #e55a6f);
            color: white;
        }

        .badge-user {
            background: rgba(255, 255, 255, 0.1);
            color: #A0A5B5;
        }

        .badge-blocked {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .btn-icon {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.2rem;
        }

        .btn-block {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .btn-block:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid rgba(255, 107, 129, 0.1);
            color: #A0A5B5;
            font-size: 0.75rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .container { padding: 0 1rem; }
            .nav-links { display: none; }
            th, td { padding: 0.5rem; font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="nav-content">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><span>Q</span></div>
                <span class="logo-text">LETI QCM Admin</span>
            </a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="users.php" class="active">Utilisateurs</a>
                <a href="questions.php">Questions</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <div class="container">
        <div class="page-header">
            <h1>👥 Gestion des utilisateurs</h1>
            <p>Gérez les comptes utilisateurs (bloquer, supprimer)</p>
        </div>

        <div class="admin-nav">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="users.php" class="active">👥 Utilisateurs</a>
            <a href="questions.php">❓ Questions</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['role'] === 'admin' ? '<span class="badge badge-admin">👑 Admin</span>' : '<span class="badge badge-user">👤 User</span>'; ?></td>
                            <td><?php echo $user['est_bloque'] ? '<span class="badge badge-blocked">🔒 Bloqué</span>' : '<span class="badge badge-active">✅ Actif</span>'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <a href="?block=<?php echo $user['id']; ?>" class="btn-icon btn-block" onclick="return confirm('<?php echo $user['est_bloque'] ? 'Débloquer' : 'Bloquer'; ?> cet utilisateur ?')">
                                        <?php echo $user['est_bloque'] ? '🔓 Débloquer' : '🔒 Bloquer'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Supprimer définitivement cet utilisateur ?')">🗑️ Supprimer</a>
                                <?php else: ?>
                                    <span style="color: #6B7280;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>© 2026 LETI QCM - Administration</p>
    </div>
</footer>

</body>
</html>