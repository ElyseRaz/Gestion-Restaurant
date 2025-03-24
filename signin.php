<?php 
require_once 'models/User.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation des champs
    if (empty($username) || empty($password) || empty($email) || empty($confirmPassword)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        try {
            $user = new User($username, $password, $email);
            $user->signIn();
            $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur pour duplicate entry
                $error = "Ce nom d'utilisateur ou email existe déjà";
            } else {
                $error = "Une erreur est survenue lors de la création du compte";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./views/css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Inscription</title>
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #000000);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(255, 193, 7, 0.2);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(45deg, #ffc107, #ffdb4d) !important;
            padding: 1.5rem !important;
        }
        .card-header h4 {
            font-size: 1.8rem;
            margin: 0;
            font-weight: 600;
            color: #000;
        }
        .restaurant-name {
            text-align: center;
            color: #ffc107;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            font-style: italic;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 10px rgba(42,82,152,0.2);
        }
        .btn-primary {
            background: linear-gradient(45deg, #ffc107, #ffdb4d);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #000;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
            background: linear-gradient(45deg, #ffdb4d, #ffc107);
        }
        a {
            color: #ffc107;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #ffdb4d;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="restaurant-name">Chez L'Or</h1>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Création de compte</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <br>
                                <a href="login.php" class="alert-link">Se connecter</a>
                            </div>
                        <?php endif; ?>

                        <form action="signin.php" method="post">
                            <div class="mb-4">
                                <label for="username" class="form-label fw-bold">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">Mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <i class="password-toggle bi bi-eye-slash" onclick="togglePassword('password')"></i>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">Confirmer le mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <i class="password-toggle bi bi-eye-slash" onclick="togglePassword('confirm_password')"></i>
                                </div>
                            </div>
                            <div class="mb-4">
                                <button type="submit" class="btn btn-primary w-100">Créer un compte</button>
                            </div>
                        </form>
                        <p class="text-center mb-0">Déjà un compte? <a href="login.php">Se connecter</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.parentElement.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>
</html>
