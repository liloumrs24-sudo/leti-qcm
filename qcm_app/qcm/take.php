<?php



require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ================= SÉCURITÉ SESSION =================
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_SESSION['qcm_questions'])) {
    header('Location: choisir_categories.php');
    exit;
}

// ================= DONNÉES =================
$questions = $_SESSION['qcm_questions'];
$currentIndex = $_SESSION['current_index'] ?? 0;

if ($currentIndex >= count($questions)) {
    header('Location: result.php');
    exit;
}

$currentQuestion = $questions[$currentIndex];
$questionNumber = $currentIndex + 1;

// ================= TEMPS =================
$tempsLimiteTotal = 600; // 10 minutes
$tempsLimiteParQuestion = 60;

$examStart = $_SESSION['exam_start_time'] ?? time();
$tempsEcoule = time() - $examStart;
$tempsRestant = max(0, $tempsLimiteTotal - $tempsEcoule);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question <?= $questionNumber ?>/10 - LETI QCM</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    user-select: none;
}

body {
    font-family: 'Inter', sans-serif;
    background: #0a0a0a;
    color: white;
}

.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    padding: 1rem;
    background: rgba(10,10,10,0.9);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.container {
    max-width: 900px;
    margin: auto;
}

.qcm-container {
    margin: 6rem auto 2rem;
    padding: 2rem;
    background: rgba(20,20,30,0.6);
    border-radius: 20px;
}

.qcm-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.qcm-timer.warning {
    background: red;
}

.option {
    padding: 1rem;
    margin-bottom: 10px;
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    cursor: pointer;
}

.option.selected {
    background: rgba(182,70,95,0.3);
}

.btn {
    padding: 10px 20px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #b6465f;
    color: white;
}

.btn-secondary {
    background: rgba(255,255,255,0.1);
    color: white;
}
</style>
</head>

<body>

<div class="navbar">
    <div class="container">
        <strong>LETI QCM</strong>
    </div>
</div>

<div class="qcm-container">

    <div class="qcm-header">
        <div>Question <?= $questionNumber ?>/10</div>
        <div id="timer">01:00</div>
    </div>

    <div class="question-text">
        <?= htmlspecialchars($currentQuestion['question']) ?>
    </div>

    <div id="options">
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="option" data-value="<?= $i ?>">
                <?= htmlspecialchars($currentQuestion['reponse'.$i]) ?>
            </div>
        <?php endfor; ?>
    </div>

    <div style="margin-top:20px; display:flex; justify-content:space-between;">
        <button class="btn btn-secondary" onclick="skipQuestion()">Passer</button>
        <button class="btn btn-primary" onclick="submitAnswer()">Valider</button>
    </div>
</div>

<form id="answerForm" method="POST" action="process_answer.php">
    <input type="hidden" name="answer" id="selectedAnswer">
</form>

<script>
let selectedValue = null;
let isSubmitting = false;
let quizActive = true;
let timeRemaining = <?= $tempsLimiteParQuestion ?>;

const timerElement = document.getElementById('timer');

// ================= TIMER =================
function updateTimer() {
    if (!quizActive) return;

    if (timeRemaining <= 0) {
        autoSubmit();
        return;
    }

    let m = Math.floor(timeRemaining / 60);
    let s = timeRemaining % 60;

    timerElement.textContent =
        String(m).padStart(2,'0') + ":" + String(s).padStart(2,'0');

    if (timeRemaining <= 10) timerElement.classList.add('warning');

    timeRemaining--;
    setTimeout(updateTimer, 1000);
}
updateTimer();

// ================= OPTIONS =================
document.querySelectorAll('.option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
        selectedValue = opt.dataset.value;
    });
});
function sendAntiCheatEvent(eventName, extraData = null) {
    const formData = new FormData();
    formData.append('event', eventName);
    if (extraData) formData.append('data', JSON.stringify(extraData));
    fetch('api_anticheat.php', {
        method: 'POST',
        body: formData
    }).then(res => {
        if (res.status === 403) {
            alert('Session invalidée pour suspicion de triche.');
            window.location.href = 'quitter.php';
        }
    });
}
// Si le score dépasse 50 pour la première fois, envoyer un mail à l'admin
if ($points > 0 && $score >= 50 && $score - $points < 50) {
    $adminEmail = "admin@leti-qcm.com"; // à configurer
    $subject = "⚠️ Alerte triche - Utilisateur $user_id";
    $message = "L'utilisateur $user_id a atteint un score de suspicion de $score points.\nDernier événement : $event";
    mail($adminEmail, $subject, $message, "From: no-reply@leti-qcm.com");
}

// Exemples d'utilisation (intégrez-les dans vos écouteurs existants) :
document.addEventListener('visibilitychange', () => {
    if (document.hidden) sendAntiCheatEvent('tab_switch');
});
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) sendAntiCheatEvent('fullscreen_exit');
});
document.addEventListener('copy', () => sendAntiCheatEvent('copy_paste'));
document.addEventListener('paste', () => sendAntiCheatEvent('copy_paste'));
// etc.

// ================= FULLSCREEN =================
function enterFullscreen() {
    let el = document.documentElement;
    if (el.requestFullscreen) el.requestFullscreen();
}

// ================= SUBMIT =================
function submitAnswer() {
    if (!selectedValue) {
        alert("Choisis une réponse !");
        return;
    }

    isSubmitting = true;

    document.getElementById('selectedAnswer').value = selectedValue;
    document.getElementById('answerForm').submit();
}

function skipQuestion() {
    isSubmitting = true;
    document.getElementById('selectedAnswer').value = 0;
    document.getElementById('answerForm').submit();
}

function autoSubmit() {
    if (isSubmitting) return;
    isSubmitting = true;
    document.getElementById('selectedAnswer').value = selectedValue || 0;
    document.getElementById('answerForm').submit();
}

// ================= ANTI TRICHE SIMPLE =================
let tabSwitch = 0;

document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        tabSwitch++;
        alert("⚠️ Changement d'onglet détecté ("+tabSwitch+")");

        if (tabSwitch >= 3) {
            window.location.href = "quitter.php";
        }
    }
});

document.addEventListener("contextmenu", e => e.preventDefault());

document.addEventListener("keydown", e => {
    if (e.ctrlKey && ['c','v','x','u'].includes(e.key.toLowerCase())) {
        e.preventDefault();
    }
    if (e.key === "F12") e.preventDefault();
});

// ================= START =================
window.onload = () => {
    enterFullscreen();
};
</script>
<script>
// ========== ANTI-TRICHE COMPLET ==========
let quizActive = true;
let tabSwitches = 0;
let fullscreenExits = 0;
let devtoolsOpen = false;
let sessionId = <?php echo $_SESSION['exam_session_id'] ?? 0; ?>;

// Envoi d'un événement au backend
function sendAntiCheatEvent(eventName, extraData = null) {
    const formData = new FormData();
    formData.append('event', eventName);
    if (extraData) formData.append('data', JSON.stringify(extraData));
    fetch('api_anticheat.php', {
        method: 'POST',
        body: formData
    }).then(res => {
        if (res.status === 403) {
            alert('Session invalidée pour suspicion de triche.');
            window.location.href = 'quitter.php';
        }
    }).catch(err => console.error('Erreur envoi:', err));
}

// 1. Plein écran obligatoire (bouton)
const fsBtn = document.getElementById('forceFullscreen');
if (fsBtn) {
    fsBtn.addEventListener('click', () => {
        document.documentElement.requestFullscreen();
    });
}

// 2. Détection changement d'onglet
document.addEventListener('visibilitychange', () => {
    if (document.hidden && quizActive) {
        tabSwitches++;
        sendAntiCheatEvent('tab_switch', { count: tabSwitches });
        if (tabSwitches >= 3) {
            alert('QCM invalidé : trop de changements d\'onglet');
            window.location.href = 'quitter.php';
        }
    }
});

// 3. Détection sortie plein écran
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && quizActive) {
        fullscreenExits++;
        sendAntiCheatEvent('fullscreen_exit', { count: fullscreenExits });
        if (fullscreenExits >= 3) {
            alert('QCM invalidé : sortie plein écran trop fréquente');
            window.location.href = 'quitter.php';
        } else {
            alert('Remettez le QCM en plein écran !');
            document.documentElement.requestFullscreen();
        }
    }
});

// 4. Blocage clic droit, copier, coller
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('copy', e => {
    e.preventDefault();
    sendAntiCheatEvent('copy_paste');
});
document.addEventListener('paste', e => {
    e.preventDefault();
    sendAntiCheatEvent('copy_paste');
});
document.addEventListener('cut', e => {
    e.preventDefault();
    sendAntiCheatEvent('copy_paste');
});

// 5. Détection DevTools par taille de fenêtre
setInterval(() => {
    const threshold = 160;
    const nowOpen = (window.outerWidth - window.innerWidth > threshold) ||
                    (window.outerHeight - window.innerHeight > threshold);
    if (nowOpen && !devtoolsOpen) {
        devtoolsOpen = true;
        sendAntiCheatEvent('devtools');
    } else if (!nowOpen && devtoolsOpen) {
        devtoolsOpen = false;
    }
}, 1000);

// 6. Blocage des raccourcis clavier (Ctrl+C, Ctrl+V, F12...)
document.addEventListener('keydown', e => {
    if (e.ctrlKey && ['c','v','x','u'].includes(e.key.toLowerCase())) {
        e.preventDefault();
        sendAntiCheatEvent('shortcut_blocked');
    }
    if (e.key === 'F12') {
        e.preventDefault();
        sendAntiCheatEvent('devtools');
    }
    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        sendAntiCheatEvent('devtools');
    }
});

// 7. Heartbeat (toutes les 10 secondes)
setInterval(() => {
    fetch('heartbeat.php');
}, 10000);

console.log('Anti-triche actif');
</script>
</body>
</html>