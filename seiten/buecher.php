<?php

include 'db_connection.php';

session_start();
    

// Variablen für die Filterung und Sortierung
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;
$sort = $_GET['sort'] ?? null;
$order = $_GET['order'] ?? 'asc';
$search = $_GET['search'] ?? '';
$zustand = $_GET['zustand'] ?? null;
$kategorieId = $_GET['kategorie'] ?? null;
$verfuegbarkeit = $_GET['verfuegbarkeit'] ?? null;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';


// SQL Abfrage für die Bücher
$query = "SELECT * FROM buecher";
echo $verfuegbarkeit;

$conditions = [];
if ($search) {
    $conditions[] = "kurztitle LIKE :search";
}
if ($zustand) {
    $conditions[] = "zustand = :zustand";
}
if ($kategorieId) {
    $conditions[] = "buecher.kategorie = :kategorie";
}
if ($verfuegbarkeit !== null) { 
    $conditions[] = "buecher.verkauft = :verfuegbarkeit";
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}


if ($sort && $order) {
    $query .= " ORDER BY $sort $order";
}

// SQL Abfrage für die Bücher mit Limit und Offset
$query .= " LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($query);

if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if ($zustand) {
    $stmt->bindParam(':zustand', $zustand);
}
if ($kategorieId) {
    $stmt->bindParam(':kategorie', $kategorieId);
}
if ($verfuegbarkeit !== null) { 
    $stmt->bindParam(':verfuegbarkeit', $verfuegbarkeit, PDO::PARAM_INT);
}

// Ausführung der SQL Abfrage
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalBooksQuery = "SELECT COUNT(*) as total FROM buecher";
if ($conditions) {
    $totalBooksQuery .= " WHERE " . implode(" AND ", $conditions);
}
$stmt = $conn->prepare($totalBooksQuery);

// Bindung der Parameter
if (in_array("kurztitle LIKE :search", $conditions)) {
    $stmt->bindValue(':search', "%$search%");
}
if (in_array("zustand = :zustand", $conditions)) {
    $stmt->bindParam(':zustand', $zustand);
}
if (in_array("buecher.kategorie = :kategorie", $conditions)) {
    $stmt->bindParam(':kategorie', $kategorieId);
}
if (in_array("buecher.verkauft = :verfuegbarkeit", $conditions)) { 
    $stmt->bindParam(':verfuegbarkeit', $verfuegbarkeit, PDO::PARAM_INT);
}

// Ausführung der SQL Abfrage
$stmt->execute();
$query = "SELECT * FROM kategorien";
$stmt = $conn->prepare($query);
$stmt->execute();
$kategorien = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookway | Buecher</title>
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
            <!-- User is logged in -->
            <li><a href="login.php?logout">Logout</a></li>
            <?php if ($_SESSION['user']['admin'] == 1): ?>
                <!-- User is an admin -->
                <li><a href="kundenverwaltung.php">Admin</a></li>
            <?php endif; ?>
        <?php else: ?>
            <!-- User is not logged in -->
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
        <li><a href="login.php"><img id="loginicon" src="../bilder/loginicon.png" alt = "logobild" ></a></li>
    </ul>
</nav>
    </header>
<div class="new-container">
    <div class="new-text">
        <h2>Was ist neu?</h2>
        <p>Erkunden Sie in unserem Bücherantiquariat die aktuellsten Schätze! Unsere Auswahl umfasst neue Erstausgaben zeitloser Klassiker, beeindruckende historische Dokumente und beeindruckende Bildbände. Erleben Sie die Geschichte der Literatur und suchen Sie nach Ihrem nächsten Lieblingsbuch. Bitte besuchen Sie regelmässig unsere Kollektion, damit Sie keine neuen Schätze verpassen!</p>
    </div>
    <img class="new-image" src="../bilder/books.png" alt="New">
</div>

<div class="flex-container"> <!--Suche und Filter-->
    <form action="" method="get" class="search-form">
        <input type="text" class="search-form-search" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
        <a href="buecher.php">Zur&#252;cksetzen</a>
    </form>
<select id="filter" class="filter-dropdown" onchange="location = this.value;">
    <option disabled selected>Filtern</option> 
    <optgroup label="Verfügbarkeit">
        <option value="buecher.php?verfuegbarkeit=0&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&zustand=<?php echo urlencode($zustand); ?>&kategorie=<?php echo urlencode($kategorieId); ?>">Verfügbar</option>
        <option value="buecher.php?verfuegbarkeit=1&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&zustand=<?php echo urlencode($zustand); ?>&kategorie=<?php echo urlencode($kategorieId); ?>">Nicht Verfügbar</option>
    </optgroup>
    <optgroup label="Zustand">
        <option value="buecher.php?zustand=G&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&verfuegbarkeit=<?php echo urlencode($verfuegbarkeit); ?>&kategorie=<?php echo urlencode($kategorieId); ?>">Gut</option>
        <option value="buecher.php?zustand=M&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&verfuegbarkeit=<?php echo urlencode($verfuegbarkeit); ?>&kategorie=<?php echo urlencode($kategorieId); ?>">Mittel</option>
        <option value="buecher.php?zustand=S&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&verfuegbarkeit=<?php echo urlencode($verfuegbarkeit); ?>&kategorie=<?php echo urlencode($kategorieId); ?>">Schlecht</option>
    </optgroup>
    <optgroup label="Kategorien">
        <?php foreach ($kategorien as $kategorie): ?>
            <option value="buecher.php?kategorie=<?php echo urlencode($kategorie['id']); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&zustand=<?php echo urlencode($zustand); ?>&verfuegbarkeit=<?php echo urlencode($verfuegbarkeit); ?>">
                <?php echo htmlspecialchars($kategorie['kategorie']); ?>
            </option>
        <?php endforeach; ?>
    </optgroup>
</select>
<select id="sort" class="sort-dropdown" onchange="location = 'buecher.php?sort=' + this.value.split('&')[0] + '&order=' + this.value.split('&')[1] + '&search=<?php echo $search; ?>&zustand=<?php echo $zustand; ?>&kategorie=<?php echo $kategorieId; ?>';">
    <option disabled selected>Sortieren</option>
    <option value="kurztitle&asc">Titel (A-Z)</option>
    <option value="kurztitle&desc">Titel (Z-A)</option>
    <option value="kaufer&desc">Bestseller</option>
    <option value="zustand&asc">Zustand (Gut-Schlecht)</option>
    <option value="zustand&desc">Zustand (Schlecht-Gut)</option>
</select>
</div>
<div class="card-container"> <!--Buchkarten werden hier angezeigt-->
    <?php if (empty($books)): ?>
        <h1 class="no-result">Ups! Keine Bücher wurden gefunden.</h1>
    <?php else: ?>    
    <?php foreach ($books as $book): ?>
    <a href="buch_details.php?id=<?php echo $book['id']; ?>">
        <div class="card">
            <img src='../bilder/bookcover.png' alt="Book cover" style="width:100%">
            <div class="container-card">
                <h4><b><?php echo htmlspecialchars(mb_strimwidth($book['kurztitle'], 0, 30, "...")); ?></b></h4>
                <p><strong>Autor: </strong><?php echo htmlspecialchars(mb_strimwidth($book['autor'], 0, 20, "...")); ?></p>
                <p><strong>Zustand:</strong> <?php
                    $stmt = $conn->prepare("SELECT buecher.*, zustaende.beschreibung AS zustand_name 
                                            FROM buecher 
                                            LEFT JOIN zustaende ON buecher.zustand = zustaende.zustand
                                            WHERE buecher.id = :id");
                    $stmt->bindParam(':id', $book['id']);
                    $stmt->execute();
                    $book_zustand = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars($book_zustand['zustand_name']); ?> </p>
                    <p><strong>Kategorie: </strong><?php 
                    $stmt = $conn->prepare("SELECT buecher.*, kategorien.kategorie AS kategorie_name 
                                            FROM buecher 
                                            LEFT JOIN kategorien ON buecher.kategorie = kategorien.id 
                                            WHERE buecher.id = :id");
                    $stmt->bindParam(':id', $book['id']);
                    $stmt->execute();
                    $book_kat = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars(mb_strimwidth($book_kat['kategorie_name'], 0, 20, "...")); ?></p>
                    <p><strong>Verf&#252;gbarkeit:</strong> <?php echo $book['verkauft'] == 0 ? 'Verf&#252;gbar' : 'Nicht Verf&#252;gbar'; ?></p>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
$limit = 12; // blätterfunktion
$total_books = $conn->query("SELECT count(*) FROM buecher")->fetchColumn();
$total_pages = ceil($total_books / $limit);
$zustand = isset($_GET['zustand']) ? $_GET['zustand'] : null;
$kategorie = isset($_GET['kategorie']) ? $_GET['kategorie'] : null;
$verfuegbarkeit = isset($_GET['verfuegbarkeit']) ? $_GET['verfuegbarkeit'] : null;

echo '<div class="center-form">';
echo '<form method="GET" autocomplete="off">';

if ($page > 1) {
    echo '<a href="?page=' . ($page - 1) . '&sort=' . $sort . '&order=' . $order . '&zustand=' . $zustand . '&kategorie=' . $kategorie . '&verfuegbarkeit=' . $verfuegbarkeit . '">◄</a>';
} else {
    echo '<span>◄</span>';
}

echo '<label>Seite <input type="text" value="' . $page . '" name="page" size="3" title="Seitenzahl eingeben und Eingabetaste betätigen"> von ' . $total_pages . '</label>';

if ($page < $total_pages) {
    echo '<a href="?page=' . ($page + 1) . '&sort=' . $sort . '&order=' . $order . '&zustand=' . $zustand . '&kategorie=' . $kategorie . '&verfuegbarkeit=' . $verfuegbarkeit . '">►</a>';
} else {
    echo '<span>►</span>';
}

echo '</form>';
echo '</div>';
?>

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
