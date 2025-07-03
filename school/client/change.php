<?php
session_start();
require_once '../classes/db_connect.php';
require_once '../classes/StudentAccount.php';

if (!isset($_SESSION['matricule'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];
    $studentAccount = new StudentAccount();
    $studentAccount->changePassword($_SESSION['matricule'], $newPassword);

    header('Location: card.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4158D0, #C850C0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            border: none;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 3rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.8rem;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4158D0;
            box-shadow: 0 0 0 0.2rem rgba(65, 88, 208, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4158D0, #C850C0);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .card-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid #eee;
            border-right: none;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #4158D0, #C850C0);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: white;
        }

        .password-requirements {
            font-size: 0.875rem;
            color: #666;
            margin-top: 1rem;
            text-align: left;
        }

        .password-requirements ul {
            padding-left: 1.2rem;
            margin-top: 0.5rem;
        }

        .password-requirements li {
            margin-bottom: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="logo-container">
                            <div class="logo">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <h2 class="card-title">Changer le mot de passe</h2>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="new_password" class="form-control" 
                                           placeholder="Nouveau mot de passe" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" 
                                           placeholder="Confirmer le mot de passe" required>
                                </div>
                            </div>
                            <div class="password-requirements">
                                <strong>Votre mot de passe doit contenir :</strong>
                                <ul>
                                    <li>Au moins 8 caractères</li>
                                    <li>Au moins une lettre majuscule</li>
                                    <li>Au moins un chiffre</li>
                                    <li>Au moins un caractère spécial</li>
                                </ul>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                Changer le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>