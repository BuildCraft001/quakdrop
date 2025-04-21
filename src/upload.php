<?php
include 'db.php';

$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$expiryMinutes = 10;

function generateShortId($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $shortId = '';
    for ($i = 0; $i < $length; $i++) {
        $shortId .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $shortId;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Upload erfolgreich – QuakDrop 🦆</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>🦆 QuakDrop 🦆</h1>

<?php
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $originalName;

    // Verhindere Überschreiben
    $i = 1;
    $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    while (file_exists($filePath)) {
        $filePath = $uploadDir . $nameOnly . "_$i." . $extension;
        $i++;
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        do {
            $linkId = generateShortId();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM quakdrop WHERE link = ?");
            $stmt->execute([$linkId]);
        } while ($stmt->fetchColumn() > 0);

        $expires = time() + 600; // 10 Minuten

        $stmt = $pdo->prepare("INSERT INTO quakdrop (link, file, expires) VALUES (?, ?, ?)");
        $stmt->execute([$linkId, $filePath, $expires]);

        $host = $_SERVER['HTTP_HOST'];
        $shortLink = "https://$host/$linkId";
        $qrCodeUrl = "https://quickchart.io/chart?chs=300x300&cht=qr&chl=" . urlencode($shortLink);

        echo "<p>Dein Download-Link (gültig für $expiryMinutes Minuten):</p>";
        echo "<a href='$shortLink'>$shortLink</a><br><br>";
        echo "<div class='qrcode-container'>";
        echo "<img src='$qrCodeUrl' alt='QR Code zum Link'>";
        echo "</div>";
    } else {
        echo "<p>Fehler beim Hochladen.</p>";
    }
} else {
    echo "<p>Datei wurde nicht korrekt übertragen.</p>";
}
?>

<!-- Enten 🦆 -->
<script>
    const duckCount = 20;
    for (let i = 0; i < duckCount; i++) {
        let duck = document.createElement("div");
        duck.classList.add("duck");
        duck.style.top = Math.random() * 100 + "vh";
        duck.style.left = "-" + Math.random() * 100 + "vw";
        duck.style.animationDelay = (Math.random() * 10) + "s";
        duck.innerText = "🦆";
        document.body.appendChild(duck);
    }
</script>
</body>
</html>
