<?php
$servername = "localhost"; // Ou o endereço do seu servidor de banco de dados
$username = "root"; // O nome de usuário do banco de dados
$password = ""; // A senha do banco de dados (pode ser vazio se estiver usando XAMPP ou WAMP)
$dbname = "chat_db"; // O nome do banco de dados

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
