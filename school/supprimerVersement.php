<?php
require_once 'classes/db_connect.php'; // Aucun changement ici
require_once 'classes/Versement.php';

$database = new Database1(); // Utilisation de Database1
$db = $database->getConnection();
$versement = new Versement($db);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($versement->delete($id)) {
        header("Location: VersementListe.php?success=1");
    } else {
        header("Location: VersementListe.php?error=1");
    }
}
?>