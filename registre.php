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
    <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image" /></div>
    <div class="user-icon">Registre</div>
  </header>

  <br><br>

  <h1>Registrar-se com <?php echo $_GET['mode'] ?></h1>

  <div class="form-container">
  <form action="php/register.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
  <!-- Campo oculto para pasar 'mode' -->
  <input type="hidden" name="mode" value="<?php echo isset($_GET['mode']) ? $_GET['mode'] : ''; ?>">

      <label for="user">Username:</label>
      <input id="user" name="user" required maxlength="64">
      <?php
        // Mostrar mensaje si existe el error en la URL
        if (isset($_GET['error']) && $_GET['error'] == 'userexists') {
          echo '<div style="color: red; margin-top: 10px;">El nom d\'usuari ja existeix.</div>';
        }
      ?>
      <br><br>
      <label for="nom">Nom:</label>
      <input id="nom" name="nom" required maxlength="64"><br><br>
      <label for="llinatges">Llinatges:</label>
      <input id="llinatges" name="llinatges" required maxlength="64"><br><br>
      <label for="adr">Adreça:</label>
      <input id="adr" name="adr" required maxlength="128"><br><br>
      <label for="ciutat">Ciutat:</label>
      <input id="ciutat" name="ciutat" required maxlength="64"><br><br>
      <label for="cp">Codi Postal:</label>
      <input id="cp" name="cp" required maxlength="16"><br><br>
      <label for="pais">País:</label>
      <select id="pais" name="pais" required>
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
      <input id="email" name="email" required maxlength="128"><br><br>
      <label for="tlf">Telèfon:</label>
      <input id="tlf" name="tlf" required maxlength="16"><br><br>
      <label for="pass">Contrassenya:</label>
      <input type="password" id="pass" name="pass" required maxlength="128"><br><br>
      <label for="fotoPerfil">Foto de perfil (Opcional):</label>
      <input type="file" id="fotoPerfil" name="fotoPerfil" accept="image/*"><br><br>


      <!-- Botón para mostrar los campos de la empresa -->
      <button type="button" id="showCompanyFields" onclick="toggleCompanyFields()">Crear una Empresa</button>

      <!-- Campos de la empresa que estarán ocultos inicialmente -->
      <div id="companyFields" style="display:none; margin-top: 20px;">
        <label for="empNom">Nom de l'empresa:</label>
        <input id="empNom" name="empNom" maxlength="64"><br><br>
        <label for="cif">CIF:</label>
        <input id="cif" name="cif" maxlength="16"><br><br>
      </div>

      <input name="Register" type="submit">
    </form>           
  </div>

  <footer class="footer">
        <p>© 2024 LTWS</p>
    </footer>

  <script src="js/register.js"></script>
  <script src="js/paginaweb.js"></script>

</body>
</html>