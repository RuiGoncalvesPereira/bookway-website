<?php // Start vom Php Code

include 'db_connection.php'; // Verbindung zur Datenbank
session_start();

   $id = $_GET['id']; // ID des Buches
   $stmt = $conn->prepare("SELECT * FROM buecher WHERE id = :id"); // SQL Abfrage basierend auf der ID
   $stmt->bindParam(':id', $id); // Parameter für die ID setzen
   $stmt->execute(); // Anweisung zum Ausführen der Abfrage
   $book = $stmt->fetch(PDO::FETCH_ASSOC); // Ergebnis der Abfrage in ein assoziatives Array speichern
   $stmt = $conn->prepare("SELECT buecher.*, kategorien.kategorie AS kategorie_name # SQL Abfrage für die Kategorie
                            FROM buecher 
                            LEFT JOIN kategorien ON buecher.kategorie = kategorien.id 
                            WHERE buecher.id = :id");
   $stmt->bindParam(':id', $book['id']); // Parameter für die ID setzen
   $stmt->execute(); // Anweisung zum Ausführen der Abfrage
   $book_kat = $stmt->fetch(PDO::FETCH_ASSOC); // Ergebnis der Abfrage in ein assoziatives Array speichern
   
   
                       
   $stmt = $conn->prepare("SELECT buecher.*, zustaende.beschreibung AS zustand_name # SQL Abfrage für den Zustand
                            FROM buecher 
                            LEFT JOIN zustaende ON buecher.zustand = zustaende.zustand
                            WHERE buecher.id = :id");
   $stmt->bindParam(':id', $book['id']); // Parameter für die ID setzen
   $stmt->execute(); // Anweisung zum Ausführen der Abfrage
   $book_zustand = $stmt->fetch(PDO::FETCH_ASSOC); // Ergebnis der Abfrage in ein assoziatives Array speichern
   ?> <!-- Ende vom Php Code -->
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
      <main>
         <div class="book-details">
            <div class="book-image">
               <img src="../bilder/bookcover.png" alt="<?php echo $book['title']; ?>">
            </div>
            <div class="book-info">
               <h2><?php echo $book['kurztitle']; ?></h2> <!--Ausgabe des Buchtitels -->
               <p><strong>Beschreibung: </strong><?php echo $book['title']; ?></p> <!--Ausgabe der Buchbeschreibung -->
               <p><strong>Autor:</strong> <?php echo $book['autor']; ?></p><!--Ausgabe des Autors -->
               <p><strong>ID:</strong> <?php echo $book['id']; ?></p><!--Ausgabe der ID -->
               <p><strong>Kategorie:</strong> <?php echo $book_kat['kategorie_name']; ?></p><!--Ausgabe der Kategorie -->
               <p><strong>Zustand:</strong> <?php echo $book_zustand['zustand_name']; ?></p><!--Ausgabe des Zustands -->
               <p><strong>Verfügbarkeit:</strong> <?php echo $book['verkauft'] == 0 ? 'Verfügbar' : 'Nicht Verfügbar'; ?></p><!--Ausgabe der Verfügbarkeit -->
            </div>
         </div>
         <a href="buecher.php" class="back-button">Zurück</a>
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
