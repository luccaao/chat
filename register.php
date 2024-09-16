<?php 
include 'db.php';

if ($_SERVER['REQUEST_METHOD']== 'POST'){
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
    $stmt ->bind_param("s", $name);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    session_start();

    $_SESSION["user_id"] = $user_id;
    $_SESSION["name"] = $name;

    header("Location: chat.php");

}

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <form method="post" action="register.php">
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>