<?php
// Starten der Session
session_start();

// Einbinden der Datenbankverbindung
include 'db_connection.php';
$error = '';

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

// Abrufen der Such- und Sortierparameter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';

// Verarbeitung des Formulars zum Hinzufügen eines neuen Buches
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validierung der Daten
    if (empty($_POST['katalog']) || empty($_POST['nummer']) || empty($_POST['kurztitle']) || empty($_POST['kategorie']) || empty($_POST['autor'])) {
        // Fehlende erforderliche Daten
        $error = 'Bitte füllen Sie alle erforderlichen Felder aus!';
    } else {
        // Hinzufügen des neuen Buches zur Datenbank
        $stmt = $conn->prepare('INSERT INTO buecher (katalog, nummer, kurztitle, kategorie, verkauft, kaufer, autor, title, foto, verfasser, zustand) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['katalog'],
            $_POST['nummer'],
            $_POST['kurztitle'],
            $_POST['kategorie'],
            $_POST['verkauft'] ?? null,
            $_POST['kaufer'] ?? null,
            $_POST['autor'],
            $_POST['title'] ?? null,
            $_POST['foto'] ?? null,
            $_POST['verfasser'] ?? null,
            $_POST['zustand'] ?? null
        ]);

        header('Location: buecherverwaltung.php');
    }
}

// Funktion zur Aktivierung des Links für die aktuelle Seite
function getActiveClass($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page == $page ? 'active-link' : '';
}

// Pagination und Abfrage der Bücher
$limit = 10; 
$total_books = $conn->query("SELECT count(*) FROM buecher WHERE kurztitle LIKE '%$search%' OR title LIKE '%$search%'")->fetchColumn();
$total_pages = ceil($total_books / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$sql = "SELECT * FROM buecher WHERE kurztitle LIKE :search OR title LIKE :search ORDER BY $sort LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Bookway | Bücherverwaltung</title>
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
         <div class="links-container">
            <a href="kundenverwaltung.php" class="<?php echo getActiveClass('kundenverwaltung.php'); ?>">Kundenverwaltung</a>
            <a href="benutzerverwaltung.php" class="<?php echo getActiveClass('benutzerverwaltung.php'); ?>">Benutzerverwaltung</a>
            <a href="buecherverwaltung.php" class="<?php echo getActiveClass('buecherverwaltung.php'); ?>">Bücherverwaltung</a>
        </div>

        <div class="customers-container">
            <h2 id="customer-h2">Bücher</h2>
            <div class="customer-section">
                <div class="customer-list-pagination">
                    <div class="search-sort-container">
                        <form method="GET" autocomplete="off" class="search-form">
                            <input type="text" class="search-admin" name="search" value="<?php echo isset($search) ? $search : ''; ?>" placeholder="Suchen...">
                            <input type="submit" value="Suchen">
                            <a href="buecherverwaltung.php">Zurücksetzen</a>
                            <select class="dropdown-admin" name="sort" onchange="this.form.submit()">
                                <option value="id" <?php echo $sort == 'id' ? 'selected' : ''; ?>>ID</option>
                                <option value="kurztitle" <?php echo $sort == 'kurztitle' ? 'selected' : ''; ?>>Kurztitel</option>
                                <option value="nummer" <?php echo $sort == 'nummer' ? 'selected' : ''; ?>>Nummer</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="customer-list">
                    <?php if (isset($books) && !empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                            <div class="customer">
                                <div class="customer-info">
                                    <div class="info-item">ID: <?php echo isset($book['id']) ? $book['id'] : ''; ?></div>
                                    <div class="info-item"><?php echo isset($book['kurztitle']) ? $book['kurztitle'] : ''; ?></div>
                                    <div class="info-item">Zustand: <?php echo isset($book['zustand']) ? $book['zustand'] : ''; ?></div>
                                </div>
                                <a href="buecher_details.php?id=<?php echo isset($book['id']) ? $book['id'] : ''; ?>" class="edit-icon">
                                    <img src="../bilder/edit.png" alt="Details anzeigen">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Keine Bücher gefunden</p>
                    <?php endif; ?>
                </div>

                <div class="pagination">
                    <div class="center-form">
                        <form method="GET" autocomplete="off">
                            <?php
                            if (isset($page) && $page > 1) {
                                echo '<a href="?page=' . ($page - 1) . '&search=' . $search . '&sort=' . $sort . '">◄</a>';
                            } else {
                                echo '<span>◄</span>';
                            }
                            
                            echo '<label>Seite <input type="text" value="' . (isset($page) ? $page : '') . '" name="page" size="3" title="Seitenzahl eingeben und Eingabetaste betätigen"> von ' . (isset($total_pages) ? $total_pages : '') . '</label>';
                            
                            if (isset($page) && isset($total_pages) && $page < $total_pages) {
                                echo '<a href="?page=' . ($page + 1) . '&search=' . $search . '&sort=' . $sort . '">►</a>';
                            } else {
                                echo '<span>►</span>';
                            }
                            ?>
                        </form>
                    </div>
                </div>

                <h2>Bücher hinzufügen</h2>
                <div class="input-form-container">
                    <form method="POST" action="buecherverwaltung.php">
                        <label>
                            Kurztitel:
                            <input type="text" name="kurztitle" maxlength="100" required>
                        </label>
                        <label>
                            Katalog:
                            <input type="text" name="katalog" maxlength="255" required>
                        </label>
                        <label>
                            Nummer:
                            <input type="text" name="nummer" maxlength="255" required>
                        </label>
                        <label>
                            Kategorie:
                            <select name="kategorie" required>
                                <?php
                                // Abrufen aller Kategorien
                                $stmt = $conn->prepare('SELECT * FROM kategorien');
                                $stmt->execute();
                                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Generieren einer Option für jede Kategorie
                                foreach ($categories as $category) {
                                    $selected = '';
                                    if (isset($book['kategorie']) && $book['kategorie'] == $category['id']) {
                                        $selected = 'selected';
                                    }
                                    echo "<option value=\"{$category['id']}\" $selected>{$category['kategorie']}</option>";
                                }
                                ?>
                            </select>
                        </label>
                        <label>
                            Verkauft:
                            <select name="verkauft">
                                <option value="1">Ja</option>
                                <option value="0">Nein</option>
                            </select>
                        </label>
                        <label>
                            Käufer:
                            <input type="text" name="kaufer" maxlength="255">
                        </label>
                        <label>
                            Autor:
                            <input type="text" name="autor" maxlength="255" required>
                        </label>
                        <label>
                            Titel:
                            <input type="text" name="title" maxlength="255">
                        </label>
                        <label>
                            Verfasser:
                            <input type="text" name="verfasser" maxlength="255">
                        </label>
                        <label>
                            Zustand:
                            <select name="zustand">
                                <option value="S">Schlecht</option>
                                <option value="M">Mittel</option>
                                <option value="G">Gut</option>
                            </select>
                        </label>
                        <label id="buecherfotolabel">
                            Foto: 
                            <input id="buecherfoto" type="file" name="foto" accept="image/*">
                        </label>
                        <input id="addsubmit" type="submit" value="Buch hinzufügen">

                        <?php 
                        if (!empty($error)) {
                            echo "<p style='color:red;'>$error</p>";
                        }
                        ?>
                    </form>
                </div>
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
