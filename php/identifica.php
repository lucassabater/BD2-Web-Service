<?php
session_start();

// Conexión a la base de datos
include "connexio.php";

// Obtener la contraseña encriptada desde la base de datos
$user = $_POST['user'];
$pass = $_POST['pass'];

// Consultar la base de datos para obtener la contraseña encriptada
$userExists = "SELECT * 
               FROM persona p
               WHERE p.username = '$user'";

$resultatUs = mysqli_query($con, $userExists);

// Comprobar si el usuario existe
if ($resultatUs && mysqli_num_rows($resultatUs) > 0) {
    $row = mysqli_fetch_assoc($resultatUs);
    $passbd = $row['contrassenyaActual'];  // La contraseña encriptada de la base de datos

    // Verificar la contraseña usando password_verify
    if (password_verify($pass, $passbd)) {
        // Contraseña correcta, redirigir al usuario
        // Recoger parámetros
        $_SESSION['user'] = $user;
        // Consultar la base de datos para obtener la contraseña encriptada
        $selcUs = "SELECT * 
                        FROM persona p
                        JOIN usuari u
                        ON p.username = u.id
                        WHERE p.username = '$user'";
        $resultatUs = mysqli_query($con, $selcUs);
        if ($resultatUs && mysqli_num_rows($resultatUs) > 0) {
            $_SESSION['mode'] = "usuari";
        } else if ($resultatUs && mysqli_num_rows($resultatUs) == 0) {
            $_SESSION['mode'] = "personal";
        } else {
            header("Location: ../login.php?error=notfound");
            exit();
        }
        
        header("Location: ../index.php");
        exit();
    } else {
        // Contraseña incorrecta
        header("Location: ../login.php?error=notfound");
        exit();
    }
} else {
    // Usuario no encontrado
    header("Location: ../login.php?error=notfound");
    exit();
}
?>