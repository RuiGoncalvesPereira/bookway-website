<?php
// Einbinden der Datenbankverbindung
include 'db_connection.php';

// Starten der Session
session_start();

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user'])) {
    // Benutzer ist nicht angemeldet, Weiterleitung zur Login-Seite
    header('Location: login.php');
    exit();
}

// Überprüfen, ob der Benutzer ein Admin ist
if ($_SESSION['user']['admin'] != 1) {
    // Benutzer ist kein Admin, Weiterleitung zu einer anderen Seite
    header('Location: buecher.php');
    exit();
}

$passwordMessage = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_benutzername = $_POST['benutzername'];
    $new_name = $_POST['name'];
    $new_vorname = $_POST['vorname'];
    $new_email = $_POST['email'];
    $new_admin = $_POST['admin'];
    $new_passwort = $_POST['passwort'];

    // Abrufen der aktuellen Benutzerdetails
    $stmt = $conn->prepare('SELECT * FROM benutzer WHERE id = ?');
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_user) {
        die('Benutzer nicht gefunden');
    }

    // Überprüfen auf doppelte E-Mail nur, wenn diese aktualisiert wird
    if ($new_email !== $current_user['email']) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM benutzer WHERE email = ? AND id != ?');
        $stmt->execute([$new_email, $user_id]);
        $duplicate_email = $stmt->fetchColumn();
        if ($duplicate_email > 0) {
            $error = 'Diese E-Mail-Adresse ist bereits registriert!';
        }
    }

    // Überprüfen auf doppelten Benutzernamen nur, wenn dieser aktualisiert wird
    if ($new_benutzername !== $current_user['benutzername']) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM benutzer WHERE benutzername = ? AND id != ?');
        $stmt->execute([$new_benutzername, $user_id]);
        $duplicate_username = $stmt->fetchColumn();
        if ($duplicate_username > 0) {
            $error = 'Dieser Benutzername ist bereits registriert!';
        }
    }

    if (empty($error)) {
        // Prepare the update query with or without the password
        if (!empty($new_passwort)) {
            if (strlen($new_passwort) < 8) {
                $passwordMessage = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
            } else {
                $hashed_password = password_hash($new_passwort, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE benutzer SET benutzername = ?, name = ?, vorname = ?, passwort = ?, email = ?, admin = ? WHERE id = ?');
                $stmt->execute([$new_benutzername, $new_name, $new_vorname, $hashed_password, $new_email, $new_admin, $user_id]);
            }
        } else {
            $stmt = $conn->prepare('UPDATE benutzer SET benutzername = ?, name = ?, vorname = ?, email = ?, admin = ? WHERE id = ?');
            $stmt->execute([$new_benutzername, $new_name, $new_vorname, $new_email, $new_admin, $user_id]);
        }
    }
}

// Überprüfen, ob eine Benutzer-ID übergeben wurde
if (!isset($_GET['id']) && $user_id === null) {
    die('Keine User ID übergeben');
}

$stmt = $conn->prepare('SELECT * FROM benutzer WHERE id = ?');
$stmt->execute([$_GET['id'] ?? $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Benutzer nicht gefunden');
}

if (isset($_POST['action']) && $_POST['action'] == 'Delete') {
    // Löschen des Benutzers aus der Datenbank
    $stmt = $conn->prepare('DELETE FROM benutzer WHERE id = ?');
    $stmt->execute([$user_id]);
    header('Location: benutzerverwaltung.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Bookway | Benutzerdetails</title>
</head>
<body>
<header>
    <a href="../index.php"><img class="logo" src="../bilder/logo.png" alt="logo"></a>
    <nav>
        <ul class="nav_links">
            <li><a href="buecher.php">Bücher</a></li>
            <li><a href="ueberuns.php">Über uns</a></li>
            <li><a href="kontakt.php">Kontakt</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Benutzer ist angemeldet -->
                <li><a href="login.php?logout">Logout</a></li>
                <?php if ($_SESSION['user']['admin'] == 1): ?>
                    <!-- Benutzer ist Admin -->
                    <li><a href="kundenverwaltung.php">Admin</a></li>
                <?php endif; ?>
            <?php else: ?>
                <!-- Benutzer ist nicht angemeldet -->
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
            <li><a href="login.php"><img id="loginicon" src="../bilder/loginicon.png" alt="logobild"></a></li>
        </ul>
    </nav>
</header>
<main>
    <div class="book-details">
        <div class="book-image">
            <img src="../bilder/mann.png" alt="Profilbild">
        </div>
        <div>
            <form class="kunden-info" method="POST" action="benutzer_details.php?id=<?php echo htmlspecialchars($user['ID']); ?>">
                <label>
                    ID: <?php echo htmlspecialchars($user['ID']); ?>
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['ID']); ?>">
                </label>
                <label>
                    Benutzername:
                    <input type="text" name="benutzername" value="<?php echo htmlspecialchars($user['benutzername']); ?>" required>
                </label>
                <label>
                    Name:
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                </label>
                <label>
                    Vorname:
                    <input type="text" name="vorname" value="<?php echo htmlspecialchars($user['vorname']); ?>">
                </label>
                <label>
                    Passwort:
                    <input type="password" name="passwort" placeholder="Passwort eingeben">
                    <div style="color: red;"><?php echo $passwordMessage; ?></div>
                </label>
                <label>
                    Email:
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required maxlength="150">
                </label>
                <label>
                    Admin:
                    <select name="admin" required>
                        <option value="0" <?php echo $user['admin'] == 0 ? 'selected' : ''; ?>>Nein</option>
                        <option value="1" <?php echo $user['admin'] == 1 ? 'selected' : ''; ?>>Ja</option>
                    </select>
                </label>
                <input class="kunden-submit-button" type="submit" value="Aktualisieren">
                <div>
                    <input class="delete-button" type="submit" name="action" value="Delete">
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <a href="benutzerverwaltung.php" class="back-button">Zurück</a>
</main>
<footer>
    <div class="container">
        <div class="left">
            <p>Andreas Heusler-Strasse 41, 4052 Basel</p>
        </div>
        <div class="center">
            <p><a href="#">Impressum</a></p>
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
