const zoneDepotimg = document.getElementById('depot-img');
const inputFichierimg = document.getElementById('input-img');
const texteImg = document.getElementById('texte-img');

zoneDepotimg.addEventListener('click', () => {
    inputFichierimg.click();
});

inputFichierimg.addEventListener('change', function() {
    gererFichiers(this.files);
});

zoneDepotimg.addEventListener('dragover', (e) => {
    e.preventDefault();
    zoneDepotimg.classList.add('survol');
});

zoneDepotimg.addEventListener('dragleave', () => {
    zoneDepotimg.classList.remove('survol');
});

zoneDepotimg.addEventListener('drop', (e) => {
    e.preventDefault();
    zoneDepotimg.classList.remove('survol');
    gererFichiers(e.dataTransfer.files);
});

function gererFichiers(fichiers) {
    if (fichiers.length > 0) {
        const fichier = fichiers[0];
        
        if (fichier.type.startsWith('image/')) {
            // 1. On assigne virtuellement le fichier glissé à l'input caché pour le formulaire PHP
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fichier);
            inputFichierimg.files = dataTransfer.files;

            // 2. On gère l'aperçu visuel
            const lecteur = new FileReader();
            
            lecteur.onload = (e) => {
                // On met l'image en fond
                zoneDepotimg.style.backgroundImage = `url('${e.target.result}')`;
                
                // NOUVEAU : On centre l'image et on l'adapte
                zoneDepotimg.style.backgroundPosition = 'center';
                zoneDepotimg.style.backgroundRepeat = 'no-repeat';
                zoneDepotimg.style.backgroundSize = 'contain'; 

                // On cache le texte "Glissez et déposez..."
                if (texteImg) texteImg.style.display = 'none';
                // On enlève la bordure pointillée
                zoneDepotimg.style.border = 'none'; 
            };
            
            lecteur.readAsDataURL(fichier);
        } else {
            alert("Veuillez sélectionner uniquement une image.");
        }
    }
}