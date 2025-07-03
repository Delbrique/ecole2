<?php
session_start();
require_once '../classes/db_connect.php';
require_once '../classes/ProfessorAccount.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $professorAccount = new ProfessorAccount();
    $professorInfo = $professorAccount->getProfessorInfoByLogin($login);

    if ($professorInfo && $professorInfo['password_enseignant'] === $password) {
        if ($professorInfo['password_enseignant'] !== $login) {
            $_SESSION['login'] = $login;
            header('Location: notes.php');
            exit();
        } else {
            $_SESSION['login'] = $login;
            header('Location: change_password.php');
            exit();
        }
    } else {
        $error = "Login ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Professeur</title>
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
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            border: none;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-body {
            padding: 3rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.8rem;
            border: 2px solid #eee;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #4158D0;
            box-shadow: 0 0 0 0.2rem rgba(65, 88, 208, 0.25);
            background-color: #fff;
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

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: none;
            color: #dc3545;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .form-floating label {
            color: #6c757d;
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
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <h2 class="card-title">Connexion Professeur</h2>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" name="login" class="form-control" 
                                               id="loginInput" placeholder="Login" required>
                                        <label for="loginInput">Login</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" name="password" class="form-control" 
                                               id="passwordInput" placeholder="Mot de passe" required>
                                        <label for="passwordInput">Mot de passe</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
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