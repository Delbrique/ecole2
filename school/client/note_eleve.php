<?php
session_start();
require_once '../classes/db_connect.php';
require('../extractor_pdf/fpdf.php'); // Assurez-vous que la bibliothèque FPDF est correctement incluse.

if (!isset($_SESSION['matricule'])) {
    header('Location: index.php');
    exit();
}

$matricule = $_SESSION['matricule'];

// Récupération des informations de l'étudiant à partir de la base de données
$database = new Database1();
$conn = $database->getConnection();
$query = "SELECT * FROM etudiant_infos WHERE matricule = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $matricule);
$stmt->execute();
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$studentInfo) {
    echo "Étudiant non trouvé.";
    exit();
}

$classe = $studentInfo['classe'];
$table_notes = "note_" . strtolower($classe);

// Récupération des notes de l'étudiant
$query = "SELECT * FROM $table_notes WHERE matricule_etudiant = :matricule";
$stmt = $conn->prepare($query);
$stmt->bindParam(':matricule', $matricule);
$stmt->execute();
$notes = $stmt->fetch(PDO::FETCH_ASSOC);

// Fonction pour générer le relevé de notes en PDF et l'envoyer au navigateur
function generateReleveNotes($matricule, $nom, $prenom, $classe, $notes) {
    // Initialiser FPDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Style du relevé de notes
    $pdf->SetFillColor(240, 240, 255); // Fond clair
    $pdf->SetDrawColor(200, 200, 200); // Bordure douce
    $pdf->SetTextColor(50, 50, 50);

    // Conteneur du relevé de notes
    $pdf->SetXY(20, 40);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(170, 10, 'Relevé de Notes', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prénom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Ln(10);

    // Notes de l'étudiant
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Notes:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12);
    foreach ($notes as $matiere => $note) {
        if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
            $pdf->Cell(0, 10, ucwords(str_replace('_', ' ', $matiere)) . ": " . $note, 0, 1, 'L');
        }
    }

    // Ajout d'une bordure autour du relevé de notes
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF directement dans le navigateur
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="releve_notes_' . $matricule . '.pdf"'); // Modifié pour le téléchargement
    $pdf->Output('D'); // 'D' pour forcer le téléchargement

    exit();
}

// Vérifiez si le bouton "Exporter" a été cliqué
if (isset($_POST['export'])) {
    generateReleveNotes(
        $studentInfo['matricule'],
        $studentInfo['nom'],
        $studentInfo['prenom'],
        $studentInfo['classe'],
        $notes
    );
}
?>
<?php
// Garder tout le code PHP existant jusqu'à <!DOCTYPE html>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de Notes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            min-height: 100vh;
            padding-bottom: 60px;
            position: relative;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-nav .nav-link {
            color: #333;
            font-weight: 500;
            padding: 0.8rem 1.2rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
            border-radius: 8px;
        }

        .grades-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 2rem;
            margin-top: 6rem;
            transition: transform 0.3s ease;
        }

        .grades-card:hover {
            transform: translateY(-5px);
        }

        .student-info {
            background: rgba(52, 152, 219, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .grades-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .grades-table thead {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
        }

        .grades-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            padding: 1rem !important;
        }

        .grades-table td {
            padding: 1rem !important;
            vertical-align: middle;
        }

        .grades-table tbody tr {
            transition: all 0.3s ease;
        }

        .grades-table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
        }

        .grade-cell {
            font-weight: 600;
            color: #2ecc71;
        }

        .export-btn {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        footer {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            position: absolute;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-badge {
            background: white;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            margin: 0.5rem;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="card.php">
                            <i class="fas fa-id-card me-2"></i>Mes infos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chatbot.php">
                            <i class="fas fa-robot me-2"></i>Chatbot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="note_eleve.php">
                            <i class="fas fa-graduation-cap me-2"></i>Mes Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_cours.php">
                            <i class="fas fa-book me-2"></i>Mes Cours
                        </a>
                    </li>
                </ul>
                <a href="logout.php" class="btn logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Se déconnecter
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="grades-card">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Relevé de Notes
                    </h2>

                    <div class="student-info text-center">
                        <div class="info-badge">
                            <i class="fas fa-user me-2"></i>
                            <strong>Matricule:</strong> <?php echo $studentInfo['matricule']; ?>
                        </div>
                        <div class="info-badge">
                            <i class="fas fa-id-card me-2"></i>
                            <strong>Nom:</strong> <?php echo $studentInfo['nom']; ?>
                        </div>
                    </div>

                    <?php if ($notes): ?>
                        <div class="table-responsive grades-table">
                            <table class="table table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>Matière</th>
                                        <th class="text-center">Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notes as $matiere => $note): ?>
                                        <?php if (!in_array($matiere, ['id', 'matricule_etudiant', 'nom', 'prenom'])): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-book me-2"></i>
                                                    <?php echo ucwords(str_replace('_', ' ', $matiere)); ?>
                                                </td>
                                                <td class="text-center grade-cell">
                                                    <?php echo $note; ?>/20
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucune note disponible pour le moment.
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <form method="POST" action="">
                            <button type="submit" name="export" class="export-btn">
                                <i class="fas fa-download me-2"></i>
                                Exporter le relevé de notes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-copyright me-2"></i>
                2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
