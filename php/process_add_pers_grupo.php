<?php
session_start();
include "connexio.php";
// Procesar la adición de usuarios al grupo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuarioAAgregar = $_POST['user']; // ID del usuario a agregar
    $grupoSeleccionado = $_POST['grupo']; // ID del grupo seleccionado

    if (!empty($usuarioAAgregar)) {
        // Agregar el usuario al grupo
        $SAgregarUsuario = "INSERT INTO pertanyer (idUsuari, idGrup) 
                            VALUES ('$usuarioAAgregar', '$grupoSeleccionado')";

        if (mysqli_query($con, $SAgregarUsuario)) {
            echo "Usuario agregado correctamente.";
            header("Location: ../gestionar_grupo.php");
        } else {
            echo "Error al agregar el usuario: " . mysqli_error($con);
            header("Location: ../gestionar_grupo.php");
        }
    } else {
        echo "No se ha seleccionado ningún usuario para agregar.";
        header("Location: ../gestionar_grupo.php");
    }

    header("Location: ../gestionar_grupo.php");
}

?>