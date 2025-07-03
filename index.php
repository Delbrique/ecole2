<?php
require_once 'school/classes/Login.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $login = new Login($username, $password);
    $validationResult = $login->validate();
    if ($validationResult === true) {
        header("Location: school/dashboard.php");
        exit();
    } else {
        $error = $validationResult;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrateur</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            min-height: 100vh;
        }

        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .card-header {
            background: transparent;
            border-bottom: none;
            padding: 2rem 2rem 1rem;
        }

        .admin-icon {
            width: 64px;  /* Taille fixe plus petite */
            height: 64px; /* Taille fixe plus petite */
            object-fit: contain; /* Garde les proportions de l'image */
            margin-bottom: 1rem;
            padding: 0.5rem; /* Ajoute un peu d'espace autour de l'icône */
            border-radius: 50%;
            background-color: rgba(13, 110, 253, 0.1); /* Fond légèrement bleuté */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 2rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-control {
            border-radius: 0.75rem;
            border: 2px solid #e0e0e0;
            padding: 1rem 0.75rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-login {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            border: none;
            color: white;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #0b5ed7, #0baccc);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
        }

        .gradient-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0, #6610f2);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="gradient-background"></div>
    
    <div class="container login-container">
        <div class="card">
            <div class="card-header text-center">
                <img src="school/images_pages/admin.png" alt="Logo" class="admin-icon">
                <h4 class="mb-0">Connexion Administrateur</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur" required>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Nom d'utilisateur
                        </label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Mot de passe
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="#" class="text-decoration-none">
                        <i class="fas fa-question-circle me-1"></i>Besoin d'aide ?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les erreurs -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Erreur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage" class="text-danger"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onload = function() {
            const error = "<?php echo $error; ?>";
            if (error) {
                document.getElementById('errorMessage').innerText = error;
                new bootstrap.Modal(document.getElementById('errorModal')).show();
            }
        }
    </script>
</body>
</html>