<?php
session_start();
include "connexio.php";

$user = $_SESSION['user'];
$nomGrup = $_POST['groupName'];

$accio1 = isset($_POST['accio1']) ? $_POST['accio1'] : null;
$accio2 = isset($_POST['accio2']) ? $_POST['accio2'] : null;
$accio3 = isset($_POST['accio3']) ? $_POST['accio3'] : null;
$accio4 = isset($_POST['accio4']) ? $_POST['accio4'] : null;
$accio5 = isset($_POST['accio5']) ? $_POST['accio5'] : null;

echo $nomGrup;
echo $accio1;
echo $accio2;
echo $accio3;
echo $accio4;
echo $accio5;

$sql = "SELECT CIF FROM TREBALLAR WHERE idUsuari = '$user'";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $cif = $row['CIF'];
}

$sql = "INSERT INTO grup (nomGroup, idUsuari, CIF) 
        VALUES ('$nomGrup', '$user', '$cif')";

$result = mysqli_query($con, $sql);

$sql = "SELECT id FROM grup 
        WHERE nomGroup = '$nomGrup' AND idUsuari = '$user' AND CIF = '$cif'";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $idGrup = $row['id'];
}


if (!empty($accio1)) {
    $sql = "INSERT INTO privilegi (idGrup, idAccio) 
            VALUES ('$idGrup', '$accio1')";
    mysqli_query($con, $sql);
}

if (!empty($accio2)) {
    $sql = "INSERT INTO privilegi (idGrup, idAccio) 
            VALUES ('$idGrup', '$accio2')";
    mysqli_query($con, $sql);
}

if (!empty($accio3)) {
    $sql = "INSERT INTO privilegi (idGrup, idAccio) 
            VALUES ('$idGrup', '$accio3')";
    mysqli_query($con, $sql);
}

if (!empty($accio4)) {
    $sql = "INSERT INTO privilegi (idGrup, idAccio) 
            VALUES ('$idGrup', '$accio4')";
    mysqli_query($con, $sql);
}

if (!empty($accio5)) {
    $sql = "INSERT INTO privilegi (idGrup, idAccio) 
            VALUES ('$idGrup', '$accio5')";
    mysqli_query($con, $sql);
}


header("Location: ../perfil.php ");
?>