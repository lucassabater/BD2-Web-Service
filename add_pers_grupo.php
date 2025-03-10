<?php
// Iniciar la sesión y obtener el CIF de la empresa del usuario actual
session_start();
include "php/connexio.php";

// Comprobar si se ha recibido el grupo seleccionado desde la página anterior
if (isset($_POST['grupo']) && !empty($_POST['grupo'])) {
    $grupoSeleccionado = $_POST['grupo'];
} else {
    echo "No se ha seleccionado un grupo.";
    exit;
}

$SGrupo = "SELECT nomGroup FROM grup WHERE id = '$grupoSeleccionado'";
$resultatGrupo = mysqli_query($con, $SGrupo);

if ($resultatGrupo && mysqli_num_rows($resultatGrupo) > 0) {
    $grupoData = mysqli_fetch_assoc($resultatGrupo);
    $nombreGrupo = $grupoData['nomGroup'];
} else {
    echo "No se ha encontrado el grupo seleccionado.";
    exit;
}



// Obtener el CIF de la empresa del usuario actual
$user = $_SESSION['user'];  // El ID del usuario está en la sesión


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

// Consulta SQL para obtener los usuarios de la empresa que NO están en el grupo seleccionado
$SUsuariosNoEnGrupo = "SELECT DISTINCT t.idUsuari
FROM TREBALLAR t
WHERE t.CIF = '$usuarioCIF'
  AND t.idUsuari NOT IN (
    SELECT p.idUsuari
    FROM PERTANYER p
    INNER JOIN GRUP g ON p.idGrup = g.id
    WHERE g.CIF = '$usuarioCIF' AND g.nomGroup = '$nombreGrupo'
  );
";

$resultatUsuariosNoEnGrupo = mysqli_query($con, $SUsuariosNoEnGrupo);
$usuariosNoEnGrupo = [];

if ($resultatUsuariosNoEnGrupo && mysqli_num_rows($resultatUsuariosNoEnGrupo) > 0) {
    while ($row = mysqli_fetch_assoc($resultatUsuariosNoEnGrupo)) {
        $usuariosNoEnGrupo[] = $row; // Almacenar los usuarios
    }
} else {
    $usuariosNoEnGrupo = []; // Si no hay usuarios, se devuelve un array vacío
}

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

  <h1>Afegir Usuaris al Grup: <?php echo htmlspecialchars($nombreGrupo); ?></h1>

  <div class="form-container">

      <?php
      // Verificar si hay usuarios disponibles para agregar
      if (!empty($usuariosNoEnGrupo)) {
          foreach ($usuariosNoEnGrupo as $usuario) {
            echo '<form action="php/process_add_pers_grupo.php" method="POST">';
            echo '<select name="grupo" id="grupo" hidden>';
            echo '<option value="' . htmlspecialchars($grupoSeleccionado) . '" selected>';
            echo htmlspecialchars($grupoSeleccionado);
            echo '</option>';
            echo '</select>';
              echo '<input type="hidden" name="user" value="' . $usuario['idUsuari'] . '">';
              echo '<label>' . htmlspecialchars($usuario['idUsuari']) . '</label><br>';
            echo '<br>';
            echo '<input type="submit" value="Afegir">';
            echo '</form>';
          }
      } else {
          echo "No hi ha usuaris disponibles per afegir a aquest grup.";
      }
      ?>
      
  </div>
<div style="text-align: center;">
    <button type="button" onclick="window.location.href='gestionar_grupo.php'">Tornar</button>
</div>

</body>

</html>
