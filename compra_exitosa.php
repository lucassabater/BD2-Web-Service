<?php
  session_start();

  if (!isset($_SESSION['user'])) {
      header("Location: login.php");
      exit;
  }

  include "php/connexio.php";

  if (!isset($_SESSION['user'])) {
      echo "Session 'user' is not set!";
      exit; 
  }

  $userId = $_SESSION['user'];

  include "php/user_photo.php";
  $userPhoto = userPhoto($_SESSION['user'], $con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>LTWS</title>
  <link rel="stylesheet" href="css/paginaweb.css">
  <link rel="stylesheet" href="css/compra_e.css" />
</head>
<body>
  <header class="header">
    <div class="menu-icon" id="menuButton">☰</div>
    <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image"/></div>
    <div class="user-icon">
      <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
        <a href="perfil.php">
          <img src="<?php echo !empty($userPhoto) ? 'data:image/jpeg;base64,'.base64_encode($userPhoto) : 'img/default_avatar.png'; ?>" alt=" " style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" />
        </a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </header>

  <div class="menu" id="menu">
    <ul>
      <li><a href="index.php">Inici</a></li>
      <li><a href="perfil.php">Perfil</a></li>
      <li><a href="modificar_perfil.php">Configuració</a></li>
      <li><a href="tancar_sessio.php">Tancar Sessió</a></li>
    </ul>
  </div>

  <div class="titulo-container">
    <!-- Imagen encima del título -->
    <img src="img/thumbs_up.jpg" alt="Compra Exitosa" class="titulo-imagen">

    <div class="titulo-exitoso">
      <h1>Compra Exitosa</h1>
    </div>

    <!-- Enlace hacia la página principal -->
    <a href="index.php" class="link-home">Tornar a la pàgina d'inici</a>
  </div>

  <footer class="footer">
    <p>© 2024 LTWS</p>
  </footer>

  <script src="js/paginaweb.js"></script>
</body>
</html>
