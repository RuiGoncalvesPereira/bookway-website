<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../style.css">
      <title>Bookway | Uber Uns </title>
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
         <div class="container">
            <section class="about-us">
               <h1>Über uns</h1>
               <img class="about-us-img" src="../bilder/buecher.png" alt="Über uns">
               <p class="about-us-text">Wir sind Anis Tabakovic und Rui Goncalves Pereira von der I2a an der IMS Basel. Im Rahmen des Moduls 322 wurde uns die Aufgabe gestellt, ein eigenes Online-Bücherantiquariat zu konzipieren und umzusetzen. Dieses Projekt erforderte eine gründliche Planung sowie die Entwicklung und Implementierung aller erforderlichen Funktionen.<br><br>
                  Unser Ziel war es, eine Plattform zu schaffen, auf der Nutzer Bücher durchsuchen und auswählen können. Dies umfasste die Auswahl geeigneter Bücher, die Gestaltung einer benutzerfreundlichen Website und die Integration von Zahlungsoptionen für einen reibungslosen Bestellvorgang.<br><br>
                  Die Umsetzung dieses Projekts erforderte umfassende Kenntnisse in den Bereichen Webentwicklung, Programmierung und Datenbankmanagement. Wir nutzten unsere Fähigkeiten in PHP, HTML und CSS, um eine funktionale und ästhetisch ansprechende Website zu erstellen, die den Anforderungen des Projekts entsprach.<br><br>
                  Wir danken Ihnen für Ihr Interesse an unserem Online-Bücherantiquariat und stehen Ihnen bei weiteren Fragen gerne zur Verfügung.<br><br><br><br>
            </section>
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
            <p>&copy; 2024 Bookway AG </p>
         </div>
      </footer>
   </body>
</html>
