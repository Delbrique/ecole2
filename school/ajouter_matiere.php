<?php
// Inclure la connexion à la base de données
require_once 'classes/db_connect.php';

// Récupérer les enseignants depuis la base de données
$database = new Database1();
$conn = $database->getConnection();
$query = "SELECT matricule_enseignant, NomPrenom FROM prof";
$stmt = $conn->prepare($query);
$stmt->execute();
$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $matricule_enseignant = $_POST['matricule_enseignant'];
    $nom_prenom = $_POST['nom_prenom'];
    $niveau = $_POST['niveau'];
    $nom_matiere = strtoupper(trim($_POST['nom_matiere']));

    try {
        // Début de la transaction
        $conn->beginTransaction();

        // Vérifier les étudiants dans la table etudiant_infos pour le niveau sélectionné
        $etudiants_query = "SELECT matricule, nom, prenom FROM etudiant_infos WHERE classe = :niveau";
        $stmt_etudiants = $conn->prepare($etudiants_query);
        $stmt_etudiants->execute([':niveau' => $niveau]);
        $etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);

        // Déterminer les tables correspondantes pour le niveau
        $table_notes = "note_" . strtolower($niveau);
        $table_tp = "tp_" . strtolower($niveau);
        $table_exam = "exam_" . strtolower($niveau);

        // Fonction pour vérifier et ajouter une colonne
        function addColumnIfNotExists($conn, $table, $column) {
            $check_column_query = "SHOW COLUMNS FROM $table LIKE :column";
            $stmt_column = $conn->prepare($check_column_query);
            $stmt_column->execute([':column' => $column]);

            if ($stmt_column->rowCount() === 0) {
                $add_column_query = "ALTER TABLE $table ADD COLUMN `$column` FLOAT DEFAULT NULL";
                $conn->exec($add_column_query);
            }
        }

        // Vérifier et ajouter la colonne pour la matière dans les tables notes, tp, et exam
        addColumnIfNotExists($conn, $table_notes, $nom_matiere);
        addColumnIfNotExists($conn, $table_tp, $nom_matiere);
        addColumnIfNotExists($conn, $table_exam, $nom_matiere);

        // Fonction pour ajouter les étudiants dans une table si nécessaire
        function addStudentsIfNotExists($conn, $table, $etudiants) {
            foreach ($etudiants as $etudiant) {
                $check_etudiant_query = "SELECT * FROM $table WHERE matricule_etudiant = :matricule";
                $stmt_check = $conn->prepare($check_etudiant_query);
                $stmt_check->execute([':matricule' => $etudiant['matricule']]);

                if ($stmt_check->rowCount() === 0) {
                    $insert_etudiant_query = "INSERT INTO $table (matricule_etudiant, nom, prenom) VALUES (:matricule, :nom, :prenom)";
                    $stmt_insert = $conn->prepare($insert_etudiant_query);
                    $stmt_insert->execute([
                        ':matricule' => $etudiant['matricule'],
                        ':nom' => $etudiant['nom'],
                        ':prenom' => $etudiant['prenom']
                    ]);
                }
            }
        }

        // Ajouter les étudiants dans les tables notes, tp, et exam si nécessaire
        addStudentsIfNotExists($conn, $table_notes, $etudiants);
        addStudentsIfNotExists($conn, $table_tp, $etudiants);
        addStudentsIfNotExists($conn, $table_exam, $etudiants);

        // Générer un login_enseignant unique
        $login_base = $niveau . 'ENS';
        $login_enseignant = $login_base . '001';
        $stmt_check_login = $conn->prepare("SELECT * FROM matiere_prof WHERE login_enseignant = :login");
        $stmt_check_login->bindParam(':login', $login_enseignant);
        $stmt_check_login->execute();

        while ($stmt_check_login->rowCount() > 0) {
            $last_number = intval(substr($login_enseignant, -3)) + 1;
            $login_enseignant = $login_base . str_pad($last_number, 3, '0', STR_PAD_LEFT);
            $stmt_check_login->execute([':login' => $login_enseignant]);
        }

        // Ajouter la matière dans la table matiere_prof
        $query_insert = "INSERT INTO matiere_prof (matricule_enseignant, nom_prenom, nom_matiere, login_enseignant, password_enseignant)
                          VALUES (:matricule, :nom_prenom, :nom_matiere, :login, :password)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->execute([
            ':matricule' => $matricule_enseignant,
            ':nom_prenom' => $nom_prenom,
            ':nom_matiere' => $nom_matiere,
            ':login' => $login_enseignant,
            ':password' => $login_enseignant
        ]);

        // Valider la transaction
        $conn->commit();
    } catch (Exception $e) {
        // Vérifier si une transaction est active avant de faire un rollback
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // Enregistrer l'erreur dans un fichier de log
        error_log("Erreur : " . $e->getMessage() . "\n", 3, "error_log.txt");
    }

    // Redirection vers la page ListeMatiere.php
    header('Location: ListeMatiere.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Matière</title>
    <!-- Lien Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #F1A7C4, #A2C2E3);
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn {
            width: 100%;
            font-size: 18px;
        }
    </style>
    <script>
        function setMatricule(value) {
            const selectedOption = document.querySelector(`#enseignant option[value='${value}']`);
            const nomPrenom = selectedOption ? selectedOption.textContent : '';
            document.getElementById('matricule').value = value;
            document.getElementById('nom_prenom').value = nomPrenom;
        }
    </script>
</head>
<body>
<div class="container">
    <h2><i class="bi bi-book"></i> Ajouter une Matière</h2>
    <form method="POST">
        <div class="form-group mb-3">
            <label for="enseignant" class="form-label">
                <i class="bi bi-person"></i> Enseignant
            </label>
            <select id="enseignant" class="form-select" onchange="setMatricule(this.value)" required>
                <option value="">Sélectionnez un enseignant</option>
                <?php foreach ($enseignants as $enseignant): ?>
                    <option value="<?php echo $enseignant['matricule_enseignant']; ?>">
                        <?php echo $enseignant['NomPrenom']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="matricule" class="form-label">
                <i class="bi bi-file-earmark-text"></i> Matricule Enseignant
            </label>
            <input type="text" id="matricule" name="matricule_enseignant" class="form-control" readonly>
        </div>
        <div class="form-group mb-3">
            <label for="nom_prenom" class="form-label">
                <i class="bi bi-person-badge"></i> Nom & Prénom
            </label>
            <input type="text" id="nom_prenom" name="nom_prenom" class="form-control" readonly>
        </div>
        <div class="form-group mb-3">
            <label for="niveau" class="form-label">
                <i class="bi bi-bar-chart"></i> Niveau
            </label>
            <select id="niveau" name="niveau" class="form-select" required>
                <option value="">Sélectionnez un niveau</option>
                <option value="B1">B1</option>
                <option value="B2">B2</option>
                <option value="B3">B3</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="nom_matiere" class="form-label">
                <i class="bi bi-journal-text"></i> Nom de la Matière
            </label>
            <input type="text" id="nom_matiere" name="nom_matiere" class="form-control" placeholder="Entrez le nom de la matière" required>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Ajouter la Matière
        </button>
    </form>
</div>

<!-- Lien Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
