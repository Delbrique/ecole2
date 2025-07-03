<?php
session_start();
require_once '../classes/db_connect.php';
require_once '../classes/StudentAccount.php';

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['matricule'])) {
    header('Location: index.php');
    exit();
}

function gemini($question)
{
    $GKey = "AIzaSyDl-uPsH1hDFxQn5dzABkVSGIG2fm_lbQw";
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=" . $GKey;

    $requestData = json_encode([
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $question]
                ]
            ]
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("Erreur cURL : " . curl_error($ch));
    }

    curl_close($ch);

    $responseObject = json_decode($response, true);

    if (isset($responseObject['candidates']) && count($responseObject['candidates']) > 0) {
        $content = $responseObject['candidates'][0]['content'] ?? null;
        if ($content && isset($content['parts']) && count($content['parts']) > 0) {
            return $content['parts'][0]['text'];
        } else {
            return "Aucune partie trouvée dans le contenu sélectionné.";
        }
    } else {
        return "Aucun candidat trouvé dans la réponse JSON.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = $_POST['message'];
    $response = gemini($userMessage);
    echo json_encode(['response' => $response]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgb(160, 222, 161), rgb(212, 234, 244));
            margin: 0;
            padding: 0;
            color: #333;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            float: left;
            display: block;
            color: #333;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .navbar a.active, .navbar a:hover {
            background-color: rgb(232, 232, 232);
            color: #333;
            border-radius: 5px;
        }
        .chat-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 100px auto;
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .chat-message {
            display: flex;
            align-items: center;
            margin: 10px 0;
            max-width: 70%;
        }
        .chat-message img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-message.bot img {
            margin-right: 10px;
        }
        .chat-message.user img {
            margin-left: 10px;
            order: 2;
        }
        .chat-message p {
            padding: 10px;
            border-radius: 10px;
            margin: 0;
        }
        .chat-message.bot p {
            background-color: rgb(212, 234, 244);
        }
        .chat-message.user p {
            background-color: rgb(160, 222, 161);
            align-self: flex-end;
        }
        .chat-input {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 25px;
        }
        .chat-input button {
            background-color: rgb(160, 222, 161);
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .chat-input button:hover {
            background-color: #a8d5a9;
            transform: scale(1.05);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
        }
        footer {
            background-color: rgba(255, 255, 255, 0.3);
            color: #666;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .loading {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .loading img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .loading p {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="navbar">
        <a href="card.php" class="active">Mes infos</a>
        <a href="chatbot.php">Assistant</a>
        <a href="note_eleve.php">Mes Notes</a>
    </div>
    <div class="chat-container">
        <div class="chat-messages" id="chat-messages">
            <!-- Message du bot -->
            <div class="chat-message bot">
                <img src="../images_pages/robot-assistant.png" alt="Bot Icon">
                <p>Bonjour ! Comment puis-je vous aider ?</p>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="user-input" placeholder="Tapez votre message ici...">
            <button onclick="sendMessage()">Envoyer</button>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Université KEYCE INFORMATIQUE ET IA. Tous droits réservés.</p>
    </footer>

    <script>
        function sendMessage() {
            const userInput = document.getElementById('user-input');
            const message = userInput.value;
            if (message.trim() === '') return;

            // Afficher le message de l'utilisateur
            addMessageToChat('user', message);
            userInput.value = '';

            // Afficher l'animation de chargement
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'chat-message bot loading';
            loadingDiv.innerHTML = '<img src="../images_pages/loading.gif" alt="Loading..."><p>En attente de la réponse...</p>';
            document.getElementById('chat-messages').appendChild(loadingDiv);

            // Envoyer la requête à Gemini
            fetch('chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                // Supprimer l'animation de chargement
                document.querySelector('.chat-message.bot.loading').remove();
                // Afficher la réponse de Gemini
                addMessageToChat('bot', data.response);
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }

        function addMessageToChat(sender, message) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + sender;
            if (sender === 'user') {
                messageDiv.innerHTML = '<p>' + message + '</p><img src="../images_pages/programmer.png" alt="User Icon">';
            } else {
                messageDiv.innerHTML = '<img src="../images_pages/robot-assistant.png" alt="Bot Icon"><p>' + message + '</p>';
            }
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>
