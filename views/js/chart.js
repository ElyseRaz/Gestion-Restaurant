document.addEventListener('DOMContentLoaded', function() {
    try {
        const labels = monthlyData.map(item => {
            const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                          'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            return months[item.mois - 1];
        });

        const data = monthlyData.map(item => parseInt(item.total));

        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chiffre d\'affaires mensuel (Ar)',
                    data: data,
                    backgroundColor: '#0d6efd',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 5,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution du chiffre d\'affaires (6 derniers mois)',
                        font: { size: 16 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString() + ' Ar'
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('error-message').textContent = 
            `Erreur de chargement des données: ${error.message}`;
    }
});
