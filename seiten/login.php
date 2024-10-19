<?php 
// Einbinden der Datenbankverbindung
include 'db_connection.php';

// Starten der Session
session_start();

if (isset($_GET['logout'])) {
    // Der Benutzer möchte sich abmelden
    unset($_SESSION['user']);
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST["benutzername"]) ? $_POST["benutzername"] : '';
    $password = isset($_POST["passwort"]) ? $_POST["passwort"] : '';

    // Serverseitige Validierung
    if (strlen($email) > 255 || strlen($password) > 255) {
        $error = "Email oder Passwort zu lang";
    } else {
        // Vorbereiten und Ausführen der SQL-Abfrage
        $stmt = $conn->prepare("SELECT * FROM benutzer WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Überprüfen des Passworts
        if ($user && password_verify($password, $user['passwort'])) {
            // Speichern der Benutzerdaten in der Session
            $_SESSION['user'] = $user;
            // Weiterleiten je nach Benutzerrolle
            if ($user['admin'] == 1) {
                header("Location: kundenverwaltung.php");
            } else {
                header("Location: buecher.php");
            }
            exit;
        } else {
            $error = "Invalide E-Mail oder Passwort";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Login</title>
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
                    <h2>Login</h2>
                    <form method="POST" action="login.php">
                        <label for="username">E-Mail:</label><br>
                        <input type="email" id="username" name="benutzername" maxlength="255" required><br>
                        <label for="passwort">Passwort:</label><br>
                        <input type="password" id="passwort" name="passwort" maxlength="255" required><br>
                        <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>
                        <input type="submit" value="Submit">
                        <div class="form-links">
                            <a href="register.php">Konto erstellen</a>
                            <a href="resetpassword.php">Passwort zurücksetzen</a>
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
