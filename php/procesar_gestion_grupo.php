<?php
include "connexio.php";

if ($_POST['action'] === 'Añadir') {
    $groupId = $_POST['groupId'];
    $userId = $_POST['userToAdd'];

    $sql = "INSERT INTO grup_usuari (idGrup, idUsuari) VALUES ('$groupId', '$userId')";
    mysqli_query($con, $sql);
} elseif ($_POST['action'] === 'Eliminar') {
    $groupId = $_POST['groupId'];
    $userId = $_POST['userToRemove'];

    $sql = "DELETE FROM grup_usuari WHERE idGrup = '$groupId' AND idUsuari = '$userId'";
    mysqli_query($con, $sql);
}

header("Location: ../gestionar_grupo.php");
?>
