<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
    <!-- Ajout des liens vers Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .top-bar {
            background-color: #343a40;
            color: white;
            padding: 10px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1050;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .top-bar .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .side-bar {
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 60px;
            left: 0;
            width: 250px;
            height: calc(100% - 60px);
            padding: 20px;
            overflow-y: auto;
        }

        .side-bar .menu a {
            display: flex;
            align-items: center;
            color: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .side-bar .menu a:hover {
            background-color: #495057;
        }

        .side-bar .menu a .menu-icon {
            font-size: 20px;
            margin-right: 15px;
        }

        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .credentials span {
            font-weight: bold;
            color: #343a40;
        }
    </style>
</head>
<body>
    <!-- Barre horizontale supérieure -->
    <header class="top-bar d-flex align-items-center">
        <div class="logo d-flex align-items-center">
            <img src="images_pages/logo.png" alt="Logo">
            <h1 class="h5 mb-0">CampusFlow</h1>
        </div>
    </header>

    <!-- Barre latérale gauche -->
    <aside class="side-bar">
        <div class="text-center mb-4">
            <img src="images_pages/international.png" alt="Admin" class="rounded-circle" width="60" height="60">
            <h6 class="mt-2">Administrateur</h6>
        </div>
        <hr class="bg-light">
        <ul class="menu list-unstyled">
            <li><a href="#"><i class="fas fa-user-graduate menu-icon"></i> Etudiants</a></li>
            <li><a href="#"><i class="fas fa-chalkboard-teacher menu-icon"></i> Professeurs</a></li>
            <li><a href="#"><i class="fas fa-wallet menu-icon"></i> Finances</a></li>
            <li><a href="#"><i class="fas fa-chart-bar menu-icon"></i> Graphiques</a></li>
            <li><a href="#"><i class="fas fa-cog menu-icon"></i> Paramètres</a></li>
            <li><a href="#"><i class="fas fa-sign-out-alt menu-icon"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="container">
            <div class="card mx-auto p-4" style="max-width: 400px;">
                <h3 class="text-center">Informations d'Authentification</h3>
                <div class="credentials text-center mt-3">
                    <p><span>Login :</span> admin</p>
                    <p><span>Mot de passe :</span> admin1234</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
