const zoneDepotimg = document.getElementById('depot-img');
const inputFichierimg = document.getElementById('input-img');


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
            const lecteur = new FileReader();
            
            lecteur.onload = (e) => {
                zoneDepotimg.style.backgroundImage = `url('${e.target.result}')`;
                zoneDepotimg.classList.add('texte-image');
            };
            
            lecteur.readAsDataURL(fichier);
        } else {
            alert("Veuillez sélectionner uniquement une image.");
        }
    }
}