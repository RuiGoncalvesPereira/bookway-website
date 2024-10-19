<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="style.css">
      <title>Bookway | Home </title>
   </head>
   <body>
      <header>
         <a href="index.php"><img class="logo" src="bilder/logo.png" alt="logo"></a>
         <nav>
    <ul class="nav_links">
        <li><a href="seiten/buecher.php">Bücher</a></li>
        <li><a href="seiten/ueberuns.php">Über uns</a></li>
        <li><a href="seiten/kontakt.php">Kontakt</a></li>
        <?php if (isset($_SESSION['user'])): ?>
            <!-- User is logged in -->
            <li><a href="seiten/login.php?logout">Logout</a></li>
            <?php if ($_SESSION['user']['admin'] == 1): ?>
                <!-- User is an admin -->
                <li><a href="seiten/kundenverwaltung.php">Admin</a></li>
            <?php endif; ?>
        <?php else: ?>
            <!-- User is not logged in -->
            <li><a href="seiten/login.php">Login</a></li>
        <?php endif; ?>
        <li><a href="seiten/login.php"><img id="loginicon" src="bilder/loginicon.png" alt = "logobild" ></a></li>
    </ul>
</nav>
      </header>
      <main>
         <section class="intro">
            <div class="intro-text">
               <h1>Finde Dein <br> perfektes Buch.</h1>
               <p>In unserem Antiquariat finden Sie alles.</p>
               <div class="start-button"><a href="seiten/buecher.php" class="button-link">Jetzt loslegen</a></div>
            </div>
            <div class="intro-image">
               <img src="bilder/bookfoto.png" alt="Book">
            </div>
         </section>
         <div class="promo">
            <div class="promo-logo">
               <img src="bilder/logo.png" alt="Logo">
            </div>
            <div class="promo-text">
               <p>Die perfekte Chance ein Buch zu gewinnen</p>
            </div>
            <div class="promo-button">
               <div class="join-button"><a href="seiten/login.php" class="button-link">Jetzt beitreten</a></div>
            </div>
         </div>
         <section class="reviews">
            <h2>Rezensionen</h2>
            <div class="review">
               <div class="stars">
                  <img src="bilder/5stars.png" alt="Star">
               </div>
               <p>Das Beste vom Besten!</p>
            </div>
            <div class="review">
               <div class="stars">
                  <img src="bilder/5stars.png" alt="Star">
               </div>
               <p>Top Auswahl!</p>
            </div>
            <div class="review">
               <div class="stars">
                  <img src="bilder/5stars.png" alt="Star">
               </div>
               <p>Netter Kundenservice!</p>
            </div>
         </section>
      </main>
      <footer>
         <div class="container">
            <div class="left">
               <p>Andreas Heusler-Strasse 41, 4052 Basel</p>
            </div>
            <div class="center">
               <p><a href="seiten/impressum.php">Impressum</a></p>
            </div>
            <div class="right">
               <a href="#"><img src="bilder/instagramlogo.png" alt="Social Media Icon 1"></a>
               <a href="#"><img src="bilder/facebooklogo.png" alt="Social Media Icon 2"></a>
               <a href="#"><img src="bilder/twitterlogo.png" alt="Social Media Icon 3"></a>
               <a href="#"><img src="bilder/youtubelogo.png" alt="Social Media Icon 4"></a>
            </div>
         </div>
         <div class="copyright">
            <p>&copy; 2024 Bookway AG </p>
         </div>
      </footer>
   </body>
</html>

