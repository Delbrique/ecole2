<?php
require_once 'classes/db_connect.php';
require_once 'classes/Professeur.php';

$database = new Database1();
$db = $database->getConnection();
$professeur = new Professeur($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $professeur->id = $_GET['id'];
    if ($professeur->delete()) {
        header("Location: ProfesseurListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression du professeur.";
    }
} else {
    echo "ID du professeur non fourni.";
}
?>
