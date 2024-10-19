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

$book_id = null; 
$error = '';

// Verarbeitung des Formulars zur Aktualisierung oder Löschung eines Buches
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        die('Keine Buch-ID angegeben');
    } elseif (empty($_POST['katalog']) || empty($_POST['nummer']) || empty($_POST['kurztitle']) || empty($_POST['kategorie']) || empty($_POST['autor'])) {
        // Fehlende erforderliche Daten
        $error = 'Bitte füllen Sie alle erforderlichen Felder aus!';
    } else

    $book_id = $_POST['id'];
    $new_katalog = substr($_POST['katalog'], 0, 255);
    $new_nummer = substr($_POST['nummer'], 0, 11);
    $new_kurztitle = substr($_POST['kurztitle'], 0, 100);
    $new_kategorie = $_POST['kategorie'];
    $new_verkauft = $_POST['verkauft'];
    $new_kaufer = substr($_POST['kaufer'], 0, 255);
    $new_autor = substr($_POST['autor'], 0, 255);
    $new_title = substr($_POST['title'], 0, 255);
    $new_verfasser = substr($_POST['verfasser'], 0, 255);
    $new_zustand = $_POST['zustand'];

    // Aktualisieren der Buchdetails
    $stmt = $conn->prepare('UPDATE buecher SET katalog = ?, nummer = ?, kurztitle = ?, kategorie = ?, verkauft = ?, kaufer = ?, autor = ?, title = ?, verfasser = ?, zustand = ? WHERE id = ?');
    $stmt->execute([$new_katalog, $new_nummer, $new_kurztitle, $new_kategorie, $new_verkauft, $new_kaufer, $new_autor, $new_title, $new_verfasser, $new_zustand, $book_id]);

    
}

// Überprüfen, ob eine Buch-ID übergeben wurde
if (!isset($_GET['id']) && $book_id === null) {
    die('Keine Buch-ID angegeben');
}

$stmt = $conn->prepare('SELECT * FROM buecher WHERE id = ?');
$stmt->execute([$_GET['id'] ?? $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die('Kein Buch mit dieser ID gefunden');
}

// Verarbeitung des Löschens eines Buches
if (isset($_POST['action']) && $_POST['action'] == 'Delete') {
    $stmt = $conn->prepare('DELETE FROM buecher WHERE id = ?');
    $stmt->execute([$book_id]);
    header('Location: buecherverwaltung.php'); 
    exit();
}

// Verarbeitung des Hochladens eines Bildes
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($_FILES['foto']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        die('Invalider Dateityp. Erlaubt sind: ' . implode(',', $allowed_types));
    }

    $file_path = '../bilder/' . basename($_FILES['foto']['name']);

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $file_path)) {
        die('Fehler beim Hochladen der Datei');
    }

    // Aktualisieren des Foto-Feldes des Buches
    $stmt = $conn->prepare('UPDATE buecher SET foto = ? WHERE id = ?');
    $stmt->execute([$file_path, $book_id]);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Bookway | Buch</title>
</head>
<body>
    <header>
        <a href="../index.php"><img class="logo" src="../bilder/logo.png" alt="Logo"></a>
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
                <li><a href="login.php"><img id="loginicon" src="../bilder/loginicon.png" alt="Login Icon"></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="book-details">
            <div class="book-image">
                <img src="<?php echo $book['foto'] ? htmlspecialchars($book['foto']) : '../bilder/bookcover.png'; ?>" alt="Hier können Sie ein Bild einfügen.">
            </div>
            <div>
                <form class="kunden-info" method="POST" action="buecher_details.php?id=<?php echo htmlspecialchars($book['id']); ?>" enctype="multipart/form-data">
                    <label>
                        ID: <?php echo htmlspecialchars($book['id']); ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($book['id']); ?>">
                    </label>
                    <label>
                        Kurztitel:
                        <input type="text" name="kurztitle" value="<?php echo htmlspecialchars($book['kurztitle']); ?>" maxlength="100" required>
                    </label>
                    <label>
                        Katalog:
                        <input type="text" name="katalog" value="<?php echo htmlspecialchars($book['katalog']); ?>" maxlength="255" required>
                    </label>
                    <label>
                        Nummer:
                        <input type="text" name="nummer" value="<?php echo htmlspecialchars($book['nummer']); ?>" maxlength="255" required>
                    </label>
                    <label>
                        Kategorie:
                        <select name="kategorie" required>
                            <?php
                            $stmt = $conn->prepare('SELECT * FROM kategorien');
                            $stmt->execute();
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($categories as $category) {
                                $selected = $book['kategorie'] == $category['id'] ? 'selected' : '';
                                echo "<option value=\"{$category['id']}\" $selected>" . htmlspecialchars($category['kategorie']) . "</option>";
                            }
                            ?>
                        </select>
                    </label>
                    <label>
                        Verkauft:
                        <select name="verkauft">
                            <option value="1" <?php echo $book['verkauft'] == 1 ? 'selected' : ''; ?>>Ja</option>
                            <option value="0" <?php echo $book['verkauft'] == 0 ? 'selected' : ''; ?>>Nein</option>
                        </select>
                    </label>
                    <label>
                        Käufer:
                        <input type="text" name="kaufer" value="<?php echo htmlspecialchars($book['kaufer']); ?>" maxlength="255">
                    </label>
                    <label>
                        Autor:
                        <input type="text" name="autor" value="<?php echo htmlspecialchars($book['autor']); ?>" maxlength="255" required>
                    </label>
                    <label>
                        Titel:
                        <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" maxlength="255">
                    </label>
                    <label>
                        Verfasser:
                        <input type="text" name="verfasser" value="<?php echo htmlspecialchars($book['verfasser']); ?>" maxlength="255">
                    </label>
                    <label>
                        Zustand:
                        <select name="zustand">
                            <option value="S" <?php echo $book['zustand'] == 'S' ? 'selected' : ''; ?>>Schlecht</option>
                            <option value="M" <?php echo $book['zustand'] == 'M' ? 'selected' : ''; ?>>Mittel</option>
                            <option value="G" <?php echo $book['zustand'] == 'G' ? 'selected' : ''; ?>>Gut</option>
                        </select>
                    </label>
                    <label>
                        Foto: <?php echo htmlspecialchars($book['foto']); ?>
                        <input type="file" name="foto" accept="image/*">
                    </label>
                    <input class="kunden-submit-button" type="submit" value="Aktualisieren">
                    <div>
                        <input class="delete-button" type="submit" name="action" value="Delete">
                    </div>
                </form>
                <?php if ($error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <a href="buecherverwaltung.php" class="back-button">Zurück</a>
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
                <a href="#"><img src="../bilder/instagramlogo.png" alt="Instagram Logo"></a>
                <a href="#"><img src="../bilder/facebooklogo.png" alt="Facebook Logo"></a>
                <a href="#"><img src="../bilder/twitterlogo.png" alt="Twitter Logo"></a>
                <a href="#"><img src="../bilder/youtubelogo.png" alt="YouTube Logo"></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Bookway AG</p>
        </div>
    </footer>
</body>
</html>
