
const divConnexion = document.getElementById("connexion");
const divCompte = document.getElementById("compte");

const lienVersCompte = document.getElementById("lcompte");
const lienVersConnexion = document.getElementById("lconnexion");



lienVersCompte.addEventListener('click', function(event){
    event.preventDefault();
    

    divConnexion.classList.remove("visible");
    divConnexion.classList.add("invisible");
    
   
    divCompte.classList.remove("invisible");
    divCompte.classList.add("visible");
});


lienVersConnexion.addEventListener('click', function(event){
    event.preventDefault();


    divCompte.classList.remove("visible");
    divCompte.classList.add("invisible");



    divConnexion.classList.remove("invisible");
    divConnexion.classList.add("visible");
});

const copyloginmdp = document.getElementById("login_mdp");
copyloginmdp.addEventListener('paste',function(event){
    event.preventDefault();
    alert("Merci de taper le mot de passe manuellement pour confirmation.");
});

const copynmdp = document.getElementById("new_mdp");
copynmdp.addEventListener('paste',function(event){
    event.preventDefault();
    alert("Merci de taper le mot de passe manuellement pour confirmation.");
});

const copynewvmdp = document.getElementById("new_vmdp");
copynewvmdp.addEventListener('paste',function(event){
    event.preventDefault();
    alert("Merci de taper le mot de passe manuellement pour confirmation.");
});

const inscription = document.getElementById("inscription");
inscription.addEventListener('submit',function(event){
    if(copynmdp.value!==copynewvmdp.value){
        event.preventDefault();
        alert("Codes différents");
        copynewvmdp.value=" ";
        copynewvmdp.focus();

    }
});


function configurerValidation(idInput, idMessage) {
    
    const inputElement = document.getElementById(idInput);
    const msgElement = document.getElementById(idMessage);

  
    if (!inputElement || !msgElement) return;

    inputElement.addEventListener("input", function() {
        const valeur = inputElement.value;

    
        const contientLettre = /[a-zA-Z]/.test(valeur);
        const contientChiffre = /[0-9]/.test(valeur);
        const longueurSuffisante = valeur.length >= 8;

        
        if (valeur === "") {
            msgElement.style.display = "none";
            inputElement.style.border = "1px solid #ddd";
            return;
        }

    
        if (!longueurSuffisante || !contientLettre || !contientChiffre) {
            msgElement.textContent = "⚠️ Il faut 8 caractères, des lettres et des chiffres.";
            msgElement.style.color = "red";
            msgElement.style.display = "block";
            inputElement.style.border = "1px solid red";
        } 
        
        else {
            msgElement.textContent = "Mot de passe sécurisé ✅";
            msgElement.style.color = "green";
            msgElement.style.display = "block";
            inputElement.style.border = "1px solid green";
        }
    });
}





configurerValidation("new_mdp", "msg-new-mdp");


configurerValidation("new_vmdp", "msg-new-vmdp");

configurerValidation("login_mdp","msg-login-mdp");