<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Quando um novo cliente se conecta
        $this->clients->attach($conn);
        echo "Nova conexão: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Quando uma mensagem é enviada, retransmitimos para todos os clientes conectados,
        // incluindo o remetente.
        foreach ($this->clients as $client) {
            // Enviar a mensagem para todos, inclusive o remetente
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Quando um cliente se desconecta
        $this->clients->detach($conn);
        echo "Conexão fechada: ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Se houver algum erro
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Rodar o servidor WebSocket
$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
