<?php
function getMissionComments($mission_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT comments.id, comments.text, comments.time, users.name, users.email, users.image 
        FROM comments 
        JOIN users ON users.email = comments.user_email
        WHERE mission_id=?
        ORDER BY comments.id DESC
    ");
    $stmt->bind_param("i",$mission_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addComment($mission_id, $email, $text) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO comments (mission_id, user_email, text, time) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $mission_id, $email, $text);
    $stmt->execute();
}

function deleteComment($comment_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM comments WHERE id=?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
}
?>