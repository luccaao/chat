<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typing = $_POST['typing'] == 'true' ? 1 : 0;
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO typing (user_id, typing) VALUES (?, ?) ON DUPLICATE KEY UPDATE typing = ?");
    $stmt->bind_param("iii", $user_id, $typing, $typing);
    $stmt->execute();
    $stmt->close();
}
?>
