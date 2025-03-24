<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="css/styles/bootstrap5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&display=swap" rel="stylesheet">
    <style>
        .navbar-brand {
            font-family: 'Roboto Flex', sans-serif;
            font-style: italic;
            font-weight: 600;
            color: #ffc107 !important;
        }
        .nav-link:hover {
            color: #ffc107 !important;
            transition: color 0.3s ease;
        }
        .nav-link.active {
            color: #ffc107 !important;
            border-bottom: 2px solid #ffc107;
        }
        .btn-logout {
            background-color: #212529 !important;
            color: #ffdb4d !important;
            font-weight: bold !important;
            border-radius: 5px !important;
            padding: 8px 20px !important;
            border: 1px solid #ffdb4d !important;
            border-color: #ffdb4d !important;
        }
        .btn-logout:hover {
            background-color: #ffdb4d !important;
            color: #000 !important;
        }
    </style>
    <script>
        function confirmLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</head>
<body class="">
    <?php 
    function isActive($page) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        
        // Pages principales et leurs pages associées
        $pageGroups = [
            'TablePage.php' => ['UpdateTable.php', 'AddTable.php'],
            'MenuPage.php' => ['AddMenu.php', 'EditMenu.php'],
            'CommandePage.php' => ['EditCommand.php', 'AddCommand.php'],
            'ReservationPage.php' => ['EditReservation.php', 'AddReservation.php']
        ];

        // Vérifier si la page courante est la page principale
        if ($currentPage === $page) {
            return 'active';
        }

        // Vérifier si la page courante est une page associée
        if (isset($pageGroups[$page]) && in_array($currentPage, $pageGroups[$page])) {
            return 'active';
        }

        return '';
    }
    ?>
    <!--barre de navigation-->
<nav class="navbar navbar-expand-lg navbar-light bg-primary px-5 py-3 mb-3 navbar-dark bg-dark">
  <div class="container-fluid ">
    <a class="navbar-brand" href="Dashboard.php"><span>Chez L'Or</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"> 
          <a class="nav-link text-white <?php echo isActive('Dashboard.php'); ?>" href="Dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?php echo isActive('TablePage.php'); ?>" href="TablePage.php">Tables</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?php echo isActive('MenuPage.php'); ?>" href="MenuPage.php">Menu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?php echo isActive('CommandePage.php'); ?>" href="CommandePage.php">Commande</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white <?php echo isActive('ReservationPage.php'); ?>" href="ReservationPage.php">Reservation</a>
        </li>
        <li class="nav-item ms-3">
          <a class="nav-link btn-logout" href="javascript:void(0)" onclick="confirmLogout()">Déconnexion</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
    
    <!--fin de la barre de navigation-->
    
    <script src="/views/css/styles/bootstrap5.3.2/js/bootstrap.min.js"></script>
</body>
</html>