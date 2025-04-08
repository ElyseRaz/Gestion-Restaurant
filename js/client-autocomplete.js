document.addEventListener('DOMContentLoaded', function() {
    const clientInput = document.getElementById('nomcli');
    const dateInput = document.getElementById('datecom'); // Correction de l'ID
    const tableInput = document.getElementById('idtable');
    const suggestionBox = document.createElement('div');
    
    // Ajout des styles pour la boîte de suggestions
    suggestionBox.setAttribute('class', 'suggestion-box');
    suggestionBox.style.position = 'absolute';
    suggestionBox.style.width = clientInput.offsetWidth + 'px';
    suggestionBox.style.backgroundColor = '#fff';
    suggestionBox.style.border = '1px solid #ddd';
    suggestionBox.style.maxHeight = '200px';
    suggestionBox.style.overflowY = 'auto';
    suggestionBox.style.zIndex = '1000';
    
    // Insertion de la boîte de suggestions après l'input
    clientInput.parentNode.style.position = 'relative';
    clientInput.parentNode.appendChild(suggestionBox);
    
    clientInput.addEventListener('input', debounce(function(e) {
        const searchTerm = this.value;
        
        if (searchTerm.length < 2) {
            suggestionBox.style.display = 'none';
            return;
        }
        
        // Correction du chemin de l'API
        fetch(`../ajax/search_client.php?term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                suggestionBox.innerHTML = '';
                if (data.length > 0) {
                    suggestionBox.style.display = 'block';
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.innerHTML = `${item.NOMCLI} - Table ${item.DESIGNATION}`;
                        div.style.padding = '8px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #eee';
                        
                        div.addEventListener('mouseover', function() {
                            this.style.backgroundColor = '#f0f0f0';
                        });
                        
                        div.addEventListener('mouseout', function() {
                            this.style.backgroundColor = '#fff';
                        });
                        
                        div.addEventListener('click', function() {
                            clientInput.value = item.NOMCLI;
                            if (item.DATERESERVE) {
                                const date = new Date(item.DATERESERVE);
                                dateInput.value = date.toISOString().split('T')[0];
                            }
                            tableInput.value = item.NUMTABLE;
                            suggestionBox.style.display = 'none';
                        });
                        
                        suggestionBox.appendChild(div);
                    });
                } else {
                    suggestionBox.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                suggestionBox.style.display = 'none';
            });
    }, 300));
    
    // Fonction debounce pour limiter les appels à l'API
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Fermer les suggestions en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (!clientInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
    });
});
