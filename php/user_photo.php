<?php
//user_photo.php
function userPhoto($userId, $con) {
    // Obtener los datos de la foto del usuario desde la base de datos
    $sql = "SELECT foto FROM persona WHERE username = '$userId'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $userData = mysqli_fetch_assoc($result);
        return $userData['foto']; // Retorna la foto del usuario
    } else {
        return null; // Si no se encuentra la foto, retorna null
    }
}
?>