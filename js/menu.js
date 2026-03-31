
function toggleHeart(button, capsuleId) {
    // 1. Effet visuel immédiat pour l'utilisateur
    button.classList.toggle('active');

    // 2. Envoi des données au serveur en arrière-plan
    const formData = new FormData();
    formData.append('capsule_id', capsuleId);

    fetch('../php/like_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            // Si erreur (ex: déconnecté), on annule l'effet visuel
            button.classList.remove('active');
            alert("Erreur : " + data.message);
        }
        console.log("Action : " + data.action);
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.classList.remove('active');
    });
}

function toggleBookmark(element) {
  element.classList.toggle('is-active');
}