function handleTypeCommandeChange() {
    const typeCommande = document.getElementById('typecom').value;
    const idTableInput = document.getElementById('idtable');

    if (typeCommande === 'A emporter') {
        idTableInput.value = ''; // Vide le champ
        idTableInput.disabled = true; // Désactive le champ
        idTableInput.removeAttribute('required'); // Supprime l'attribut required
    } else {
        idTableInput.disabled = false; // Réactive le champ
        idTableInput.setAttribute('required', 'required'); // Ajoute l'attribut required
    }
}

document.addEventListener('DOMContentLoaded', () => {
    function toggleQuantityInput(checkbox, idplat) {
        const quantityInput = document.getElementById('quantite' + idplat);
        if (checkbox.checked) {
            quantityInput.style.display = 'block'; // Affiche le champ de quantité
        } else {
            quantityInput.style.display = 'none'; // Masque le champ de quantité
            quantityInput.value = '1'; // Réinitialise la valeur du champ
        }
    }

    // Attachez la fonction à l'objet global
    window.toggleQuantityInput = toggleQuantityInput;

    // Fonction pour récupérer le prix unitaire et calculer le prix total
    function updateTotalPrice() {
        let total = 0;
        document.querySelectorAll('.form-check-input').forEach((checkbox) => {
            const idplat = checkbox.value;
            const quantityInput = document.getElementById('quantite' + idplat);
            if (checkbox.checked && quantityInput) {
                const quantity = parseInt(quantityInput.value || 0);
                const pu = parseFloat(checkbox.dataset.pu); // Récupère le prix unitaire depuis l'attribut data-pu
                if (!isNaN(quantity) && !isNaN(pu)) {
                    total += pu * quantity;
                }
            }
        });
        document.getElementById('totalPrice').value = total.toFixed(2); // Affiche le total avec 2 décimales
    }

    // Ajoutez un écouteur pour les cases à cocher et les champs de quantité
    document.querySelectorAll('.form-check-input').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const idplat = checkbox.value;
            const quantityInput = document.getElementById('quantite' + idplat);
            if (checkbox.checked) {
                quantityInput.style.display = 'block'; // Affiche le champ de quantité
            } else {
                quantityInput.style.display = 'none'; // Masque le champ de quantité
                quantityInput.value = '1'; // Réinitialise la quantité
            }
            updateTotalPrice();
        });
    });

    document.querySelectorAll('input[type="number"]').forEach((input) => {
        input.addEventListener('input', updateTotalPrice);
    });
});

