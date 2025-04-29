<?php
require 'db.php';

$stmt = $pdo->prepare("SELECT id, file FROM quakdrop WHERE expires < ?");
$stmt->execute([time()]);

while ($row = $stmt->fetch()) {
    $file = $row['file'];
    $fullPath = __DIR__ . '/' . $file;
    
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

$stmt = $pdo->prepare("DELETE FROM quakdrop WHERE expires < ?");
$stmt->execute([time()]);
?>
