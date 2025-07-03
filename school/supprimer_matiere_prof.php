<?php
require_once 'classes/db_connect.php';
require_once 'classes/MatiereProf.php';

$database = new Database1();
$db = $database->getConnection();
$matiere_prof = new MatiereProf($db);

if (isset($_GET['id'])) {
    $matiere_prof->id = $_GET['id'];
    
    if ($matiere_prof->delete()) {
        header("Location: MatiereProfListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression.";
    }
} else {
    echo "ID non fourni.";
}
?>