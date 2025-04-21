<?php
include 'db.php';

$path = trim($_SERVER["REQUEST_URI"], "/");

$stmt = $pdo->prepare("SELECT * FROM quakdrop WHERE link = ?");
$stmt->execute([$path]);
$row = $stmt->fetch();

if ($row && time() < $row['expires']) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($row['file']) . '"');
    readfile($row['file']);
    exit;
} else {
    http_response_code(404);
    echo "Datei nicht gefunden oder abgelaufen.";
}
?>
