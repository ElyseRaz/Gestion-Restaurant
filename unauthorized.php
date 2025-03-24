<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès non autorisé</title>
    <link href="views/css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #1a1c1e 0%, #2d3436 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #e4e6eb;
        }
        .alert {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            background: #2c3138;
            padding: 2rem;
        }
        .alert h4 {
            color: #ff4757;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: #4a69bd;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #5773c0;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74,105,189,0.4);
        }
        #countdown {
            font-weight: bold;
            color: #ff6b81;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        p {
            color: #b2bec3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert fade-in">
            <h4><i class="fas fa-exclamation-triangle text-warning"></i> Accès non autorisé</h4>
            <p class="mb-3">Vous devez vous connecter pour accéder à cette page.</p>
            <p class="mb-4">Vous serez redirigé vers la page de connexion dans <span id="countdown">5</span> secondes...</p>
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter maintenant
            </a>
        </div>
    </div>
    <script>
        let seconds = 5;
        const countdown = setInterval(() => {
            seconds--;
            document.getElementById('countdown').textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>
