<?php 

include 'db_connection.php'; // Datenbankverbindung einbinden

session_start(); // Session starten
$error = ''; // Fehler-Variable initialisieren

// Überprüfen, ob das Formular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formulardaten holen und überprüfen, ob sie gesetzt sind
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $oldpasswort = isset($_POST["oldpasswort"]) ? $_POST["oldpasswort"] : '';
    $newpasswort = isset($_POST["newpasswort"]) ? $_POST["newpasswort"] : '';
    $confirmpasswort = isset($_POST["confirmpasswort"]) ? $_POST["confirmpasswort"] : '';

    // Server-seitige Validierung
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ungültiges E-Mail-Format";
    } elseif (strlen($newpasswort) < 8 || strlen($newpasswort) > 255) {
        $error = "Das Passwort muss zwischen 8 und 255 Zeichen lang sein";
    } elseif ($newpasswort !== $confirmpasswort) {
        $error = "Passwörter stimmen nicht überein";
    } else {
        // Passwort in der Datenbank überprüfen
        $stmt = $conn->prepare("SELECT passwort FROM benutzer WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        // Wenn das alte Passwort korrekt ist
        if ($result && password_verify($oldpasswort, $result['passwort'])) {
            // Neues Passwort setzen
            $stmt = $conn->prepare("UPDATE benutzer SET passwort = ? WHERE email = ?");
            $stmt->execute([password_hash($newpasswort, PASSWORD_DEFAULT), $email]);
            header("Location: login.php"); // Benutzer zur Login-Seite weiterleiten
            exit;
        } else {
            $error = "Falsches altes Passwort";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Passwort zurücksetzen</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <a href="../index.php"><img class="logo" src="../bilder/logo.png" alt="Logo"></a>
        <nav>
            <ul class="nav_links">
                <li><a href="buecher.php">Bücher</a></li>
                <li><a href="ueberuns.php">Über uns</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="login.php"><img id="loginicon" src="../bilder/loginicon.png" alt="Login-Icon"></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="login-container">
            <div class="login-form">
                <div id="login-form-inner">
                    <h2>Passwort zurücksetzen</h2>
                    <form method="POST" action="resetpassword.php">
                        <label for="email">E-Mail:</label><br>
                        <input type="email" id="email" name="email" maxlength="255" required><br>
                        <label for="oldpasswort">Altes Passwort:</label><br>
                        <input type="password" id="oldpasswort" name="oldpasswort" maxlength="255" required><br>
                        <label for="newpasswort">Neues Passwort:</label><br>
                        <input type="password" id="newpasswort" name="newpasswort" minlength="8" maxlength="255" required><br>
                        <label for="confirmpasswort">Passwort bestätigen:</label><br>
                        <input type="password" id="confirmpasswort" name="confirmpasswort" minlength="8" maxlength="255" required><br>
                        <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
                        <input type="submit" value="Submit">
                        <div class="form-links">
                            <a href="login.php">Einloggen</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="login-image">
                <img src="../bilder/book_login2.png" alt="Login Image" class="login-img">
            </div>
        </div>
    </main>
    <footer>
        <div class="container">
            <div class="left">
                <p>Andreas Heusler-Strasse 41, 4052 Basel</p>
            </div>
            <div class="center">
                <p><a href="impressum.php">Impressum</a></p>
            </div>
            <div class="right">
                <a href="#"><img src="../bilder/instagramlogo.png" alt="Instagram"></a>
                <a href="#"><img src="../bilder/facebooklogo.png" alt="Facebook"></a>
                <a href="#"><img src="../bilder/twitterlogo.png" alt="Twitter"></a>
                <a href="#"><img src="../bilder/youtubelogo.png" alt="YouTube"></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Bookway AG </p>
        </div>
    </footer>
</body>
</html>
