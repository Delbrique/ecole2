<?php
session_start();
require_once '../classes/db_connect.php';

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
$table_cours = "cours_" . strtolower($classe);

// Récupération des cours de l'étudiant
$query = "SELECT * FROM $table_cours";
$stmt = $conn->prepare($query);
$stmt->execute();
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
// Garder tout le code PHP existant jusqu'à <!DOCTYPE html>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Cours</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6441A5, #2a0845);
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
            color: #6441A5;
            background-color: rgba(100, 65, 165, 0.1);
            border-radius: 8px;
        }

        .courses-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 2rem;
            margin-top: 6rem;
            transition: transform 0.3s ease;
        }

        .courses-card:hover {
            transform: translateY(-5px);
        }

        .student-info {
            background: rgba(100, 65, 165, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .course-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .course-table thead {
            background: linear-gradient(45deg, #6441A5, #2a0845);
            color: white;
        }

        .course-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            padding: 1rem !important;
            border: none !important;
        }

        .course-table td {
            padding: 1.2rem !important;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .course-table tbody tr {
            transition: all 0.3s ease;
        }

        .course-table tbody tr:hover {
            background-color: rgba(100, 65, 165, 0.05);
            transform: scale(1.01);
        }

        .download-btn {
            background: linear-gradient(45deg, #6441A5, #2a0845);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .preview-box {
            width: 100%;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8f9fa;
            border: 2px solid rgba(100, 65, 165, 0.1);
        }

        .preview-box embed {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .info-badge {
            background: white;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            margin: 0.5rem;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .course-description {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                        <a class="nav-link" href="note_eleve.php">
                            <i class="fas fa-graduation-cap me-2"></i>Mes Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mes_cours.php">
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
                <div class="courses-card">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-book me-2"></i>
                        Mes Cours
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

                    <?php if ($cours): ?>
                        <div class="table-responsive course-table">
                            <table class="table table-borderless mb-0">
                                <thead>
                                    <tr>
                                        <th>Cours</th>
                                        <th>Description</th>
                                        <th>Prévisualisation</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cours as $cour): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-alt me-2"></i>
                                                <?php echo htmlspecialchars($cour['nom_cours']); ?>
                                            </td>
                                            <td>
                                                <div class="course-description" title="<?php echo htmlspecialchars($cour['description']); ?>">
                                                    <?php echo htmlspecialchars($cour['description']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $file_path = "../cours/" . strtolower($classe) . "/" . $cour['nom_cours'];
                                                if (file_exists($file_path)): ?>
                                                    <div class="preview-box">
                                                        <embed src="<?php echo $file_path; ?>" />
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Aperçu non disponible
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (file_exists($file_path)): ?>
                                                    <a href="<?php echo $file_path; ?>" class="download-btn" download>
                                                        <i class="fas fa-download me-2"></i>Télécharger
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>
                                                        Non disponible
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun cours disponible pour le moment.
                        </div>
                    <?php endif; ?>
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
