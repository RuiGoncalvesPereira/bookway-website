<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../style.css">
      <title>Bookway | Impressum</title>
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
         <section class="impressum">
            <h1>Impressum</h1>
            <p><b>Verantwortliche Instanz:</b><br/>Bookway AG<br/>Andreas Heusler-Strasse 41<br/>4052 Basel<br/>Schweiz<br/><strong>E-Mail</strong>: support@bookway.ch<br/><br/><strong>Vertretungsberechtigte Personen</strong><br/>Anis Tabakovic<br/>Rui Goncalves Pereira<br/><br/><strong>Name des Unternehmens</strong>: Bookway<br/><strong>Registrationsnummer</strong>: CHE-123.456.789<br/><strong>Umsatzsteuer-Identifikationsnummer</strong>: CHE-123.456.789 MWST<br/><br/><strong>Haftungsausschluss</strong><br/>Der Autor übernimmt keine Gewähr für die Richtigkeit, Genauigkeit, Aktualität, Zuverlässigkeit und Vollständigkeit der Informationen.<br/>Haftungsansprüche gegen den Autor wegen Schäden materieller oder immaterieller Art, die aus dem Zugriff oder der Nutzung bzw. Nichtnutzung der veröffentlichten Informationen, durch Missbrauch der Verbindung oder durch technische Störungen entstanden sind, werden ausgeschlossen.<br/><br/>Alle Angebote sind freibleibend. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.<br/><br/><strong>Haftungsausschluss für Inhalte und Links</strong><br/>Verweise und Links auf Webseiten Dritter liegen ausserhalb unseres Verantwortungsbereichs. Es wird jegliche Verantwortung für solche Webseiten abgelehnt. Der Zugriff und die Nutzung solcher Webseiten erfolgen auf eigene Gefahr des jeweiligen Nutzers.<br/><br/><strong>Urheberrechtserklärung</strong><br/>Die Urheber- und alle anderen Rechte an Inhalten, Bildern, Fotos oder anderen Dateien auf dieser Website, gehören ausschliesslich Bookway oder den speziell genannten Rechteinhabern. Für die Reproduktion jeglicher Elemente ist die schriftliche Zustimmung des Urheberrechtsträgers im Voraus einzuholen.<br/></p>
         </section>
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
            <p>&copy; 2024 Bookway AG </p>
         </div>
      </footer>
   </body>
</html>
