
function showFlash(message, type = 'success') {
    const flash = document.getElementById('flash-message');
    if (!flash) return;
    flash.textContent = message;
    flash.style.display = 'block';
    flash.style.background = (type === 'error') ? '#f44336' : '#4CAF50';
    flash.style.color = '#fff';
    flash.style.boxShadow = '0 2px 6px rgba(0,0,0,0.25)';

    setTimeout(() => {
        flash.style.display = 'none';
    }, 2500);
}

function updateFavoriteCounter(delta) {
    const counterEl = document.getElementById('favorite-count');
    if (!counterEl) return;

    const current = parseInt(counterEl.textContent, 10) || 0;
    counterEl.textContent = Math.max(0, current + delta);
}

function toggleHeart(button, capsuleId) {
    button.classList.toggle('is-active');

    const formData = new FormData();
    formData.append('capsule_id', capsuleId);

    fetch('../php/like_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            button.classList.toggle('is-active');
            showFlash('Erreur : ' + data.message, 'error');
            return;
        }

        let actionText = data.action === 'added' ? 'Ajouté aux favoris' : 'Supprimé des favoris';
        showFlash(actionText);
        if (data.action === 'added') updateFavoriteCounter(1);
        if (data.action === 'removed') updateFavoriteCounter(-1);

        console.log('Action : ' + data.action);
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.classList.toggle('is-active');
        showFlash('Erreur réseau, réessayez.', 'error');
    });
}

function toggleBookmark(element, capsuleId) {
    element.classList.toggle('is-active');

    const formData = new FormData();
    formData.append('capsule_id', capsuleId);

    fetch('../php/like_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            element.classList.toggle('is-active');
            showFlash('Erreur : ' + data.message, 'error');
            return;
        }

        let actionText = data.action === 'added' ? 'Ajouté aux favoris' : 'Supprimé des favoris';
        showFlash(actionText);
        if (data.action === 'added') updateFavoriteCounter(1);
        if (data.action === 'removed') updateFavoriteCounter(-1);

        console.log('Favoris : ' + data.action);
    })
    .catch(error => {
        console.error('Erreur:', error);
        element.classList.toggle('is-active');
        showFlash('Erreur réseau, réessayez.', 'error');
    });
}

function removeFavorite(button, capsuleId) {
    const li = button.closest('li');
    const formData = new FormData();
    formData.append('capsule_id', capsuleId);

    fetch('../php/like_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            showFlash('Erreur : ' + data.message, 'error');
            return;
        }

        if (data.action === 'removed') {
            if (li) li.remove();
            updateFavoriteCounter(-1);
            showFlash('Capsule retirée des favoris');

            const total = parseInt(document.getElementById('favorite-count').textContent, 10) || 0;
            if (total === 0) {
                const navLinks = document.getElementById('nav-links');
                if (navLinks) {
                    navLinks.innerHTML = '<p style="text-align: center; width: 100%;">Aucun favori pour le moment. Ajoutez des capsules en favoris depuis le menu.</p>';
                }
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showFlash('Erreur réseau, réessayez.', 'error');
    });
}
