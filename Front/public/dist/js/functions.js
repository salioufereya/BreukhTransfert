import { messageCompte, clientEtat, clientTransa } from "./dom.js";
export function chargerSelect(select, tableau) {
    tableau.forEach((element) => {
        let option = document.createElement("option");
        option.innerHTML = element;
        select.appendChild(option);
    });
}
export function viderChamps(tab) {
    tab.forEach(element => {
        element.value = "";
    });
}
export function fetche(url, body, method) {
    return fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ body })
    });
}
export function message(message, div) {
    div.innerHTML = message;
    setTimeout(() => {
        div.innerHTML = "";
    }, 15000);
}
export function createButton(texte, classeBootstrap, attr, element, div) {
    let ted = document.createElement("td");
    let bouton = document.createElement("button");
    bouton.setAttribute("identifiantCompte", element.id);
    bouton.setAttribute("class", `${attr} ${classeBootstrap}`);
    bouton.innerHTML = texte;
    ted.append(bouton);
    div.append(ted);
    bouton.addEventListener('click', () => {
        console.log(element.typeCompte);
        if (bouton.innerHTML === "Annuler") {
            fetch(`http://127.0.0.1:8000/api/Compte/${bouton.innerHTML} `, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id: element.id,
                    expediteur: element.expediteur
                })
            }).then((response => response.json().then(data => {
                console.log(data);
                message(data.message, clientTransa);
            }))).catch((error) => {
                messageCompte.innerHTML = error.message;
            });
        }
        else {
            fetch(`http://127.0.0.1:8000/api/User/${bouton.innerHTML} `, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id: element.id,
                    fournisseur: element.typeCompte
                })
            }).then((response => response.json().then(data => {
                console.log(data);
                message(data.message, clientEtat);
            }))).catch((error) => {
                messageCompte.innerHTML = error.message;
            });
        }
    });
}
export function afficherTransaction(data) {
    for (const iterator of data) {
        let div = document.createElement("tr");
        for (const key in iterator) {
            let td = document.createElement("td");
            console.log(iterator[key]);
            td.innerHTML = iterator[key];
            div.append(td);
        }
    }
}
