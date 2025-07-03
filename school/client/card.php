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

// Fonction pour générer la carte étudiant en PDF et l'envoyer au navigateur
function generateStudentCard($matricule, $nom, $prenom, $classe, $date_naissance, $image_path) {
    // Initialiser FPDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Style de la carte étudiant
    $pdf->SetFillColor(240, 240, 255); // Fond clair
    $pdf->SetDrawColor(200, 200, 200); // Bordure douce
    $pdf->SetTextColor(50, 50, 50);

    // Conteneur de la carte
    $pdf->SetXY(20, 40);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(170, 10, 'Carte Etudiant', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Ajout de l'image (photo de profil)
    if (!empty($image_path) && file_exists('../photos/' . $image_path)) {
        $pdf->Image('../photos/' . $image_path, 85, 55, 40, 40); // Centré
        $pdf->Ln(50);
    }

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prenom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de Naissance: ' . $date_naissance, 0, 1, 'C');

    // Ajout d'une bordure autour de la carte
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF directement dans le navigateur
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="carte_etudiant_' . $matricule . '.pdf"'); // Modifié pour le téléchargement
    $pdf->Output('D'); // 'D' pour forcer le téléchargement

    exit();
}

// Vérifiez si le bouton "Exporter" a été cliqué
if (isset($_POST['export'])) {
    generateStudentCard(
        $studentInfo['matricule'],
        $studentInfo['nom'],
        $studentInfo['prenom'],
        $studentInfo['classe'],
        $studentInfo['date_naissance'],
        $studentInfo['image_path']
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
    <title>Carte de l'étudiant</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6B8E23, #4682B4);
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
            color: #4682B4;
            background-color: rgba(70, 130, 180, 0.1);
            border-radius: 8px;
        }

        .student-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 2rem;
            margin-top: 6rem;
            transition: transform 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-5px);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            padding: 0.8rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(5px);
        }

        .export-btn {
            background: linear-gradient(45deg, #4682B4, #6B8E23);
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
            background: linear-gradient(45deg, #6B8E23, #4682B4);
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
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
                        <a class="nav-link active" href="card.php">
                            <i class="fas fa-id-card me-2"></i>Mes infos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chatbot.php">
                            <i class="fas fa-robot me-2"></i>Chatbot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="note_eleve.php">
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
            <div class="col-md-8">
                <div class="student-card text-center">
                    <h2 class="mb-4">
                        <i class="fas fa-id-card me-2"></i>
                        Carte de l'étudiant
                    </h2>
                    <img src="../photos/<?php echo $studentInfo['image_path']; ?>" 
                         alt="Photo de l'étudiant" 
                         class="profile-image">
                    
                    <div class="info-item">
                        <i class="fas fa-hashtag me-2"></i>
                        <strong>Matricule:</strong> <?php echo $studentInfo['matricule']; ?>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-user me-2"></i>
                        <strong>Nom:</strong> <?php echo $studentInfo['nom']; ?>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-user me-2"></i>
                        <strong>Prénom:</strong> <?php echo $studentInfo['prenom']; ?>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-users me-2"></i>
                        <strong>Classe:</strong> <?php echo $studentInfo['classe']; ?>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope me-2"></i>
                        <strong>Email:</strong> <?php echo $studentInfo['email']; ?>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-calendar me-2"></i>
                        <strong>Date de Naissance:</strong> <?php echo $studentInfo['date_naissance']; ?>
                    </div>

                    <form method="POST" action="" class="mt-4">
                        <button type="submit" name="export" class="export-btn">
                            <i class="fas fa-download me-2"></i>
                            Exporter la carte d'étudiant
                        </button>
                    </form>
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

