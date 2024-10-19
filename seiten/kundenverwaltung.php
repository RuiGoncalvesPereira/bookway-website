<?php
// Einbinden der Datenbankverbindung
include 'db_connection.php';

// Starten der Session
session_start();

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Überprüfen, ob der Benutzer ein Admin ist
if ($_SESSION['user']['admin'] != 1) {
    header('Location: buecher.php');
    exit();
}

$error = '';

// Abrufen von Such- und Sortierparametern
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'kid';

// Verarbeitung des Formulars zum Hinzufügen eines neuen Kunden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['vorname']) || empty($_POST['name']) || empty($_POST['email'])) {
        $error = 'Bitte füllen Sie alle erforderlichen Felder aus!';
    } else {
        // Überprüfen, ob die E-Mail-Adresse bereits registriert ist
        $stmt = $conn->prepare("SELECT * FROM kunden WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();

        if ($user) {
            $error = 'Diese E-Mail-Adresse ist bereits registriert!';
        } else {
            // Einfügen eines neuen Kunden in die Datenbank
            $stmt = $conn->prepare('INSERT INTO kunden (vorname, name, geburtstag, geschlecht, kunde_seit, email, kontaktpermail) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['vorname'], 
                $_POST['name'], 
                $_POST['geburtstag'] ?? null, 
                $_POST['geschlecht'] ?? null, 
                $_POST['kunde_seit'] ?? null, 
                $_POST['email'], 
                isset($_POST['kontaktpermail']) ? 1 : 0
            ]);

            header('Location: kundenverwaltung.php');
            exit();
        }
    }
}

// Funktion zur Aktivierung des Links für die aktuelle Seite
function getActiveClass($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page == $page ? 'active-link' : '';
}

$limit = 10;
$total_customers = $conn->query("SELECT count(*) FROM kunden WHERE vorname LIKE '%$search%' OR name LIKE '%$search%'")->fetchColumn();
$total_pages = ceil($total_customers / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$sql = "SELECT * FROM kunden WHERE vorname LIKE :search OR name LIKE :search ORDER BY $sort LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Admin</title>
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
            <h2 id="customer-h2">Kunden</h2>
            <div class="customer-section">
                <div class="customer-list-pagination">
                    <div class="search-sort-container">
                        <form method="GET" autocomplete="off" class="search-form">
                            <input type="text" class="search-admin" name="search" value="<?php echo isset($search) ? $search : ''; ?>" placeholder="Suchen...">
                            <input type="submit" value="Suchen">
                            <a href="kundenverwaltung.php">Zurücksetzen</a>
                            <select class="dropdown-admin" name="sort" onchange="this.form.submit()">
                                <option value="kid" <?php echo $sort == 'kid' ? 'selected' : ''; ?>>ID</option>
                                <option value="vorname" <?php echo $sort == 'vorname' ? 'selected' : ''; ?>>Vorname</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nachname</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="customer-list">
                    <?php if (isset($customers) && !empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <div class="customer">
                                <div class="customer-info">
                                    <div class="info-item">ID: <?php echo isset($customer['kid']) ? $customer['kid'] : ''; ?></div>
                                    <div class="info-item"><?php echo isset($customer['vorname']) ? $customer['vorname'] : ''; ?> <?php echo isset($customer['name']) ? $customer['name'] : ''; ?></div>
                                    <div class="info-item">E-Mail: <?php echo isset($customer['email']) ? $customer['email'] : ''; ?></div>
                                </div>
                                <a href="kunden_details.php?kid=<?php echo isset($customer['kid']) ? $customer['kid'] : ''; ?>" class="edit-icon">
                                    <img src="../bilder/edit.png" alt="Details anzeigen">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Keine Kunden gefunden.</p>
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

                <h2>Kunden hinzufügen</h2>
                <div class="input-form-container">
                    <form method="POST" action="kundenverwaltung.php">
                        <label>
                            Vorname:
                            <input type="text" name="vorname" required maxlength="50">
                        </label>
                        <label>
                            Name:
                            <input type="text" name="name" required maxlength="50">
                        </label>
                        <label>
                            Geburtstag:
                            <input type="date" name="geburtstag">
                        </label>
                        <label>
                            Geschlecht:
                            <select name="geschlecht">
                                <option value="M">M</option>
                                <option value="F">F</option>
                            </select>
                        </label>
                        <label>
                            Kunde seit:
                            <input type="date" name="kunde_seit">
                        </label>
                        <label>
                            Email:
                            <input type="email" name="email" required maxlength="150">
                        </label>
                        <label class="custom-checkbox">
                            Kontakt per Mail<br>
                            <input type="checkbox" name="kontaktpermail">
                            <span class="checkmark"></span>
                        </label>
                        <input id="addsubmit" type="submit" value="Kunde hinzufügen">
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
