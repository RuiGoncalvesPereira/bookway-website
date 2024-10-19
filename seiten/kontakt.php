<?php
session_start();

include 'db_connection.php';


?>

<!DOCTYPE html>
<html lang="de">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../style.css">
      <title>Bookway | Kontakt </title>
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
         <section class="info-section">
            <h2>Unsere Informationen</h2>
            <div class="info-item">
               <h3>Allgemeine Kontaktinformationen</h3>
               <p>Telefon: +41 61 123 45 67</p>
               <p>E-Mail: support@bookway.ch
               <h3>Adresse</h3>
               <p>Andreas Heusler-Strasse 41 <br> 4052, Basel <br> Schweiz</p>
            </div>
            <div class="info-item">
               <h3>Öffnungszeiten</h3>
               <p>Montag - Freitag: 9:00 - 18:00</p>
               <p>Samstag: 10:00 - 14:00</p>
               <p>Sonntag: Geschlossen</p>
            </div>
         </section>
         <div class="contact-form-container">
            <h3>Kontakt</h3>
            <p>Haben Sie Fragen oder Probleme? Schreiben Sie uns bitte.</p>
            <form action="#" method="POST">
               <label for="name">Name:</label><br>
               <input type="text" id="name" name="name" required><br>
               <label for="email">Email:</label><br>
               <input type="email" id="email" name="email" required><br>
               <label for="message">Nachricht:</label><br>
               <textarea id="message" name="message" rows="4" cols="50" required></textarea><br>
               <input type="submit" value="Senden">
            </form>
         </div>
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
