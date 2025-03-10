<?php
// Iniciar la sesión y obtener el CIF de la empresa del usuario actual
session_start();
include "php/connexio.php";

// Suponiendo que el CIF de la empresa se obtiene desde la sesión del usuario
$user = $_SESSION['user'];  // El CIF de la empresa está almacenado en la sesión

if (!$user) {
    echo "No se ha encontrado la empresa asociada al usuario.";
    exit;
}

// Consulta para obtener el CIF de la empresa asociada al usuario
$SUsuario = "SELECT CIF FROM treballar WHERE idUsuari = '$user'";
$resultatUsuario = mysqli_query($con, $SUsuario);

if ($resultatUsuario && mysqli_num_rows($resultatUsuario) > 0) {
    $usuarioData = mysqli_fetch_assoc($resultatUsuario);
    $usuarioCIF = $usuarioData['CIF'];
} else {
    echo "No se ha encontrado el CIF de la empresa.";
    exit;
}


// Consulta SQL para obtener los grupos asociados a la empresa del usuario
$SGrupos = "SELECT id, nomGroup FROM grup WHERE CIF = '$usuarioCIF'";
$resultatGrupos = mysqli_query($con, $SGrupos);

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>LTWS</title>
  <link rel="stylesheet" href="css/paginaweb.css">
</head>

<body>
  <header class="header">
    <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image" /></div>
  </header>

  <h1>Grups disponibles</h1>

  <div class="form-container">
  <form action="" method="POST">
    <label for="grupo">Selecciona un grup:</label>
    <select name="grupo" id="grupo" required>
        <option value="">Seleccioni un grup</option>
        <?php
        // Mostrar los grupos en el select
        if ($resultatGrupos && mysqli_num_rows($resultatGrupos) > 0) {
            $grupos = [];
            while ($row = mysqli_fetch_assoc($resultatGrupos)) {
                $grupos[] = $row;
            echo "<option value='" . $row['id'] . "'>" . $row['nomGroup'] . "</option>";
            }
        } else {
            echo "<option value=''>No hi ha grups disponibles</option>";
        }
        ?>
    </select>
    <br><br>
    <input type="submit" value="Afegir" onclick="this.form.action='add_pers_grupo.php';">
    <input type="submit" value="Eliminar" onclick="this.form.action='elim_pers_grupo.php';">
    <button type="button" onclick="window.location.href='perfil.php'">Tornar al perfil</button>

  </form>
  </div>
</body>

</html>
