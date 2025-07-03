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
    <title>Liste des Étudiants</title>
    <style>
        /* [Tout le CSS de l'artifact précédent reste le même] */
          /* Corps principal avec le dégradé */
          body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(145deg, #89f7fe, #66a6ff);
            display: flex;
            height: 100vh;
            color: #333;
        }
        
        /* Navbar verticale */
        .sidebar {
            width: 250px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .sidebar img.logo {
            margin-top: 20px;
            width: 80px;
            height: 80px;
        }
        
        .sidebar h2 {
            margin: 10px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .sidebar .menu {
            width: 100%;
            margin-top: 30px;
        }
        
        .menu-item {
            padding: 15px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
            font-size: 16px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .menu-item img {
            width: 20px;
            height: 20px;
        }
        
        .menu-item.active {
            background: #f0f8ff;
            color: #333;
            border-left: 4px solid #66a6ff;
        }
        
        .menu-item:hover {
            background: #f0f8ff;
            border-left: 4px solid #66a6ff;
        }
        
        .menu-item.logout {
            margin-top: auto;
            color: #ff4d4d;
            border-left: 4px solid transparent;
        }
        
        /* Header horizontal */
        .header {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-top {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #66a6ff;
            color: #ffffff;
        }
        
        .header-top span {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .header-top img {
            width: 24px;
            height: 24px;
        }
        
        /* Section principale */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            overflow-y: auto;
        }
        
        .main table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .main table th, .main table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        
        .main table th {
            background-color: #66a6ff;
            color: #ffffff;
            position: sticky;
            top: 0;
            z-index: 5;
        }
        
        .main table img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .main button {
            background: linear-gradient(145deg, #66a6ff, #89f7fe);
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 10px;
        }
        
        .main button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 166, 255, 0.5);
        }
        
        .main .btn-add {
            background: linear-gradient(145deg, #66a6ff, #89f7fe);
            width: 300px;
        }
        
        .main .btn-edit {
            background: linear-gradient(145deg, #4caf50, #81c784);
            margin-right: 5px;
        }
        
        .main .btn-delete {
            background: linear-gradient(145deg, #f44336, #e57373);
            margin-right: 5px;
        }
        
        .main .btn-info {
            background: linear-gradient(145deg, #ff9800, #ffb74d);
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
    </style>
</head>
<body>
    <!-- Navbar verticale -->
    <div class="sidebar">
        <img src="images/dashboard.png" alt="Logo" class="logo">
        <h2>Mon Site</h2>
        <div class="menu">
            <a href="dashboard.php" class="menu-item">
                <img src="images/dashboard.png" alt="Dashboard"> Dashboard
            </a>
            <a href="EtudiantListe.php" class="menu-item active">
                <img src="images/user.png" alt="Étudiant"> Étudiant
            </a>
            <a href="Finance.php" class="menu-item">
                <img src="images/money.png" alt="Finance"> Finance
            </a>
            <a href="logout.php" class="menu-item logout">
                <img src="images/lock.png" alt="Déconnexion"> Se déconnecter
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="header">
        <!-- Header horizontal -->
        <div class="header-top">
            <span>Bienvenue, Administrateur</span>
            <img src="images/user.png" alt="Profil">
        </div>

        <!-- Section principale -->
        <div class="main">
            <button class="btn-add" onclick="window.location.href='ajouter_etudiant.php'">
                Ajouter un Étudiant
            </button>
            <table>
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
                    <th>Actions</th>
                </tr>
                <?php 
                if($num > 0){ 
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ 
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
                    <td>
                        <button class='btn-edit' onclick="window.location.href='modifier_etudiant.php?id=<?php echo $id; ?>'">
                            UPDATE
                        </button>
                        <button class='btn-delete' onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')){window.location.href='supprimer_etudiant.php?id=<?php echo $id; ?>'}">
                            DELETE
                        </button>
                        <button class='btn-info' onclick="showStudentDetails(
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
                        )">
                            DETAILS
                        </button>
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
            </table>
        </div>
    </div>

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
                </div>
            </div>
        </div>
    </div>

    <script>
        function showStudentDetails(id, matricule, imagePath, nom, prenom, classe, email, dateNaissance, nomParent, emailParent, montantAPayer) {
            document.getElementById('modalStudentImage').src = 'photos/' + imagePath;
            document.getElementById('modalNom').textContent = nom;
            document.getElementById('modalPrenom').textContent = prenom;
            document.getElementById('modalMatricule').textContent = matricule;
            document.getElementById('modalClasse').textContent = classe;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalDateNaissance').textContent = dateNaissance;
            document.getElementById('modalNomParent').textContent = nomParent;
            document.getElementById('modalEmailParent').textContent = emailParent;

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
    </script>
</body>
</html>