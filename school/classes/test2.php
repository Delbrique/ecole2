<?php
require_once 'classes/db_connect.php';
require_once 'classes/Etudiant.php';

$database = new Database();
$db = $database->getConnection();
$etudiant = new Etudiant($db);
$stmt = $etudiant->read();
$num = $stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
    <style>
        /* --- Style général --- */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f4f4f9;
        }

        /* --- Barre horizontale supérieure --- */
        .top-bar {
            background-color: rgb(108, 108, 108); /* Couleur claire */
            color: white; /* Changer la couleur du texte en blanc */
            width: 100%;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            height: 60px;
            z-index: 1000;
        }

        .top-bar .logo {
            font-size: 1.5em;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .top-bar .logo img {
            width: 40px;
            height: 100%;
            border-radius: 05%;
            margin-right: 10px;
        }

        /* --- Barre latérale gauche --- */
        .side-bar {
            background-color: #4a4a4a; /* Couleur sombre */
            color: white;
            width: 210px;
            padding: 20px 10px;
            position: fixed;
            top: 0;
            bottom: 0;
            height: 100vh;
            z-index: 1001;
        }
        .top-bar .logo img {
         margin-left: 260px; /* Ajuste la valeur selon tes besoins */
                           }
        .admin-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-section .admin-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        .admin-section .admin-name {
            margin-top: 10px;
            font-weight: bold;
        }

        .divider {
            border: 0;
            border-top: 1px solid #777;
            margin: 15px 0;
        }

        .menu {
            list-style: none;
            padding: 0;
        }

        .menu li {
            margin: 10px 0;
        }

        .menu a {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu a:hover {
            background-color: #5a5a5a;
        }

        .menu-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .dropdown-menu {
            list-style: none;
            padding-left: 20px;
            display: none;
        }

        .dropdown-menu a {
            color: #bbb;
        }

        .dropdown-menu a:hover {
            color: white;
        }

        /* --- Contenu principal --- */
        .main-content {
            margin-left: 250px;
            margin-right: 10px;
            margin-top: 80px;
            padding: 20px;
            flex-grow: 1;
            text-align: center;
        }

        .main-content img {
            width: 150px; /* Agrandir l'emoji */
            margin-bottom: 20px;
        }

        .main-content h2 {
            margin: 10px 0;
            font-size: 1.5em;
        }

        .main-content p {
            margin-bottom: 20px;
            color: #555;
        }

        .main-content button {
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .main-content button:hover {
            background-color: #357abd;
        }

        .main table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
        }

        .main table th, .main table td {
            padding: 10px;
            text-align: center;
        }

        .main table th {
            background-color: #66a6ff;
            color: #ffffff;
            position: sticky;
            top: 0;
            z-index: 5;
            border: 1px solid #ddd; /* Ajouter des bordures à l'en-tête */
        }

        .main table img {
            width: 40px; /* Largeur de l'image */
            height: 40px; /* Hauteur de l'image */
            border-radius: 50%; /* Bordures arrondies pour un cercle */
            object-fit: cover; /* Ajuster l'image pour qu'elle soit bien contenue */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optionnel : un ombrage léger pour un effet esthétique */
        }

        .main button {
            border: none;
            padding: 5px 10px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 2px;
        }

        .main button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .main .btn-add {
            background: linear-gradient(145deg, #66a6ff, #89f7fe);
            width: 200px;
        }

        .main .btn-edit {
            background: linear-gradient(145deg, #4caf50, #81c784);
        }

        .main .btn-delete {
            background: linear-gradient(145deg, #f44336, #e57373);
        }

        .main .btn-info {
            background: linear-gradient(145deg, #ff9800, #ffb74d);
        }

        .card {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* Modal de détails */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slide-down 0.3s ease;
        }

        @keyframes slide-down {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal-body {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 15px;
            margin-top: 15px;
        }

        .modal-body img {
            max-width: 200px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .modal-body .details {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
        }

        .modal-body .details strong {
            color: #666;
        }

        /* Style du card pour le tableau */
        .table-card {
            background-color: #fff; /* Couleur de fond blanche */
            border-radius: 10px; /* Bords arrondis */
            padding: 20px; /* Espacement intérieur */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Ombre */
            margin: 20px auto; /* Centre le card */
            max-width: 100%; /* Limite la largeur du card */
        }

        /* Styles pour le tableau */
        .main-content table {
            width: 100%; /* Occupe toute la largeur du card */
            border-collapse: collapse; /* Évite les doubles bordures */
            font-size: 12px; /* Réduit la taille de la police */
        }

        .main-content table th, .main-content table td {
            border: none; /* Enlève les bordures */
            padding: 5px; /* Réduit l'espace intérieur des cellules */
            text-align: center; /* Centre le contenu des cellules */
        }

        /* Arrondir l'image de l'étudiant */
        .main-content table img {
            width: 40px; /* Largeur de l'image */
            height: 40px; /* Hauteur de l'image */
            border-radius: 50%; /* Arrondi l'image */
            object-fit: cover; /* Garde le ratio de l'image */
        }

        /* Aligner les boutons horizontalement */
        .action-buttons {
            display: flex; /* Utilise flexbox pour l'alignement */
            gap: 5px; /* Espace entre les boutons */
        }

        .add-button-container {
            text-align: left;
            margin-bottom: 20px;
        }

        /* Réduire la hauteur des lignes et alterner les couleurs */
        .main-content table tr:nth-child(even) {
            background-color: #f9f9f9; /* Couleur grise claire pour les lignes paires */
        }

        .main-content table tr:nth-child(odd) {
            background-color: #ffffff; /* Couleur blanche pour les lignes impaires */
        }

        /* Style pour le card de recherche */
        .search-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-card select, .search-card input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        .search-card input {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <!-- Barre latérale gauche -->
    <aside class="side-bar">
        <div class="admin-section">
            <img src="images_pages/administrateur.png" alt="Admin" class="admin-icon">
            <p class="admin-name">Administrateur</p>
        </div>
        <hr class="divider">
        <ul class="menu">
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/eleve.png" alt="Etudiant" class="menu-icon"> Etudiant
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EtudiantListe.php">Liste des Etudiants</a></li>
                    <li><a href="ajouter_etudiant.php">Ajouter un étudiant</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <img src="images_pages/budget.png" alt="Finances" class="menu-icon"> Finances
                </a>
                <ul class="dropdown-menu">
                    <li><a href="VersementListe.php">Liste des versements</a></li>
                    <li><a href="ajouter_versement.php">Effectuer un versement</a></li>
                </ul>
            </li>
            <li>
                <a href="#">
                    <img src="images_pages/settings.png" alt="Paramètres" class="menu-icon"> Paramètres
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <img src="images_pages/login.png" alt="Déconnexion" class="menu-icon"> Déconnexion
                </a>
            </li>
        </ul>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="search-card">
            <select id="search-column">
                <option value="id">ID</option>
                <option value="matricule">Numero Versement</option>
                <option value="nom">Montant versé</option>
                <option value="prenom">Reste Pension</option>
                <option value="classe">Matricule Etudiant</option>
                <option value="email">Email</option>
                <option value="nom_parent">Nom Parent</option>
                <option value="email_parent">Email Parent</option>
                <option value="date_naissance">Date de Naissance</option>
                <option value="solvabilite">Solubilité</option>
            </select>
            <input type="text" id="search-value" placeholder="Rechercher..." onkeyup="filterTable()">
        </div>
        <div class="table-card">
            <div class="add-button-container">
                <button class="btn-add" onclick="window.location.href='ajouter_etudiant.php'">
                    Ajouter un Étudiant
                </button>
            </div>
            <table id="student-table">
                <tr style="background-color: #ffffff;">
                    <th>ID</th>
                    <th>Matricule</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Classe</th>
                    <th>Email</th>
                    <th>Nom Parent</th>
                    <th>Email Parent</th>
                    <th>Date de Naissance</th>
                    <th>Montant à payer</th>
                    <th>Solubilité</th>
                    <th>Actions</th>
                </tr>
                <tbody id="student-table-body">
                    <?php
                    if ($num > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($row);
                    ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td><?php echo $matricule; ?></td>
                                <td>
                                    <img src='photos/<?php echo $image_path; ?>' alt='Photo'>
                                </td>
                                <td><?php echo $nom; ?></td>
                                <td><?php echo $prenom; ?></td>
                                <td><?php echo $classe; ?></td>
                                <td><?php echo $email; ?></td>
                                <td><?php echo $nom_parent; ?></td>
                                <td><?php echo $email_parent; ?></td>
                                <td><?php echo $date_naissance; ?></td>
                                <td><?php echo $montant_a_payer; ?></td>
                                <td><?php echo $solvabilite; ?></td>
                                <td class="action-buttons">
                                    <a href="modifier_etudiant.php?id=<?php echo $id; ?>">
                                        <img src="images/pen.png" alt="Edit" class="btn-edit">
                                    </a>
                                    <a href="supprimer_etudiant.php?id=<?php echo $id; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?');">
                                        <img src="images/delete.png" alt="Delete" class="btn-delete">
                                    </a>
                                    <a href="#" onclick="showStudentDetails(
                                        '<?php echo $id; ?>',
                                        '<?php echo $matricule; ?>',
                                        '<?php echo $image_path; ?>',
                                        '<?php echo $nom; ?>',
                                        '<?php echo $prenom; ?>',
                                        '<?php echo $classe; ?>',
                                        '<?php echo $email; ?>',
                                        '<?php echo $date_naissance; ?>',
                                        '<?php echo $nom_parent; ?>',
                                        '<?php echo $email_parent; ?>',
                                        '<?php echo $solvabilite; ?>'
                                    )">
                                        <img src="images/about.png" alt="Info" class="btn-info">
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="12">Aucun étudiant trouvé</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal pour les détails de l'étudiant -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de l'Étudiant</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="modalStudentImage" src="" alt="Photo de l'étudiant">
                <div class="details">
                    <strong>Nom:</strong> <span id="modalNom"></span>
                    <strong>Prénom:</strong> <span id="modalPrenom"></span>
                    <strong>Matricule:</strong> <span id="modalMatricule"></span>
                    <strong>Classe:</strong> <span id="modalClasse"></span>
                    <strong>Email:</strong> <span id="modalEmail"></span>
                    <strong>Date de Naissance:</strong> <span id="modalDateNaissance"></span>
                    <strong>Nom Parent:</strong> <span id="modalNomParent"></span>
                    <strong>Email Parent:</strong> <span id="modalEmailParent"></span>
                    <strong>Solvabilite:</strong> <span id="modalsolvabilite"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Redirection vers la page des étudiants
        function redirectToEtudiants() {
            window.location.href = "EtudiantListe.php";
        }

        
    </script>
</body>
</html>
