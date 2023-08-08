import { Fournisseur, Transaction } from "./types.js";
import { isTel, isNumCompte } from "./validator.js";
import { numero, nomComplet, port, fournisseurSelect, transactionSelect, montant, numeroDes, nomCompletDes, send, error, newNomComplet, newTel, adNewClient, numeroCompteAdd, nomCompletAdd, adNewCompte, messageCompte, messageClient, fournisseurSelect2, infoG, modalG, des, code, codeT, btnTransaction, list_transaction, list_compte, btnCompte, bloquer, colfour, annulerTransa, selectFiltre, mon, infoTransaction, transactionDetail, montantRecu } from "./dom.js";
import { chargerSelect, viderChamps, message, createButton } from "./functions.js";
const fournisseurs = Object.values(Fournisseur);
fournisseurs.push("WR");
chargerSelect(fournisseurSelect, fournisseurs);
chargerSelect(fournisseurSelect2, fournisseurs);
chargerSelect(transactionSelect, Object.values(Transaction));
let id_expediteur = null;
let id_destinataire = null;
let mont = null;
let typ = null;
let nomComplete = null;
let telephone = null;
montantRecu.style.display = "block";
fournisseurSelect2.addEventListener("change", () => {
    montant.value = "";
    montantRecu.value = "";
    if (fournisseurSelect2.value === "OM") {
        colfour.forEach(element => {
            element.setAttribute("class", "Orange_Money");
        });
    }
    else if (fournisseurSelect2.value === "WR") {
        colfour.forEach(element => {
            element.setAttribute("class", "Wari");
        });
    }
    else if (fournisseurSelect2.value === "WV") {
        colfour.forEach(element => {
            element.setAttribute("class", "Wave");
        });
    }
    else if (fournisseurSelect2.value === "CB") {
        colfour.forEach(element => {
            element.setAttribute("class", "CB");
        });
    }
    else {
        colfour.forEach(element => {
            element.removeAttribute("class");
        });
    }
});
infoG.style.display = "none";
transactionSelect.addEventListener("change", () => {
    if (transactionSelect.value === "Retrait") {
        des.style.display = "none";
    }
    else {
        des.style.display = "block";
    }
    if (transactionSelect.value === "Retrait_Avec_Code") {
        code.style.display = "block";
        des.style.display = "none";
        mon.style.display = "none";
    }
    else {
        code.style.display = "none";
        mon.style.display = "block";
    }
    if (transactionSelect.value != "saisir") {
        send.removeAttribute("disabled");
    }
    else {
        send.setAttribute("disabled", "true");
        send.disabled = true;
    }
});
numero.addEventListener("change", () => {
    if (!(isNumCompte(numero.value) || isTel(numero.value))) {
        message("Numero inexistant dans la base de donnée", error);
        viderChamps([numero]);
    }
    nomComplet.value = "";
    fetch(port + "/clients/" + numero.value)
        .then(response => response.json())
        .then(dataResponse => {
        if (dataResponse.data) {
            nomComplet.value = dataResponse.data.nomComplet;
            //  console.log(dataResponse.data.id);
            id_expediteur = dataResponse.data.id;
            infoG.style.display = "block";
        }
        else {
            console.log(dataResponse);
            alert(dataResponse.message);
        }
    });
});
//destinateur
numeroDes.addEventListener("change", () => {
    if (!(isNumCompte(numeroDes.value) || isTel(numeroDes.value))) {
        message("Numero inexistant dans la base de donnée", error);
        viderChamps([numeroDes]);
    }
    nomCompletDes.value = "";
    fetch(port + "/clients/" + numeroDes.value)
        .then(response => response.json())
        .then(dataResponse => {
        if (dataResponse.data) {
            nomCompletDes.value = dataResponse.data.nomComplet;
            id_destinataire = dataResponse.data.id;
            console.log(dataResponse.data);
        }
        else {
            console.log(dataResponse);
            message(dataResponse.message, error);
        }
    });
});
//sendData
send.addEventListener("click", () => {
    if (numero.value == "") {
        message("Numero numero Obligatoire ", error);
    }
    if (!(isNumCompte(numero.value) || isTel(numero.value))) {
        return message("Numero inexistant dans la base de donnée", error);
    }
    console.log(id_expediteur);
    console.log(id_destinataire);
    mont = montant.value;
    typ = transactionSelect.value;
    console.log(mont);
    console.log(typ);
    fetch(`http://127.0.0.1:8000/api/transactions/${typ}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            montant: mont,
            type: typ,
            expediteur_id: id_expediteur,
            destinateur_id: id_destinataire,
            fournisseur: fournisseurSelect2.value,
            code: codeT.value
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        error.innerHTML = data.message;
        setTimeout(() => {
            error.innerHTML = "";
        }, 15000);
        viderChamps([montant, nomComplet, nomCompletDes, numero, numeroDes, codeT]);
    }))).catch((error) => {
        error.innerHTML = error.message;
    });
});
//addnewClient
nomComplete = newNomComplet.value,
    telephone = newTel.value;
adNewClient.addEventListener('click', () => {
    console.log(newNomComplet.value);
    console.log(newTel.value);
    fetch('http://127.0.0.1:8000/api/Client/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            nomComplete: newNomComplet.value,
            telephone: newTel.value
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        message(data.message, messageClient);
        viderChamps([newNomComplet, newTel]);
    }))).catch((error) => {
        messageClient.innerHTML = error.message;
    });
});
//addCompte
numeroCompteAdd.addEventListener("change", () => {
    if (!(isNumCompte(numeroCompteAdd.value) || isTel(numeroCompteAdd.value))) {
        message("Numero de compte ou telphone invalide", messageCompte);
    }
    nomComplet.value = "";
    fetch(port + "/clients/" + numeroCompteAdd.value)
        .then(response => response.json())
        .then(dataResponse => {
        if (dataResponse.data) {
            nomCompletAdd.value = dataResponse.data.nomComplet;
            id_expediteur = dataResponse.data.id;
        }
        else {
            console.log(dataResponse);
            alert(dataResponse.message);
        }
    });
});
adNewCompte.addEventListener("click", () => {
    fetch('http://127.0.0.1:8000/api/Compte/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            id: id_expediteur,
            fournisseur: fournisseurSelect.value
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        message(data.message, messageCompte);
        viderChamps([numeroCompteAdd, nomCompletAdd]);
    }))).catch((error) => {
        messageCompte.innerHTML = error.message;
    });
});
//modal
infoG.addEventListener('click', () => {
    modalG.classList.toggle("ferme");
});
btnTransaction.addEventListener('click', () => {
    fetch('http://127.0.0.1:8000/api/transactions/historique', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            expediteur_id: id_expediteur,
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        selectFiltre.addEventListener("click", () => {
            if (selectFiltre.value == "montant") {
                data.sort((a, b) => a.montant - b.montant);
                list_transaction.innerHTML = "";
                for (const iterator of data) {
                    let div = document.createElement("tr");
                    for (const key in iterator) {
                        let td = document.createElement("td");
                        console.log(iterator[key]);
                        td.innerHTML = iterator[key];
                        div.append(td);
                    }
                    createButton("Annuler", "btn btn-danger", "transAnnuler", iterator, div);
                    list_transaction.append(div);
                }
            }
            else if (selectFiltre.value == "date") {
                data.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
                list_transaction.innerHTML = "";
                for (const iterator of data) {
                    let div = document.createElement("tr");
                    for (const key in iterator) {
                        let td = document.createElement("td");
                        console.log(iterator[key]);
                        td.innerHTML = iterator[key];
                        div.append(td);
                    }
                    createButton("Annuler", "btn btn-danger", "transAnnuler", iterator, div);
                    list_transaction.append(div);
                }
            }
        });
        list_transaction.innerHTML = "";
        for (const iterator of data) {
            let div = document.createElement("tr");
            for (const key in iterator) {
                let td = document.createElement("td");
                console.log(iterator[key]);
                td.innerHTML = iterator[key];
                div.append(td);
            }
            createButton("Annuler", "btn btn-danger", "transAnnuler", iterator, div);
            list_transaction.append(div);
        }
    })));
    // annulerTransa.addEventListener("click", () => {
    //     console.log("test");
    // })
});
annulerTransa.forEach(element => {
    element.addEventListener("click", () => {
        console.log("test");
    });
});
btnCompte.addEventListener('click', () => {
    fetch('http://127.0.0.1:8000/api/Compte', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            expediteur_id: id_expediteur,
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        list_compte.innerHTML = "";
        for (const iterator of data) {
            let div = document.createElement("tr");
            if (iterator.etat == 0) {
                iterator.etat = "false";
                createButton("Bloquer", "btn btn-danger", "bloquer", iterator, div);
            }
            else if (iterator.etat == 1) {
                iterator.etat = "true";
                createButton("Debloquer", "btn btn-success", "debloquer", iterator, div);
            }
            if (iterator.EnFonction == 0) {
                iterator.EnFonction = "false";
                createButton("Fermer", "btn btn-danger", "fermer", iterator, div);
            }
            else if (iterator.EnFonction == 1) {
                iterator.EnFonction = "true";
                createButton("Reouvrir", "btn btn-success", "rouvrir", iterator, div);
            }
            for (const key in iterator) {
                let td = document.createElement("td");
                td.innerHTML = iterator[key];
                div.append(td);
            }
            list_compte.append(div);
        }
    })));
    console.log(bloquer);
});
bloquer.forEach(element => {
    element.addEventListener("click", () => {
        console.log("test");
    });
});
infoTransaction.style.display = "none";
codeT.addEventListener('change', () => {
    console.log(codeT.value);
    if (codeT.value != "") {
        infoTransaction.style.display = "block";
    }
    else {
        infoTransaction.style.display = "none";
    }
    fetch('http://127.0.0.1:8000/api/transactions/getMontant', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            code: codeT.value
        })
    }).then((response => response.json().then(data => {
        console.log(data);
        transactionDetail.innerHTML = "";
        for (const iterator of data) {
            let div = document.createElement("tr");
            for (const key in iterator) {
                let td = document.createElement("td");
                console.log(iterator[key]);
                td.innerHTML = iterator[key];
                div.append(td);
            }
            transactionDetail.append(div);
        }
    }))).catch((error) => {
        messageCompte.innerHTML = error.message;
    });
});
montant.addEventListener('input', () => {
    let arecevoir;
    let some = montant.value;
    console.log(montant.value);
    if (fournisseurSelect2.value === "OM") {
        arecevoir = some - (some * 0.01);
    }
    else if (fournisseurSelect2.value === "WR") {
        arecevoir = some - (some * 0.02);
    }
    else if (fournisseurSelect2.value === "WV") {
        arecevoir = some - (some * 0.01);
    }
    else if (fournisseurSelect2.value === "CB") {
        arecevoir = some - (some * 0.05);
    }
    else {
        arecevoir = 0;
    }
    montantRecu.value = arecevoir;
});
montantRecu.addEventListener('input', () => {
    let arecevoir;
    let some = montantRecu.value;
    console.log(montant.value);
    if (fournisseurSelect2.value === "OM") {
        arecevoir = +some + (+some * 0.01);
    }
    else if (fournisseurSelect2.value === "WR") {
        arecevoir = +some + (+some * 0.02);
    }
    else if (fournisseurSelect2.value === "WV") {
        arecevoir = +some + (+some * 0.01);
    }
    else if (fournisseurSelect2.value === "CB") {
        arecevoir = +some + (+some * 0.05);
    }
    else {
        arecevoir = 0;
    }
    montant.value = arecevoir;
});
