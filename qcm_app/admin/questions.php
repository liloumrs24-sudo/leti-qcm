<?php
require_once '../config.php';
requireAdmin();

// Ajouter une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = $_POST['question'];
    $reponse1 = $_POST['reponse1'];
    $reponse2 = $_POST['reponse2'];
    $reponse3 = $_POST['reponse3'];
    $reponse4 = $_POST['reponse4'];
    $bonne_reponse = $_POST['bonne_reponse'];
    $categorie = $_POST['categorie'];

    $stmt = $pdo->prepare("INSERT INTO questions (question, reponse1, reponse2, reponse3, reponse4, bonne_reponse, categorie) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$question, $reponse1, $reponse2, $reponse3, $reponse4, $bonne_reponse, $categorie]);
    header('Location: questions.php');
    exit;
}

// Supprimer une question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: questions.php');
    exit;
}

// Récupérer une question pour modification
$editQuestion = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editQuestion = $stmt->fetch();
}

// Modifier une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_question'])) {
    $id = $_POST['id'];
    $question = $_POST['question'];
    $reponse1 = $_POST['reponse1'];
    $reponse2 = $_POST['reponse2'];
    $reponse3 = $_POST['reponse3'];
    $reponse4 = $_POST['reponse4'];
    $bonne_reponse = $_POST['bonne_reponse'];
    $categorie = $_POST['categorie'];

    $stmt = $pdo->prepare("UPDATE questions SET question=?, reponse1=?, reponse2=?, reponse3=?, reponse4=?, bonne_reponse=?, categorie=? WHERE id=?");
    $stmt->execute([$question, $reponse1, $reponse2, $reponse3, $reponse4, $bonne_reponse, $categorie, $id]);
    header('Location: questions.php');
    exit;
}

// Récupérer toutes les questions
$stmt = $pdo->query("SELECT * FROM questions ORDER BY id DESC");
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion questions - Admin</title>
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

        .form-card {
            background: rgba(26, 29, 38, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 107, 129, 0.15);
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease;
        }

        .form-card:hover {
            border-color: rgba(255, 107, 129, 0.3);
            transform: translateY(-3px);
        }

        .form-card h3 {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #F5F7FA;
        }

        .form-card h3 i {
            color: #FF6B81;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #A0A5B5;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            background: rgba(15, 17, 23, 0.8);
            border: 1px solid rgba(255, 107, 129, 0.2);
            border-radius: 12px;
            color: #F5F7FA;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FF6B81;
            box-shadow: 0 0 0 3px rgba(255, 107, 129, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B81, #e55a6f);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 129, 0.4);
        }

        .btn-secondary {
            background: rgba(26, 29, 38, 0.8);
            color: #F5F7FA;
            padding: 0.8rem 1.5rem;
            border: 1px solid rgba(255, 107, 129, 0.3);
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: rgba(255, 107, 129, 0.15);
            border-color: #FF6B81;
            transform: translateY(-2px);
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

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .btn-edit:hover {
            background: #3b82f6;
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
            .form-row { grid-template-columns: 1fr; }
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
                <a href="users.php">Utilisateurs</a>
                <a href="questions.php" class="active">Questions</a>
                <a href="../auth/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <div class="container">
        <div class="page-header">
            <h1>❓ Gestion des questions</h1>
            <p>Ajoutez, modifiez ou supprimez des questions</p>
        </div>

        <div class="admin-nav">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="users.php">👥 Utilisateurs</a>
            <a href="questions.php" class="active">❓ Questions</a>
        </div>

        <!-- Formulaire ajout/modification -->
        <div class="form-card">
            <h3>
                <i class="fas <?php echo isset($editQuestion) ? 'fa-pen' : 'fa-plus'; ?>"></i>
                <?php echo isset($editQuestion) ? 'Modifier la question' : 'Ajouter une nouvelle question'; ?>
            </h3>
            <form method="POST">
                <?php if (isset($editQuestion)): ?>
                    <input type="hidden" name="id" value="<?php echo $editQuestion['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Question</label>
                    <textarea name="question" rows="3" required><?php echo isset($editQuestion) ? htmlspecialchars($editQuestion['question']) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Réponse 1</label>
                        <input type="text" name="reponse1" value="<?php echo isset($editQuestion) ? htmlspecialchars($editQuestion['reponse1']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Réponse 2</label>
                        <input type="text" name="reponse2" value="<?php echo isset($editQuestion) ? htmlspecialchars($editQuestion['reponse2']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Réponse 3</label>
                        <input type="text" name="reponse3" value="<?php echo isset($editQuestion) ? htmlspecialchars($editQuestion['reponse3']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Réponse 4</label>
                        <input type="text" name="reponse4" value="<?php echo isset($editQuestion) ? htmlspecialchars($editQuestion['reponse4']) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Bonne réponse (1, 2, 3 ou 4)</label>
                        <input type="number" name="bonne_reponse" min="1" max="4" value="<?php echo isset($editQuestion) ? $editQuestion['bonne_reponse'] : '1'; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="categorie" required>
                            <option value="Général" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Général') ? 'selected' : ''; ?>>Général</option>
                            <option value="Mathématiques" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Mathématiques') ? 'selected' : ''; ?>>Mathématiques</option>
                            <option value="Histoire" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Histoire') ? 'selected' : ''; ?>>Histoire</option>
                            <option value="Informatique" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Informatique') ? 'selected' : ''; ?>>Informatique</option>
                            <option value="Culture générale" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Culture générale') ? 'selected' : ''; ?>>Culture générale</option>
                            <option value="Sciences" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Sciences') ? 'selected' : ''; ?>>Sciences</option>
                            <option value="Géographie" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Géographie') ? 'selected' : ''; ?>>Géographie</option>
                            <option value="Art" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Art') ? 'selected' : ''; ?>>Art</option>
                            <option value="Sport" <?php echo (isset($editQuestion) && $editQuestion['categorie'] == 'Sport') ? 'selected' : ''; ?>>Sport</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <?php if (isset($editQuestion)): ?>
                        <a href="questions.php" class="btn-secondary">Annuler</a>
                    <?php endif; ?>
                    <button type="submit" name="<?php echo isset($editQuestion) ? 'edit_question' : 'add_question'; ?>" class="btn-primary">
                        <?php echo isset($editQuestion) ? '✏️ Modifier' : '➕ Ajouter'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des questions -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Question</th>
                        <th>Réponses</th>
                        <th>Bonne</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($q['question'], 0, 60)) . '...'; ?></td>
                            <td>
                                <?php echo htmlspecialchars($q['reponse1']); ?><br>
                                <?php echo htmlspecialchars($q['reponse2']); ?><br>
                                <?php echo htmlspecialchars($q['reponse3']); ?><br>
                                <?php echo htmlspecialchars($q['reponse4']); ?>
                            </td>
                            <td><?php echo $q['bonne_reponse']; ?></td>
                            <td><?php echo htmlspecialchars($q['categorie']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $q['id']; ?>" class="btn-icon btn-edit">✏️ Modifier</a>
                                <a href="?delete=<?php echo $q['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Supprimer cette question ?')">🗑️ Supprimer</a>
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