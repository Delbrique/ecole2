<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .top-bar {
            background: linear-gradient(135deg, #4158d0 0%, #c850c0 100%);
            color: white;
            width: 100%;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            height: 60px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .side-bar {
            background-color: #fff;
            color: #333;
            width: 280px;
            padding: 20px 10px;
            position: fixed;
            top: 0;
            bottom: 0;
            height: 100vh;
            z-index: 1001;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .admin-section {
            text-align: center;
            margin: 20px 0 30px 0;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        .admin-section .admin-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 3px;
            border: 2px solid #4158d0;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu li {
            margin: 5px 0;
        }

        .menu a {
            text-decoration: none;
            color: #555;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .menu a:hover, .menu a.active {
            background-color: #f0f2ff;
            color: #4158d0;
            transform: translateX(5px);
        }

        .menu i {
            margin-right: 10px;
            font-size: 1.2em;
            width: 25px;
            text-align: center;
        }

        .dropdown-menu {
            list-style: none;
            padding-left: 35px;
            display: none;
            margin-top: 5px;
        }

        .dropdown-menu a {
            padding: 8px 15px;
            font-size: 0.9em;
            color: #666;
        }

        .main-content {
            margin-left: 300px;
            margin-top: 80px;
            padding: 30px;
            flex-grow: 1;
        }

        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4158d0 0%, #c850c0 100%);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            box-shadow: 0 4px 15px rgba(65,88,208,0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(65,88,208,0.4);
        }

        .menu-icon-custom {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <aside class="side-bar">
        <div class="admin-section">
            <img src="images_pages/diplome.png" alt="Admin" class="admin-icon">
            <p class="admin-name fw-bold mt-3 mb-0">Administrateur</p>
        </div>
        <ul class="menu">
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-user-graduate text-primary"></i> Étudiant
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EtudiantListe.php"><i class="fas fa-list text-info"></i> Liste des Étudiants</a></li>
                    <li><a href="ajouter_etudiant.php"><i class="fas fa-user-plus text-success"></i> Ajouter un étudiant</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-chalkboard-teacher text-warning"></i> Professeurs
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ProfesseurListe.php"><i class="fas fa-list text-info"></i> Liste des Professeurs</a></li>
                    <li><a href="ajouter_professeur.php"><i class="fas fa-user-plus text-success"></i> Ajouter un Professeur</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-book text-danger"></i> Matières
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ListeMatiere.php"><i class="fas fa-list text-info"></i> Liste des Matières</a></li>
                    <li><a href="ajouter_matiere.php"><i class="fas fa-plus-circle text-success"></i> Ajouter une Attribution</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-coins text-warning"></i> Finances
                </a>
                <ul class="dropdown-menu">
                    <li><a href="VersementListe.php"><i class="fas fa-list text-info"></i> Liste des versements</a></li>
                    <li><a href="versement.php"><i class="fas fa-money-bill-wave text-success"></i> Effectuer un versement</a></li>
                </ul>
            </li>
            <li>
                <a href="listeNote.php">
                    <i class="fas fa-star text-warning"></i> Notes
                </a>
            </li>
            <li>
                <a href="graph.php">
                    <i class="fas fa-chart-bar text-info"></i> Graphiques
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fas fa-cog text-secondary"></i> Paramètres
                </a>
            </li>
            <li>
                <a href="logout.php" class="text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="welcome-card">
            <img src="images_pages/international.png" alt="Teaching" style="width: 120px; margin-bottom: 20px;">
            <h2 class="mb-4">Bienvenue sur EducNotes</h2>
            <p class="text-muted mb-4">Gérez facilement vos étudiants et finances avec notre interface intuitive.</p>
            <button class="btn btn-primary" onclick="redirectToEtudiants()">
                <i class="fas fa-users me-2"></i>Voir la liste des étudiants
            </button>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        function redirectToEtudiants() {
            window.location.href = "EtudiantListe.php";
        }
    </script>
</body>
</html>