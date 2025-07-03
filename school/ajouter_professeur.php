<?php
require_once 'classes/db_connect.php';
require_once 'classes/Professeur.php';
require_once 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);
$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NomPrenom = $_POST['NomPrenom'];
    $adresse_mail = $_POST['adresse_mail'];
    $date_naissance = $_POST['date_naissance'];
    $image_path = $_FILES['image']['name'];

    if (empty($NomPrenom) || empty($adresse_mail) || empty($date_naissance) || empty($image_path)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strtotime($date_naissance) > strtotime('2010-12-31')) {
        $error = "La date de naissance ne doit pas être supérieure à 2010.";
    } else {
        $matricule_enseignant = $professeur->generateMatricule($db);
        $target_dir = "photos_enseignant/";
        $target_file = $target_dir . basename($image_path);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $professeur->matricule_enseignant = $matricule_enseignant;
            $professeur->NomPrenom = $NomPrenom;
            $professeur->adresse_mail = $adresse_mail;
            $professeur->date_naissance = $date_naissance;
            $professeur->image_path = $image_path;

            if ($professeur->create()) {
                $success = true;
                header("Location: ProfesseurListe.php");
                exit();
            } else {
                $error = "Erreur lors de l'ajout du professeur.";
            }
        } else {
            $error = "Erreur lors du téléchargement de l'image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Professeur</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .professor-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin: auto;
        }

        .card-header {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 25px;
            text-align: center;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
        }

        .image-upload {
            position: relative;
            text-align: center;
            padding: 20px;
            border: 2px dashed #1e3c72;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .image-upload:hover {
            background: rgba(30, 60, 114, 0.05);
        }

        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 15px auto;
            border: 3px solid #1e3c72;
            padding: 3px;
            display: none;
        }

        .btn-submit {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
        }

        .form-label {
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .error-message {
            background: #fff1f1;
            border-left: 4px solid #dc3545;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .upload-icon {
            font-size: 2rem;
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .upload-text {
            font-size: 0.9rem;
            color: #666;
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            color: #1e3c72;
        }

        .animated {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="professor-card animated">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Ajouter un Professeur
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="NomPrenom">
                            <i class="fas fa-user me-2"></i>Nom et Prénom
                        </label>
                        <input type="text" class="form-control" id="NomPrenom" name="NomPrenom" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="adresse_mail">
                            <i class="fas fa-envelope me-2"></i>Adresse Email
                        </label>
                        <input type="email" class="form-control" id="adresse_mail" name="adresse_mail" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="date_naissance">
                            <i class="fas fa-calendar-alt me-2"></i>Date de Naissance
                        </label>
                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                    </div>

                    <div class="mb-4">
                        <div class="image-upload">
                            <i class="fas fa-camera upload-icon"></i>
                            <p class="upload-text mb-2">Cliquez ou glissez une photo ici</p>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            <img id="imagePreview" class="image-preview" src="#" alt="Aperçu">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save me-2"></i>Enregistrer le Professeur
                    </button>
                </form>
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
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
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
