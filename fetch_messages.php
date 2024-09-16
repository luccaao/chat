<?php
include 'db.php';

$result = $conn->query("SELECT users.name, messages.message, messages.created_at
                        FROM messages
                        JOIN users ON messages.user_id = users.id
                        ORDER BY messages.created_at DESC");

while ($row = $result->fetch_assoc()) {
    echo "<p><strong>" . htmlspecialchars($row['name']) . ":</strong> " . htmlspecialchars($row['message']) . " <em>" . $row['created_at'] . "</em></p>";
}
?>
