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
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>LTWS</title>
  <link rel="stylesheet" href="css/paginaweb.css">
  <link rel="stylesheet" href="css/login.css">
</head>

<body>
  <header class="header">
    <div class="menu-icon" id="menuButton">☰</div>
    <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image" /></div>
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


  <br><br>

  <h1>Modificar Perfil</h1>

  <div class="form-container">
  <form action="php/modifica_per.php" method="post" enctype="multipart/form-data" onsubmit="return validateForms()">
  <br>
      <label for="nom">Nom:</label>
      <input id="nom" name="nom" maxlength="64"><br><br>
      <label for="llinatges">Llinatges:</label>
      <input id="llinatges" name="llinatges" maxlength="64"><br><br>
      <label for="adr">Adreça:</label>
      <input id="adr" name="adr" maxlength="128"><br><br>
      <label for="ciutat">Ciutat:</label>
      <input id="ciutat" name="ciutat" maxlength="64"><br><br>
      <label for="cp">Codi Postal:</label>
      <input id="cp" name="cp" maxlength="16"><br><br>
      <label for="pais">País:</label>
      <select id="pais" name="pais">
        <option value="" selected>Seleccioni un país</option>
        <?php
          // Conexión a la base de datos
          include "php/connexio.php";

          // Consulta para obtener los países
          $Spais = "SELECT nom AS nomPais, codi FROM pais";
          $resultatP = mysqli_query($con, $Spais);

          if ($resultatP && mysqli_num_rows($resultatP) > 0) {
            while ($row = mysqli_fetch_assoc($resultatP)) {
              // Mostrar el país en el desplegable
              echo "<option value='".$row['codi']."'>".$row['nomPais']."</option>";
            }
          } else {
            echo "<option value=''>No hi ha països disponibles</option>";
          }
        ?>
      </select><br><br>
      <label for="email">Email:</label>
      <input id="email" name="email" maxlength="128"><br><br>
      <label for="tlf">Telèfon:</label>
      <input id="tlf" name="tlf" maxlength="16"><br><br>
      <label for="pass">Contrassenya Actual:</label>
      <input type="password" id="pass" name="pass" required maxlength="128"><br><br>
      <label for="newpass">Nova Contrassenya:</label>
      <input type="password" id="newpass" name="newpass" maxlength="128"><br><br>
      <label for="fotoPerfil">Foto de perfil (Opcional):</label>
      <input type="file" id="fotoPerfil" name="fotoPerfil" accept="image/*"><br><br>
      <?php
        // Mostrar mensaje si existe el error en la URL
        if (isset($_GET['error']) && $_GET['error'] == 'contrassenyaRepetida') {
          echo '<div style="color: red; margin-top: 10px;">Aquesta contrassenya ja l\'has utilitzada abans.</div>';
        }
      ?>
      <?php
            // Mostrar mensaje si existe el error en la URL
            if (isset($_GET['error']) && $_GET['error'] == 'contraseñaIncorrecta') {
                echo '<div style="color: red; margin-top: 10px;">La contrassenya actual es incorrecte.</div>';
            }
      ?>
      <?php
            // Mostrar mensaje si existe el error en la URL
            if (isset($_GET['error']) && $_GET['error'] == 'contraseñaRep') {
                echo '<div style="color: red; margin-top: 10px;">La contrassenya nova es la mateixa que l\'actual.</div>';
            }
      ?>
      <input name="Modificar" type="submit">
    </form>           
  </div>

  <div class="menu" id="menu">
      <ul>
        <li><a href="index.php">Inici</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="modificar_perfil.php">Configuració</a></li>
        <li><a href="tancar_sessio.php">Tancar Sessió</a></li>
      </ul>
    </div>

    <footer class="footer">
        <p>© 2024 LTWS</p>
    </footer>

  <script src="js/modifica.js"></script>
  <script src="js/paginaweb.js"></script>

</body>
</html>