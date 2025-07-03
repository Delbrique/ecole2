<?php
require_once 'classes/db_connect.php';
require 'extractor_pdf/fpdf.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class GradeProcessor {
    private $db;
    private $classe;
    private $base_table;
    private $all_averages = [];
    private $student_grades = [];

    public function __construct($db, $classe) {
        $this->db = $db;
        $this->classe = $classe;
        $this->base_table = strtolower($classe);
    }

    public function processGrades() {
        $students = $this->getStudents();
        foreach ($students as $student) {
            $this->calculateStudentGrades($student);
        }
        $this->calculateRankings();
        $this->sendReports($students);
    }

    private function getStudents() {
        $query = "SELECT DISTINCT e.matricule, e.nom, e.prenom, e.email, e.email_parent, e.nom_parent
                 FROM etudiant_infos e
                 INNER JOIN note_{$this->base_table} n ON e.matricule = n.matricule_etudiant";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasValidGrades($result) {
        if (!$result) return false;
        foreach ($result as $key => $value) {
            if (!in_array($key, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
                if (empty($value) || floatval($value) <= 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function calculateStudentGrades($student) {
        $matricule = $student['matricule'];
        $grades = [];
        $valid_grades = true;

        $tables = ["note_{$this->base_table}", "exam_{$this->base_table}", "tp_{$this->base_table}"];
        foreach ($tables as $table) {
            $query = "SELECT * FROM {$table} WHERE matricule_etudiant = :matricule";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':matricule', $matricule);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->hasValidGrades($result)) {
                $valid_grades = false;
                break;
            }

            if ($result) {
                foreach ($result as $subject => $grade) {
                    if (!in_array($subject, ['id', 'matricule_etudiant', 'nom', 'prenom'])) {
                        $grades[$subject][] = floatval($grade);
                    }
                }
            }
        }

        if ($valid_grades && !empty($grades)) {
            $final_grades = [];
            $sum = 0;
            $count = 0;

            foreach ($grades as $subject => $notes) {
                if (count($notes) === 3) {
                    $average = array_sum($notes) / 3;
                    $final_grades[$subject] = $average;
                    $sum += $average;
                    $count++;
                }
            }

            if ($count > 0) {
                $overall_average = $sum / $count;
                $this->all_averages[$matricule] = $overall_average;
                $this->student_grades[$matricule] = $final_grades;
            }
        }
    }

    private function calculateRankings() {
        arsort($this->all_averages);
    }

    private function getMention($average) {
        if ($average >= 16) return 'EXCELLENT';
        if ($average >= 14) return 'BIEN';
        if ($average >= 12) return 'ASSEZ BIEN';
        if ($average >= 10) return 'PASSABLE';
        return 'INSUFFISANT';
    }

    private function generatePDF($student, $grades, $average, $rank) {
        $mention = $this->getMention($average);
        $pdf = new FPDF('P', 'mm', 'A4');
        // Ajout du support UTF-8
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20);
    
        // En-tête avec logo
        if (file_exists('images/keyce.jpeg')) {
            $pdf->Image('images/keyce.jpeg', 20, 15, 30);
        }
    
        // Générer un hash unique
        $hash = md5($student['matricule'] . time());
        $pdf->SetTitle($hash);
    
        // En-tête du document
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 30, '', 0, 1); // Espace pour le logo
        $pdf->Cell(0, 10, 'RELEVE DE NOTES', 1, 1, 'C');
    
        // Information de l'étudiant dans un cadre
        $pdf->SetFont('Arial', '', 11);
        $pdf->Ln(10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 30, '', 1, 1, 'L', true);
        $pdf->SetY($pdf->GetY() - 25);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MATRICULE: ' . $student['matricule'], 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'NOM ET PRENOM: ' . $student['nom'] . ' ' . $student['prenom'], 0, 1);
    
        // Tableau des notes
        $pdf->Ln(15);
        
        // En-tête du tableau avec fond bleu clair
        $pdf->SetFillColor(200, 220, 255);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(90, 10, 'MATIERE', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'MOYENNE', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'APPRECIATION', 1, 1, 'C', true);
    
        // Contenu du tableau
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        foreach ($grades as $subject => $grade) {
            $subject_mention = $this->getMention($grade);
            // Conversion des caractères spéciaux
            $subject = iconv('UTF-8', 'windows-1252//TRANSLIT', $subject);
            $pdf->Cell(90, 8, $subject, 1, 0, 'L', true);
            $pdf->Cell(30, 8, number_format($grade, 2), 1, 0, 'C', true);
            $pdf->Cell(50, 8, $subject_mention, 1, 1, 'C', true);
        }
    
        // Résultats finaux dans un cadre
        $pdf->Ln(10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 30, '', 1, 1, 'L', true);
        $pdf->SetY($pdf->GetY() - 25);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MOYENNE GENERALE: ' . number_format($average, 2), 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'RANG: ' . $rank, 0, 1);
        $pdf->SetX(25);
        $pdf->Cell(0, 8, 'MENTION: ' . $mention, 0, 1);
    
        // Pied de page
        $pdf->SetY(-30);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Document généré le ' . date('d/m/Y'), 0, 1, 'C');
        $pdf->Cell(0, 10, 'Code hash: ' . $hash, 0, 1, 'C');
    
        $pdf_path = "releves/releve_{$student['matricule']}.pdf";
        $pdf->Output('F', $pdf_path);
    
        // Insérer dans la table releve_hash
        $insertQuery = "INSERT INTO releve_hash (matricule_etudiant, nom, prenom, hash) 
                       VALUES (:matricule, :nom, :prenom, :hash)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bindParam(':matricule', $student['matricule']);
        $insertStmt->bindParam(':nom', $student['nom']);
        $insertStmt->bindParam(':prenom', $student['prenom']);
        $insertStmt->bindParam(':hash', $hash);
        $insertStmt->execute();
    
        return [$pdf_path, $hash];
    }
    private function getOrientationText($nom, $prenom, $grades) {
        $GKey = "AIzaSyDl-uPsH1hDFxQn5dzABkVSGIG2fm_lbQw";

        $notes_description = "";
        foreach ($grades as $matiere => $note) {
            $notes_description .= "$matiere: $note/20, ";
        }
        $notes_description = rtrim($notes_description, ", ");

        $prompt = "En tant que conseiller d'orientation professionnel, analyse les résultats suivants pour l'étudiant " .
                 $nom . " " . $prenom . ".\n\n" .
                 "Notes: " . $notes_description . "\n\n" .
                 "Rédige un email professionnel, concis et sans partie à remplir, destiné aux parents. L'email sera adressé aux parents. Le nom de l'établissement c'est Keyce Informatique et IA et le message est envoyé de la part de la scolarité de l'établissement. Sois un peu plus large dans le message et utilise un langage professionnel.";

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;
        
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? 
               "Une erreur est survenue lors de la génération du conseil d'orientation.";
    }

    private function sendEmail($to, $cc, $nom, $prenom, $pdf_info, $orientation_text) {
        list($pdf_path, $hash) = $pdf_info;
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'gaza45palestine@gmail.com';
            $mail->Password = 'tira vtly vbec schk';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('gaza45palestine@gmail.com', 'KEYCE INFOS');
            $mail->addAddress($to);
            $mail->addCC($cc);
            $mail->addAttachment($pdf_path);

            $mail->isHTML(true);
            $mail->Subject = "Relevé de Notes - " . $nom . " " . $prenom;

            $mail->Body = "<div style='font-family: Arial, sans-serif;'>" .
                         "<h2>RÉSULTATS SCOLAIRES ET GUIDANCE PROFESSIONNELLE</h2>" .
                         "<div style='margin: 20px 0;'>" . nl2br(htmlspecialchars($orientation_text)) . "</div>" .
                         "<p style='font-style: italic;'>Code hash du fichier: " . $hash . "</p>" .
                         "<p style='color: #666;'>Message automatique - Ne pas répondre</p>" .
                         "</div>";

            $mail->AltBody = strip_tags($orientation_text) . "\n\nCode hash du fichier: " . $hash;

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi email pour {$nom} {$prenom}: {$mail->ErrorInfo}");
        }
    }

    public function sendReports($students) {
        $rank = 1;
        foreach ($this->all_averages as $matricule => $average) {
            $student = array_filter($students, function($s) use ($matricule) {
                return $s['matricule'] === $matricule;
            });
            $student = reset($student);

            if ($student && isset($this->student_grades[$matricule])) {
                $grades = $this->student_grades[$matricule];
                $pdf_info = $this->generatePDF($student, $grades, $average, $rank);
                $orientation_text = $this->getOrientationText($student['nom'], $student['prenom'], $grades);
                $this->sendEmail(
                    $student['email_parent'],
                    $student['email'],
                    $student['nom'],
                    $student['prenom'],
                    $pdf_info,
                    $orientation_text
                );
                $rank++;
            }
        }
    }
}

// Main execution
$database = new Database1();
$processor = new GradeProcessor($database->getConnection(), $_GET['classe']);
$processor->processGrades();

header('Location: listeNote.php');
exit();
?>