<?php
session_start();
require_once '../classes/db_connect.php';
require_once '../classes/ProfessorAccount.php';

if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

$login = $_SESSION['login'];
$professorAccount = new ProfessorAccount();
$professorInfo = $professorAccount->getProfessorInfo($login);
$nom_matiere = $professorInfo['nom_matiere'];
$niveau = substr($login, 0, 2);

$tables_notes = [
    "note_" . strtolower($niveau),
    "exam_" . strtolower($niveau),
    "tp_" . strtolower($niveau)
];

$db = new Database1();
$conn = $db->getConnection();

$students = [];
foreach ($tables_notes as $table_note) {
    $query = "SELECT matricule_etudiant, nom, prenom, `$nom_matiere` as note FROM $table_note";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $students[$table_note] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Notes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #a0dee1, #d4eaf4);
            min-height: 100vh;
            padding-top: 60px;
            padding-bottom: 60px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(45deg, #4158D0, #C850C0);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
        }

        .table td, .table th {
            padding: 1rem;
            vertical-align: middle;
        }

        .btn-assign {
            background: linear-gradient(45deg, #4158D0, #C850C0);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-assign:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .footer {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease forwards;
        }

        .badge-note {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Gestion Académique
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="notes.php">
                            <i class="fas fa-clipboard-list me-1"></i>
                            Gestion des Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="listecours.php">
                            <i class="fas fa-book me-1"></i>
                            Cours
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-4 fade-in">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Gestion des Notes - <?php echo htmlspecialchars($nom_matiere); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($tables_notes as $table_note): ?>
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-table me-2"></i>
                                    <?php echo ucfirst(str_replace(['note_', 'exam_', 'tp_'], ['Notes ', 'Examens ', 'TP '], $table_note)); ?>
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Matricule</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students[$table_note] as $student): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <?php echo htmlspecialchars($student['matricule_etudiant']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['nom']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['prenom']); ?></td>
                                                    <td>
                                                        <?php if (isset($student['note'])): ?>
                                                            <span class="badge badge-note <?php echo $student['note'] >= 10 ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo htmlspecialchars($student['note']); ?>/20
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary badge-note">Non noté</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary btn-assign" onclick="window.location.href='assign_notes.php'">
                                <i class="fas fa-plus-circle me-2"></i>
                                Octroyer les notes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer text-center">
        <div class="container">
            <p class="mb-0">&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>