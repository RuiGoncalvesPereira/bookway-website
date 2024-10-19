<?php 
// Einbinden der Datenbankverbindung
include 'db_connection.php';

// Starten der Session
session_start();

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

// Handling von Such- und Sortierfunktionalität
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ID'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validierung der Daten
    if (!isset($_POST['benutzername'], $_POST['passwort'], $_POST['email'])) {
        // Fehlende Daten
        $error = 'Bitte füllen Sie alle erforderlichen Felder aus!';
    } elseif (strlen($_POST['benutzername']) > 45) {
        // Benutzername ist zu lang
        $error = 'Der Benutzername darf maximal 45 Zeichen lang sein!';
    } elseif (strlen($_POST['passwort']) < 8 || strlen($_POST['passwort']) > 255) {
        // Passwort hat ungültige Länge
        $error = 'Das Passwort muss mindestens 8 und maximal 255 Zeichen lang sein!';
    } else {
        // Überprüfen, ob der Benutzername bereits existiert
        $stmt = $conn->prepare("SELECT * FROM benutzer WHERE benutzername = ?");
        $stmt->execute([$_POST['benutzername']]);
        $user = $stmt->fetch();

        if ($user) {
            $error = 'Dieser Benutzername ist bereits registriert!';
        } else {
            // Überprüfen, ob die E-Mail-Adresse bereits existiert
            $stmt = $conn->prepare("SELECT * FROM benutzer WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                $error = 'Diese E-Mail-Adresse ist bereits registriert!';
            } else {
                // Passwort hashen
                $hashed_password = password_hash($_POST['passwort'], PASSWORD_DEFAULT);
                
                // Hinzufügen des neuen Benutzers zur Datenbank
                $stmt = $conn->prepare('INSERT INTO benutzer (benutzername, name, vorname, passwort, email, admin) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$_POST['benutzername'], $_POST['name'], $_POST['vorname'], $hashed_password, $_POST['email'], $_POST['admin']]);

                header('Location: benutzerverwaltung.php');
            }
        }
    }
}

// Funktion zur Aktivierung des Links für die aktuelle Seite
function getActiveClass($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page == $page ? 'active-link' : '';
}

$limit = 10;
$total_users = $conn->query("SELECT count(*) FROM benutzer WHERE benutzername LIKE '%$search%' OR name LIKE '%$search%'")->fetchColumn();
$total_pages = ceil($total_users / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM benutzer WHERE benutzername LIKE :search OR name LIKE :search ORDER BY $sort LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Benutzerverwaltung</title>
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
            <h2 id="customer-h2">Benutzer</h2>
            <div class="customer-section">
                <div class="customer-list-pagination">
                    <div class="search-sort-container">
                        <form method="GET" autocomplete="off" class="search-form">
                            <input type="text" class="search-admin" name="search" value="<?php echo isset($search) ? $search : ''; ?>" placeholder="Suchen...">
                            <input type="submit" value="Suchen">
                            <a href="kundenverwaltung.php">Zurücksetzen</a>
                        </form>
                        <form method="GET" autocomplete="off" class="sort-form">
                            <select class="filter-dropdown" name="sort" onchange="this.form.submit()">
                                <option disabled selected>Filtern</option>
                                <option value="ID" <?php echo $sort == 'ID' ? 'selected' : ''; ?>>ID</option>
                                <option value="benutzername" <?php echo $sort == 'benutzername' ? 'selected' : ''; ?>>Benutzername</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                            </select>
                        </form>
                    </div>
                    <div class="customer-list">
                        <?php if (isset($users) && !empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <div class="customer">
                                    <div class="customer-info">
                                        <div class="info-item">ID: <?php echo isset($user['ID']) ? htmlspecialchars($user['ID']) : ''; ?></div>
                                        <div class="info-item"><?php echo isset($user['benutzername']) ? htmlspecialchars($user['benutzername']) : ''; ?></div>
                                        <div class="info-item">E-Mail: <?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?></div>
                                        <div class="info-item">Admin: <?php echo isset($user['admin']) && $user['admin'] == 1 ? 'Ja' : 'Nein'; ?> </div>
                                    </div>
                                    <a href="benutzer_details.php?id=<?php echo isset($user['ID']) ? htmlspecialchars($user['ID']) : ''; ?>" class="edit-icon">
                                        <img src="../bilder/edit.png">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Keine Benutzer gefunden</p>
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
                    <h2>Benutzer hinzufügen</h2>
                    <div class="input-form-container">
                        <form method="POST" action="benutzerverwaltung.php">
                            <label>
                                Benutzername:
                                <input type="text" name="benutzername" required maxlength="45">
                            </label>
                            <label>
                                Name:
                                <input type="text" name="name" maxlength="255">
                            </label>
                            <label>
                                Vorname:
                                <input type="text" name="vorname" maxlength="255">
                            </label>
                            <label>
                                Passwort:
                                <input type="password" name="passwort" required minlength="8" maxlength="255">
                            </label>
                            <label>
                                Email:
                                <input type="email" name="email" required maxlength="255">
                            </label>
                            <label>
                                Admin:
                                <select name="admin" required>
                                    <option value="0">Nein</option>
                                    <option value="1">Ja</option>
                                </select>
                            </label>
                            <input id="addsubmit" type="submit" value="Benutzer hinzufügen">
                        </form>
                        <?php 
                        if (!empty($error)) {
                            echo "<p style='color:red;'>$error</p>";
                        }
                        ?>
                    </div>
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
                <a href="#"><img src="../bilder/instagramlogo.png" alt="Instagram"></a>
                <a href="#"><img src="../bilder/facebooklogo.png" alt="Facebook"></a>
                <a href="#"><img src="../bilder/twitterlogo.png" alt="Twitter"></a>
                <a href="#"><img src="../bilder/youtubelogo.png" alt="YouTube"></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Bookway AG</p>
        </div>
    </footer>
</body>
</html>
