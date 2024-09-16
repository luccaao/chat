<?php
include 'db.php';

$result = $conn->query("SELECT users.name
                        FROM typing
                        JOIN users ON typing.user_id = users.id
                        WHERE typing.typing = 1");

$typing_users = [];
while ($row = $result->fetch_assoc()) {
    $typing_users[] = htmlspecialchars($row['name']);
}

if (count($typing_users) > 0) {
    echo implode(', ', $typing_users) . " está digitando...";
}
?>
