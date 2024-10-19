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

$customer_id = null;
$error = '';

// Verarbeitung des Formulars zur Aktualisierung oder Löschung eines Kunden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['customer_id'])) {
        die('Keine Kunden-ID angegeben');
    }

    $customer_id = $_POST['customer_id'];
    $new_vorname = trim($_POST['vorname']);
    $new_name = trim($_POST['name']);
    $new_email = $_POST['email'];

    // Validierung der Pflichtfelder
    if (empty($new_vorname) || empty($new_name) || empty($new_email)) {
        $error = 'Vorname, Name und Email sind Pflichtfelder!';
    } else {
        $new_geburtstag = $_POST['geburtstag'] ?? null;
        $new_geschlecht = $_POST['geschlecht'] ?? null;
        $new_kunde_seit = $_POST['kunde_seit'] ?? null;
        $new_kontaktpermail = isset($_POST['kontaktpermail']) ? 1 : 0;

        // Abrufen der aktuellen Kundendaten
        $stmt = $conn->prepare('SELECT * FROM kunden WHERE kid = ?');
        $stmt->execute([$customer_id]);
        $current_customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_customer) {
            die('Kunden-ID nicht gefunden');
        }

        // Überprüfen auf doppelte E-Mail nur, wenn diese aktualisiert wird
        if ($new_email !== $current_customer['email']) {
            $stmt = $conn->prepare('SELECT COUNT(*) FROM kunden WHERE email = ? AND kid != ?');
            $stmt->execute([$new_email, $customer_id]);
            $duplicate_email = $stmt->fetchColumn();
            if ($duplicate_email > 0) {
                $error = 'Diese E-Mail-Adresse ist bereits registriert!';
            }
        }

        if (empty($error)) {
            // Aktualisieren der Kundendaten in der Datenbank
            $stmt = $conn->prepare('UPDATE kunden SET vorname = ?, name = ?, geburtstag = ?, geschlecht = ?, kunde_seit = ?, email = ?, kontaktpermail = ? WHERE kid = ?');
            $stmt->execute([$new_vorname, $new_name, $new_geburtstag, $new_geschlecht, $new_kunde_seit, $new_email, $new_kontaktpermail, $customer_id]);
        }
    }
}

// Überprüfen, ob eine Kunden-ID übergeben wurde
if (!isset($_GET['kid']) && $customer_id === null) {
    die('Keine Kunden-ID angegeben');
}

$stmt = $conn->prepare('SELECT * FROM kunden WHERE kid = ?');
$stmt->execute([$_GET['kid'] ?? $customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die('Kunden-ID nicht gefunden');
}

// Verarbeitung des Löschens eines Kunden
if (isset($_POST['action']) && $_POST['action'] == 'Delete') {
    $stmt = $conn->prepare('DELETE FROM kunden WHERE kid = ?');
    $stmt->execute([$customer_id]);
    header('Location: kundenverwaltung.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Bookway | Kunden Details</title>
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
            <img src="../bilder/mann.png" alt="<?php echo $customer['name']; ?>">
        </div>
        <div>
            <form class="kunden-info" method="POST" action="kunden_details.php?kid=<?php echo $customer['kid']; ?>">
                <label>
                    ID: <?php echo $customer['kid']; ?>
                    <input type="hidden" name="customer_id" value="<?php echo $customer['kid']; ?>">
                </label>
                <label>
                    Vorname:
                    <input type="text" name="vorname" value="<?php echo $customer['vorname']; ?>" required>
                </label>
                <label>
                    Name:
                    <input type="text" name="name" value="<?php echo $customer['name']; ?>" required>
                </label>
                <label>
                    Geburtstag:
                    <input type="date" name="geburtstag" value="<?php echo $customer['geburtstag']; ?>">
                </label>
                <label>
                    Geschlecht:
                    <select name="geschlecht">
                        <option value="M" <?php echo $customer['geschlecht'] == 'M' ? 'selected' : ''; ?>>M</option>
                        <option value="F" <?php echo $customer['geschlecht'] == 'F' ? 'selected' : ''; ?>>F</option>
                    </select>
                </label>
                <label>
                    Kunde seit:
                    <input type="date" name="kunde_seit" value="<?php echo $customer['kunde_seit']; ?>">
                </label>
                <label>
                    Email:
                    <input type="email" name="email" value="<?php echo $customer['email']; ?>" required maxlength="150">
                </label>
                <label class="custom-checkbox">
                    Kontakt per Mail:<br>
                    <input type="checkbox" name="kontaktpermail" <?php echo $customer['kontaktpermail'] == 1 ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                </label>
                <input class="kunden-submit-button" type="submit" value="Aktualisieren">
                <div>
                    <input class="delete-button" type="submit" name="action" value="Delete">
                    <?php if ($error): ?>
                        <p style="color: red;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <a href="kundenverwaltung.php" class="back-button">Zurück</a>
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
        <p>&copy; 2024 Bookway AG </p>
    </div>
</footer>
</body>
</html>
