<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db.php'; // Inclua o arquivo de conexão com o banco de dados

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $dbConn;

    public function __construct($dbConn) {
        $this->clients = new \SplObjectStorage;
        $this->dbConn = $dbConn; // Passa a conexão MySQLi para a classe
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nova conexão: ({$conn->resourceId})\n";

        // Enviar mensagens anteriores ao novo cliente
        $this->sendPreviousMessages($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Processar a mensagem e armazená-la no banco de dados
        $parts = explode(': ', $msg, 2);
        if (count($parts) === 2) {
            $userName = $parts[0];
            $message = $parts[1];

            // Salvar a mensagem no banco de dados usando mysqli
            $stmt = $this->dbConn->prepare("INSERT INTO messages (user_name, message) VALUES (?, ?)");
            $stmt->bind_param("ss", $userName, $message);

            if ($stmt->execute()) {
                echo "Mensagem salva com sucesso.\n";
            } else {
                echo "Erro ao salvar mensagem: " . $stmt->error . "\n";
            }
            $stmt->close();

            // Enviar a mensagem para todos os clientes, inclusive o remetente
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão fechada: ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendPreviousMessages(ConnectionInterface $conn) {
        $sql = "SELECT user_name, message FROM messages ORDER BY timestamp ASC";
        $result = $this->dbConn->query($sql);

        if ($result === false) {
            echo "Erro na consulta SQL: " . $this->dbConn->error . "\n";
            return;
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $msg = $row['user_name'] . ': ' . $row['message'];
                $conn->send($msg);
            }
        }
    }
}

// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chat_db";

// Criar a conexão com o banco de dados
$dbConn = new mysqli($servername, $username, $password, $dbname);

if ($dbConn->connect_error) {
    die("Conexão com o banco de dados falhou: " . $dbConn->connect_error);
}

// Rodar o servidor WebSocket
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new Chat($dbConn)
        )
    ),
    8080
);

$server->run();
