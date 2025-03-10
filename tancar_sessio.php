<?php
session_start(); // Iniciar la sesión

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();
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
    
  <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image"/></div>
  </header>

  <div class="titulo-container">
    <!-- Imagen encima del título -->
    <img src="img/thumbs_up.jpg" alt="Compra Exitosa" class="titulo-imagen">

    <div class="titulo-exitoso">
      <h1>S'ha tancat la sessió</h1>
    </div>

    <!-- Enlace hacia la página principal -->
    <a href="login.php" class="link-home">Tornar a la pàgina d'inici de sessió</a>
  </div>

  <footer class="footer">
    <p>© 2024 LTWS</p>
  </footer>

  <script src="js/paginaweb.js"></script>
</body>
</html>