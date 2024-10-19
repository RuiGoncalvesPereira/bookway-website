<?php
// Verbindung zur Datenbank herstellen
include 'db_connection.php';

// Sitzung starten
session_start();
$error = '';

// Prüfen, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Benutzereingaben erfassen und absichern
    $benutzername = isset($_POST["benutzername"]) ? $_POST["benutzername"] : '';
    $name = isset($_POST["name"]) ? $_POST["name"] : '';
    $vorname = isset($_POST["vorname"]) ? $_POST["vorname"] : '';
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $passwort = isset($_POST["passwort"]) ? $_POST["passwort"] : '';

    // Überprüfung der Eingaben
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalides Emailformat";
    } elseif (strlen($benutzername) > 45 || strlen($name) > 100 || strlen($vorname) > 100 || strlen($email) > 255 || strlen($passwort) > 255) {
        $error = "Ein oder mehrere Zeilen sind zu lang.";
    } elseif (strlen($passwort) < 8) {
        $error = "Passwort muss mindestens 8 Zeichen lang sein";
    } else {
        // Überprüfen, ob Benutzername oder Email bereits existieren
        $stmt = $conn->prepare("SELECT * FROM benutzer WHERE benutzername = ? OR email = ?");
        $stmt->execute([$benutzername, $email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Benutzer oder Email existiert schon";
        } else {
            // Neuen Benutzer in die Datenbank einfügen
            $stmt = $conn->prepare("INSERT INTO benutzer (benutzername, name, vorname, email, passwort) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$benutzername, $name, $vorname, $email, password_hash($passwort, PASSWORD_DEFAULT)]);
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Registrieren</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <a href="../index.php"><img class="logo" src="../bilder/logo.png" alt="logo"></a>
        <nav>
            <ul class="nav_links">
                <li><a href="buecher.php">Bücher</a></li>
                <li><a href="ueberuns.php">Über uns</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="login.php"><img id="loginicon" src="../bilder/loginicon.png" alt="logobild"></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="login-container">
            <div class="login-form">
                <div id="login-form-inner">
                    <h2>Registrieren</h2>
                    <form method="POST" action="register.php">
                        <label for="benutzername">Benutzername:</label><br>
                        <input type="text" id="benutzername" name="benutzername" maxlength="45" required><br>
                        <label for="name">Name:</label><br>
                        <input type="text" id="name" name="name" maxlength="100" required><br>
                        <label for="vorname">Vorname:</label><br>
                        <input type="text" id="vorname" name="vorname" maxlength="100" required><br>
                        <label for="email">E-Mail:</label><br>
                        <input type="email" id="email" name="email" maxlength="255" required><br>
                        <label for="passwort">Passwort:</label><br>
                        <input type="password" id="passwort" name="passwort" maxlength="255" required><br>
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
                <a href="#"><img src="../bilder/instagramlogo.png" alt="Social Media Icon 1"></a>
                <a href="#"><img src="../bilder/facebooklogo.png" alt="Social Media Icon 2"></a>
                <a href="#"><img src="../bilder/twitterlogo.png" alt="Social Media Icon 3"></a>
                <a href="#"><img src="../bilder/youtubelogo.png" alt="Social Media Icon 4"></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Bookway AG</p>
        </div>
    </footer>
</body>
</html>
