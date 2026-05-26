document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('copy', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('cut', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('paste', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('selectstart', function(e) {
    e.preventDefault();
    return false;
});

function showMessage(message, type) {
    const msgDiv = document.getElementById('message');
    if (msgDiv) {
        msgDiv.textContent = message;
        msgDiv.className = `message ${type}`;
        setTimeout(() => {
            msgDiv.style.display = 'none';
        }, 5000);
    }
}