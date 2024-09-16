<?php
require __DIR__ . '/db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT user_name, message, timestamp FROM messages ORDER BY timestamp ASC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['messages' => $messages]);
