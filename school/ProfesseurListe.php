<?php
require_once 'classes/db_connect.php';
require_once 'classes/Professeur.php';

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);
$stmt = $professeur->read();
$num = $stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Professeurs</title>
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

        .table-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 100%;
        }

        .main-content table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .main-content table th, .main-content table td {
            border: none;
            padding: 5px;
            text-align: center;
        }

        .main-content table img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .add-button-container {
            text-align: left;
            margin-bottom: 20px;
        }

        .main-content table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .main-content table tr:nth-child(odd) {
            background-color: #ffffff;
        }

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
    </style>
</head>
<body>
    <!-- Barre latérale gauche -->
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

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="search-card">
            <select id="search-column">
                <option value="id">ID</option>
                <option value="matricule_enseignant">Matricule</option>
                <option value="NomPrenom">Nom</option>
                <option value="adresse_mail">Email</option>
                <option value="date_naissance">Date de Naissance</option>
            </select>
            <input type="text" id="search-value" placeholder="Rechercher..." onkeyup="filterTable()">
        </div>
        <div class="table-card">
            <div class="add-button-container">
                <button class="btn btn-primary" onclick="window.location.href='ajouter_professeur.php'">
                    Ajouter un Professeur
                </button>
            </div>
            <table id="professeur-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Matricule</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Date de Naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="professeur-table-body">
                    <?php
                    if ($num > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            extract($row);
                    ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td><?php echo $matricule_enseignant; ?></td>
                                <td><img src='photos_enseignant/<?php echo $image_path; ?>' alt='Photo' class="img-fluid rounded-circle"></td>
                                <td><?php echo $NomPrenom; ?></td>
                                <td><?php echo $adresse_mail; ?></td>
                                <td><?php echo $date_naissance; ?></td>
                                <td class="action-buttons">
                                    <a href="modifier_professeur.php?id=<?php echo $id; ?>"><img src="images/pen.png" alt="Edit" class="btn-edit"></a>
                                    <a href="supprimer_professeur.php?id=<?php echo $id; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur?');"><img src="images/delete.png" alt="Delete" class="btn-delete"></a>
                                    <a href="#" onclick="showProfesseurDetails('<?php echo $id; ?>', '<?php echo $matricule_enseignant; ?>', '<?php echo $image_path; ?>', '<?php echo $NomPrenom; ?>', '<?php echo $adresse_mail; ?>', '<?php echo $date_naissance; ?>')"><img src="images/about.png" alt="Info" class="btn-info"></a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="7">Aucun professeur trouvé</td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal pour les détails du professeur -->
    <div id="professeurModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails du Professeur</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="modalProfesseurImage" src="" alt="Photo du professeur">
                <div class="details">
                    <strong>Nom:</strong> <span id="modalNomPrenom"></span>
                    <strong>Matricule:</strong> <span id="modalMatricule"></span>
                    <strong>Email:</strong> <span id="modalEmail"></span>
                    <strong>Date de Naissance:</strong> <span id="modalDateNaissance"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion des menus déroulants
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        function showProfesseurDetails(id, matricule, imagePath, nom, email, dateNaissance) {
            document.getElementById('modalProfesseurImage').src = 'photos_enseignant/' + imagePath;
            document.getElementById('modalNomPrenom').textContent = nom;
            document.getElementById('modalMatricule').textContent = matricule;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalDateNaissance').textContent = dateNaissance;
            document.getElementById('professeurModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('professeurModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('professeurModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        function filterTable() {
            const column = document.getElementById('search-column').value;
            const value = document.getElementById('search-value').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_professeur.php?column=' + column + '&value=' + value, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    const response = JSON.parse(this.responseText);
                    let output = '';
                    if (response.records) {
                        response.records.forEach(record => {
                            output += `
                                <tr>
                                    <td>${record.id}</td>
                                    <td>${record.matricule_enseignant}</td>
                                    <td><img src='photos_enseignant/${record.image_path}' alt='Photo' class="img-fluid rounded-circle"></td>
                                    <td>${record.NomPrenom}</td>
                                    <td>${record.adresse_mail}</td>
                                    <td>${record.date_naissance}</td>
                                    <td class="action-buttons">
                                        <a href="modifier_professeur.php?id=${record.id}"><img src="images/pen.png" alt="Edit" class="btn-edit"></a>
                                        <a href="#" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce professeur?');"><img src="images/delete.png" alt="Delete" class="btn-delete"></a>
                                        <a href="#" onclick="showProfesseurDetails('${record.id}', '${record.matricule_enseignant}', '${record.image_path}', '${record.NomPrenom}', '${record.adresse_mail}', '${record.date_naissance}')"><img src="images/about.png" alt="Info" class="btn-info"></a>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        output += '<tr><td colspan="7">' + response.message + '</td></tr>';
                    }
                    document.getElementById('professeur-table-body').innerHTML = output;
                }
            }
            xhr.send();
        }
    </script>
</body>
</html>
