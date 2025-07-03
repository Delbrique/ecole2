<?php
require_once 'classes/db_connect.php';
require_once 'classes/Etudiant.php';

$database = new Database1();
$db = $database->getConnection();
$etudiant = new Etudiant($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $etudiant->id = $_GET['id'];

    if ($etudiant->delete()) {
        // Supprimer également l'entrée correspondante dans la table student_compte
        $stmt = $db->prepare("DELETE FROM student_compte WHERE matricule = :matricule");
        $stmt->bindParam(':matricule', $etudiant->matricule);
        $stmt->execute();

        header("Location: EtudiantListe.php");
        exit();
    } else {
        echo "Erreur lors de la suppression de l'étudiant.";
    }
} else {
    echo "ID de l'étudiant non fourni.";
}
?>
