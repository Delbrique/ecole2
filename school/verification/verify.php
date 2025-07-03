<?php
require_once '../classes/db_connect.php';
require_once 'fpdi/src/autoload.php';
use setasign\Fpdi\Fpdi;

function extractHashFromPDF($filepath) {
    try {
        // Créer une instance de FPDI
        $pdf = new Fpdi();
        
        // Nombre de pages
        $pageCount = $pdf->setSourceFile($filepath);
        
        // On sait que le hash est sur la dernière page en bas
        $templateId = $pdf->importPage($pageCount);
        
        // Obtenir les dimensions de la page
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        
        // Obtenir le contenu de la page en texte brut
        $content = $pdf->Output('S');
        
        // Rechercher spécifiquement la ligne "Code hash: XXXXX"
        if (preg_match('/Code hash: ([a-f0-9]{32})/i', $content, $matches)) {
            return $matches[1];
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Erreur lors de l'extraction du hash: " . $e->getMessage());
        return null;
    }
}

function verifyHash($hash) {
    try {
        $db = new Database1();
        $conn = $db->getConnection();
        $query = "SELECT matricule_etudiant, nom, prenom FROM releve_hash WHERE hash = :hash";
        $stmt = $conn->prepare($query);
        $stmt->execute(['hash' => $hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur de base de données: " . $e->getMessage());
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_FILES['transcript'])) {
        try {
            $pdf_file = $_FILES['transcript']['tmp_name'];
            
            // Vérifier si c'est un PDF valide
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $pdf_file);
            finfo_close($finfo);
            
            if ($mime_type !== 'application/pdf') {
                throw new Exception('Le fichier doit être un PDF');
            }
            
            // Extraire le hash du PDF
            $hash = extractHashFromPDF($pdf_file);
            
            if (!$hash) {
                echo json_encode([
                    'authentic' => false, 
                    'error' => 'Code de vérification non trouvé dans le document'
                ]);
                exit;
            }
            
            // Vérifier le hash dans la base de données
            $result = verifyHash($hash);
            
            if ($result) {
                echo json_encode([
                    'authentic' => true,
                    'data' => [
                        'nom' => $result['nom'],
                        'prenom' => $result['prenom'],
                        'matricule' => $result['matricule_etudiant']
                    ]
                ]);
            } else {
                echo json_encode([
                    'authentic' => false,
                    'error' => 'Document non authentique'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'authentic' => false, 
                'error' => $e->getMessage()
            ]);
        }
        exit;
    } elseif (isset($_POST['hash'])) {
        $result = verifyHash($_POST['hash']);
        if ($result) {
            echo json_encode([
                'authentic' => true,
                'data' => [
                    'nom' => $result['nom'],
                    'prenom' => $result['prenom'],
                    'matricule' => $result['matricule_etudiant']
                ]
            ]);
        } else {
            echo json_encode([
                'authentic' => false,
                'error' => 'Code de vérification invalide'
            ]);
        }
        exit;
    }
}
?>

<!-- Le reste du code HTML reste inchangé -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des Relevés - Keyce Informatique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .upload-zone:hover, .upload-zone.dragover {
            border-color: #0d6efd;
            background: #e9ecef;
        }
        .verification-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .icon-large {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .result-icon {
            font-size: 4rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-5">
            <i class="bi bi-shield-check text-primary"></i>
            Vérification des Relevés de Notes
        </h1>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card verification-card mb-4">
                    <div class="card-body p-4">
                        <div class="upload-zone mb-4" id="dropZone">
                            <i class="bi bi-cloud-upload icon-large text-primary"></i>
                            <h4>Déposez votre relevé de notes ici</h4>
                            <p class="text-muted">ou</p>
                            <input type="file" class="d-none" id="fileInput" accept="application/pdf">
                            <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Sélectionner un fichier
                            </button>
                            <div id="uploadStatus" class="mt-3"></div>
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-muted">ou</p>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="text" class="form-control" id="hashInput" placeholder="Entrez le code hash">
                            <button class="btn btn-primary" onclick="verifyHash()">
                                <i class="bi bi-search"></i>
                                Vérifier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de résultat -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div id="authenticIcon" class="mb-4 result-icon"></div>
                    <h4 id="resultTitle" class="mb-3"></h4>
                    <p id="resultMessage" class="text-muted"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('dragover');
            });
        });

        dropZone.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFileSelect);

        function handleDrop(e) {
            const file = e.dataTransfer.files[0];
            if (file && file.type === 'application/pdf') {
                verifyFile(file);
            } else {
                showUploadStatus('Erreur: Veuillez déposer un fichier PDF', 'danger');
            }
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                verifyFile(file);
            } else {
                showUploadStatus('Erreur: Veuillez sélectionner un fichier PDF', 'danger');
            }
        }

        function showUploadStatus(message, type) {
            const status = document.getElementById('uploadStatus');
            status.innerHTML = `<div class="alert alert-${type} mb-0">${message}</div>`;
        }

        function verifyFile(file) {
            showUploadStatus('Vérification en cours...', 'info');
            const formData = new FormData();
            formData.append('transcript', file);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(showResult)
            .catch(() => {
                showUploadStatus('Erreur lors de la vérification', 'danger');
            });
        }

        function verifyHash() {
            const hash = document.getElementById('hashInput').value.trim();
            if (!hash) {
                alert('Veuillez entrer un code hash');
                return;
            }

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `hash=${encodeURIComponent(hash)}`
            })
            .then(response => response.json())
            .then(showResult)
            .catch(() => {
                alert('Erreur lors de la vérification');
            });
        }

        function showResult(result) {
            const icon = document.getElementById('authenticIcon');
            const title = document.getElementById('resultTitle');
            const message = document.getElementById('resultMessage');

            if (result.authentic) {
                icon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                title.textContent = 'Document Authentique';
                message.textContent = `Ce relevé de notes est authentique et appartient à ${result.data.nom} ${result.data.prenom}`;
            } else {
                icon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                title.textContent = 'Document Non Authentique';
                message.textContent = result.error || 'Ce document ne correspond à aucun relevé dans notre base de données.';
            }

            document.getElementById('uploadStatus').innerHTML = '';
            resultModal.show();
        }
    </script>
</body>
</html>