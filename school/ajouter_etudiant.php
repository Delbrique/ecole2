<?php
require_once 'classes/db_connect.php';
require_once 'classes/Etudiant.php';
require_once 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe = $_POST['classe'];
    $email = $_POST['email'];
    $email_parent = $_POST['email_parent'];
    $nom_parent = $_POST['nom_parent'];
    $date_naissance = $_POST['date_naissance'];
    $image_path = $_FILES['image']['name'];

    // Déterminer le montant à payer en fonction de la classe
    $montant_a_payer = 0;
    if ($classe === 'B1') {
        $montant_a_payer = 1000000;
    } elseif ($classe === 'B2') {
        $montant_a_payer = 2000000;
    } elseif ($classe === 'B3') {
        $montant_a_payer = 3000000;
    }

    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($classe) || empty($email) || empty($email_parent) || empty($date_naissance) || empty($image_path)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2011-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2011.";
    } else {
        // Générer le matricule
        $matricule = generateMatricule($db, $classe);

        // Déplacer l'image téléchargée
        $target_dir = "photos/";
        $target_file = $target_dir . basename($image_path);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Insérer les données dans la table etudiant_infos
            $etudiant->nom = $nom;
            $etudiant->prenom = $prenom;
            $etudiant->matricule = $matricule;
            $etudiant->image_path = $image_path;
            $etudiant->classe = $classe;
            $etudiant->email = $email;
            $etudiant->email_parent = $email_parent;
            $etudiant->nom_parent = $nom_parent;
            $etudiant->date_naissance = $date_naissance;
            $etudiant->montant_a_payer = $montant_a_payer;
            $etudiant->solvabilite = 'INSOLVABLE';

            if ($etudiant->create()) {
                // Insérer les données dans la table student_compte
                $stmt = $db->prepare("INSERT INTO student_compte (matricule, password) VALUES (:matricule, :password)");
                $matriculeVar = $matricule; // Utiliser une variable pour passer par référence
                $stmt->bindParam(':matricule', $matriculeVar);
                $stmt->bindParam(':password', $matriculeVar);
                $stmt->execute();

                // Générer la carte étudiant
                $pdf_path = generateStudentCard($matricule, $nom, $prenom, $classe, $date_naissance, $image_path);

                // Envoyer l'email
                sendEmail($email_parent, $email, "Carte d'Etudiant", "Recevez ci-joint la carte d'étudiant pour l'étudiant : $nom $prenom, dont le matricule est le suivant : $matricule", $pdf_path);

                $success = true;
                header("Location: EtudiantListe.php");
                exit();
            } else {
                $error = "Erreur lors de l'ajout de l'étudiant.";
            }
        } else {
            $error = "Erreur lors du téléchargement de l'image.";
        }
    }
}

function generateMatricule($db, $classe) {
    $year = date('y');
    $prefix = $year . $classe;
    $stmt = $db->prepare("SELECT matricule FROM etudiant_infos WHERE matricule LIKE :prefix ORDER BY matricule DESC LIMIT 1");
    $prefixVar = $prefix . '%'; // Utiliser une variable pour passer par référence
    $stmt->bindParam(':prefix', $prefixVar);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $lastMatricule = $row['matricule'];
        $lastNumber = intval(substr($lastMatricule, -3));
        $newNumber = sprintf('%03d', $lastNumber + 1);
    } else {
        $newNumber = '001';
    }

    return $prefix . $newNumber;
}

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
    if (!empty($image_path) && file_exists('photos/' . $image_path)) {
        $pdf->Image('photos/' . $image_path, 85, 55, 40, 40); // Centré
        $pdf->Ln(50);
    }

    // Informations de l'étudiant
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Matricule: ' . $matricule, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Nom: ' . $nom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Prenom: ' . $prenom, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Classe: ' . $classe, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Date de Naissance: ' . $date_naissance, 0, 1, 'C');

    // Ajout du logo de Keyce Informatique
    $pdf->Image('images/keyce.jpeg', 15, 10, 30, 30);

    // Ajout d'une bordure autour de la carte
    $pdf->SetXY(15, 35);
    $pdf->SetLineWidth(0.5);
    $pdf->Rect(15, 35, 180, 160, 'D');

    // Sortie PDF dans le sous-dossier "cartes"
    $pdf_path = 'cartes/carte_etudiant_' . $matricule . '.pdf';
    $pdf->Output('F', $pdf_path);

    return $pdf_path;
}

function sendEmail($to, $cc, $subject, $body, $attachment) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output
        $mail->isSMTP();                           // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';            // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                    // Enable SMTP authentication
        $mail->Username = 'gaza45palestine@gmail.com';  // SMTP username
        $mail->Password = 'tira vtly vbec schk';     // SMTP password (app-specific password)
        $mail->SMTPSecure = 'tls';                 // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                         // TCP port to connect to

        //Recipients
        $mail->setFrom('gaza45palestine@gmail.com', 'KEYCE INFOS');
        $mail->addAddress($to);                    // Add a recipient
        $mail->addCC($cc);                         // Add a CC recipient

        // Attachments
        $mail->addAttachment($attachment);         // Add attachments

        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Étudiant</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 20px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .image-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            border: 3px solid #667eea;
            padding: 3px;
            margin: 10px auto;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
        }

        .alert {
            border-radius: 10px;
            padding: 15px;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .file-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            opacity: 0;
            outline: none;
            cursor: pointer;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-user-graduate me-2"></i>
                            Inscription Nouvel Étudiant
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">
                                            <i class="fas fa-user me-2"></i>Nom
                                        </label>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">
                                            <i class="fas fa-user me-2"></i>Prénom
                                        </label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="classe" class="form-label">
                                    <i class="fas fa-graduation-cap me-2"></i>Classe
                                </label>
                                <select class="form-select" id="classe" name="classe" required>
                                    <option value="">Sélectionner une classe</option>
                                    <option value="B1">B1</option>
                                    <option value="B2">B2</option>
                                    <option value="B3">B3</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Étudiant
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom_parent" class="form-label">
                                            <i class="fas fa-users me-2"></i>Nom Parent
                                        </label>
                                        <input type="text" class="form-control" id="nom_parent" name="nom_parent" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email_parent" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email Parent
                                        </label>
                                        <input type="email" class="form-control" id="email_parent" name="email_parent" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="date_naissance" class="form-label">
                                    <i class="fas fa-calendar me-2"></i>Date de Naissance
                                </label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                            </div>

                            <div class="mb-4">
                                <div class="file-upload">
                                    <label for="image" class="form-label">
                                        <i class="fas fa-camera me-2"></i>Photo de l'étudiant
                                    </label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                    <img id="imagePreview" src="#" alt="Aperçu" class="image-preview" style="display:none;">
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prévisualisation de l'image
        document.getElementById('image').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                const preview = document.getElementById('imagePreview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });

        // Validation des formulaires Bootstrap
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
