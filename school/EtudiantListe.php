<?php
require_once 'classes/db_connect.php';
require_once 'classes/Etudiant.php';

$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);
$stmt = $etudiant->read();
$num = $stmt->rowCount();
?>
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
                <option value="matricule">Matricule</option>
                <option value="nom">Nom</option>
                <option value="prenom">Prénom</option>
                <option value="classe">Classe</option>
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
                <button class="btn btn-primary" onclick="window.location.href='ajouter_etudiant.php'">
                    Ajouter un Étudiant
                </button>
            </div>
            <table id="student-table" class="table table-bordered table-hover">
                <thead>
                    <tr>
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
                </thead>
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
                                    <img src='photos/<?php echo $image_path; ?>' alt='Photo' class="img-fluid rounded-circle">
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

        // Redirection vers la page des étudiants
        function redirectToEtudiants() {
            window.location.href = "EtudiantListe.php";
        }

        function showStudentDetails(id, matricule, imagePath, nom, prenom, classe, email, dateNaissance, nomParent, emailParent, solvabilite) {
            document.getElementById('modalStudentImage').src = 'photos/' + imagePath;
            document.getElementById('modalNom').textContent = nom;
            document.getElementById('modalPrenom').textContent = prenom;
            document.getElementById('modalMatricule').textContent = matricule;
            document.getElementById('modalClasse').textContent = classe;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalDateNaissance').textContent = dateNaissance;
            document.getElementById('modalNomParent').textContent = nomParent;
            document.getElementById('modalEmailParent').textContent = emailParent;
            document.getElementById('modalsolvabilite').textContent = solvabilite;

            document.getElementById('studentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
        }

        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('studentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Fonction pour filtrer le tableau en temps réel
        function filterTable() {
            const column = document.getElementById('search-column').value;
            const value = document.getElementById('search-value').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search.php?column=' + column + '&value=' + value, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    const response = JSON.parse(this.responseText);
                    let output = '';
                    if (response.records) {
                        response.records.forEach(record => {
                            output += `
                                <tr>
                                    <td>${record.id}</td>
                                    <td>${record.matricule}</td>
                                    <td><img src='photos/${record.image_path}' alt='Photo' class="img-fluid rounded-circle"></td>
                                    <td>${record.nom}</td>
                                    <td>${record.prenom}</td>
                                    <td>${record.classe}</td>
                                    <td>${record.email}</td>
                                    <td>${record.nom_parent}</td>
                                    <td>${record.email_parent}</td>
                                    <td>${record.date_naissance}</td>
                                    <td>${record.montant_a_payer}</td>
                                    <td>${record.solvabilite}</td>
                                    <td class="action-buttons">
                                        <a href="modifier_etudiant.php?id=${record.id}">
                                            <img src="images/pen.png" alt="Edit" class="btn-edit">
                                        </a>
                                        <a href="supprimer_etudiant.php?id=${record.id}" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?');">
                                            <img src="images/delete.png" alt="Delete" class="btn-delete">
                                        </a>
                                        <a href="#" onclick="showStudentDetails(
                                            '${record.id}',
                                            '${record.matricule}',
                                            '${record.image_path}',
                                            '${record.nom}',
                                            '${record.prenom}',
                                            '${record.classe}',
                                            '${record.email}',
                                            '${record.date_naissance}',
                                            '${record.nom_parent}',
                                            '${record.email_parent}',
                                            '${record.solvabilite}'
                                        )">
                                            <img src="images/about.png" alt="Info" class="btn-info">
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        output += `<tr><td colspan="12">${response.message}</td></tr>`;
                    }
                    document.getElementById('student-table-body').innerHTML = output;
                }
            }
            xhr.send();
        }
    </script>
</body>
</html>
