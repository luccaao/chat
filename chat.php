<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat em Tempo Real</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #chat-container {
            width: 100%;
            max-width: 600px;
            height: 90vh;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #chat {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #e5ddd5;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    padding: 10px 15px;
    border-radius: 20px;
    max-width: 60%;
    font-size: 14px;
    line-height: 1.4;
    position: relative;
    display: flex;
    flex-direction: column;
}

.sent {
    background-color: #dcf8c6; /* Verde claro para mensagens enviadas */
    align-self: flex-end; /* Alinha à direita */
    border-bottom-right-radius: 0;
}

.received {
    background-color: #fff; /* Branco para mensagens recebidas */
    align-self: flex-start; /* Alinha à esquerda */
    border-bottom-left-radius: 0;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
}

.message-user {
    font-size: 12px;
    font-weight: bold;
    color: #888;
}

.message-text {
    font-size: 14px;
    color: #333;
}





        #input-container {
            display: flex;
            border-top: 1px solid #ddd;
            padding: 10px;
            background-color: #f4f7f6;
        }

        #message {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }

        #message:focus {
            border-color: #4caf50;
        }

        button {
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 20px;
            margin-left: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
  <script>
    var conn = new WebSocket('ws://localhost:8080');
    var typingTimeout;

    conn.onopen = function(e) {
        console.log("Conectado ao servidor WebSocket!");
    };

    conn.onmessage = function(e) {
        var chat = document.getElementById('chat');
        var typingNotice = document.getElementById('typing-notice');

        // Verifica se a mensagem recebida é uma notificação de digitação
        if (e.data.startsWith("typing:")) {
            var typingUser = e.data.split(":")[1].trim();

            if (typingUser === "stop") {
                // Se o "stop" for recebido, apaga a notificação
                typingNotice.textContent = "";
            } else if (typingUser !== '<?php echo $_SESSION['name']; ?>') {
                // Se for outra pessoa digitando, exibe a notificação
                typingNotice.textContent = typingUser + " está digitando...";
            }
        } else {
            // Tratamento de mensagens normais
            var message = document.createElement('div');
            message.classList.add('message');

            var msgContent = e.data.split(":");
            var userName = msgContent[0].trim(); // Nome do usuário
            var userMessage = msgContent.slice(1).join(":").trim(); // Mensagem

            // Alinhar à direita se o usuário for quem enviou, à esquerda se for de outra pessoa
            if (userName === '<?php echo $_SESSION['name']; ?>') {
                message.classList.add('sent'); // Alinha à direita
                message.innerHTML = `<span class="message-text">${userMessage}</span>`;
            } else {
                message.classList.add('received'); // Alinha à esquerda
                message.innerHTML = `<span class="message-user">${userName}</span><br><span class="message-text">${userMessage}</span>`;
            }

            chat.appendChild(message);
            chat.scrollTop = chat.scrollHeight; // Rolagem automática para a última mensagem
            typingNotice.textContent = ""; // Limpa a notificação de digitação
        }
    };

    function sendMessage() {
        var messageInput = document.getElementById('message');
        var message = messageInput.value;

        if (message.trim() !== '') {
            // Enviar a mensagem ao servidor WebSocket
            conn.send('<?php echo $_SESSION['name']; ?>: ' + message);
            messageInput.value = '';
            clearTimeout(typingTimeout);
            conn.send('typing:stop');
        }
    }

    function notifyTyping() {
        conn.send('typing:<?php echo $_SESSION['name']; ?>');
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(function() {
            conn.send('typing:stop');
        }, 3000); // Para de notificar após 3 segundos de inatividade
    }

    function loadMessages() {
    fetch('load_messages.php')
        .then(response => response.json())
        .then(data => {
            var chat = document.getElementById('chat');
            data.messages.forEach(msg => {
                var message = document.createElement('div');
                message.classList.add('message');
                // Processa e exibe a mensagem
                var userName = msg.user_name;
                var userMessage = msg.message;
                var timestamp = new Date(msg.timestamp).toLocaleTimeString();

                if (userName === '<?php echo $_SESSION['name']; ?>') {
                    message.classList.add('sent'); // Alinha à direita
                    message.innerHTML = `<span class="message-text">${userMessage}</span><br><span class="message-time">${timestamp}</span>`;
                } else {
                    message.classList.add('received'); // Alinha à esquerda
                    message.innerHTML = `<span class="message-user">${userName}</span><br><span class="message-text">${userMessage}</span><br><span class="message-time">${timestamp}</span>`;
                }

                chat.appendChild(message);
            });
            chat.scrollTop = chat.scrollHeight; // Rolagem automática para a última mensagem
        });
}

window.onload = loadMessages;


</script>



</head>
<body>
    <div id="chat-container">
        <div id="chat"></div>
        <div id="typing-notice" style="padding: 5px; color: gray; font-style: italic;"></div> <!-- Exibir aqui quem está digitando -->
        <div id="input-container">
            <input type="text" id="message" oninput="notifyTyping()" onkeypress="if(event.keyCode === 13) sendMessage();" placeholder="Digite sua mensagem">
            <button onclick="sendMessage()">Enviar</button>
        </div>
    </div>
</body>

</html>
