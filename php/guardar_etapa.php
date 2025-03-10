<?php
session_start();  // Inicia la sesión

var_dump($_POST); 
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include "connexio.php"; 

$etapaId = $_POST['etapa'];
$productId = $_POST['producte'];
// Verificamos si se recibió el ID de la etapa y el ID del producto
if ($etapaId>0) {
    $etapaId = $_POST['etapa'];

    // Validar el ID de la etapa (no debe estar vacío)
    if (!empty($etapaId)) {
        $etapaAnterior= "SELECT idEtapa FROM producte WHERE id = $productId";
        $result = mysqli_query($con, $etapaAnterior);
        $etapaAnterior = mysqli_fetch_assoc($result);
        $idEtapaAnterior = $etapaAnterior['idEtapa'];
        
        // Comprobar si el registro ya existe en la tabla anterior
        $checkExistence = "SELECT COUNT(*) AS count FROM anterior WHERE idProducte = $productId AND idEtapa = $idEtapaAnterior";
        $resultCheck = mysqli_query($con, $checkExistence);
        $existenceData = mysqli_fetch_assoc($resultCheck);

        if ($existenceData['count'] == 0) {
            // Solo insertar si no existe
            $uptadeProd = "INSERT INTO anterior (idProducte, idEtapa) VALUES ($productId, $idEtapaAnterior)";
            $result = mysqli_query($con, $uptadeProd);

            if (!$result) {
                // Si hay un error al insertar, redirigir con un mensaje de error
                header("Location: ../producto_personal.php?product=$productId&error=dbInsertFailed");
                exit();
            }
        }
        
        // Actualizamos la etapa del producto en la base de datos
        $uptadeProd = "UPDATE producte SET idEtapa = '$etapaId' WHERE id = $productId";
        $result = mysqli_query($con, $uptadeProd);
        if (!($result)) {
            // Si hay un error al insertar, redirigir a la página de login con un mensaje de error
            header("Location: ../producto_personal.php?product=$productId&error=dbInsertFailed");
            exit();
        }
        
        $result = mysqli_query($con, $uptadeProd);
        header("Location: ../producto_personal.php?product=$productId");
        exit();
    } else {
        echo "Per favor, selecciona una etapa vàlida.";
    }
} else {
    echo "Dades invàlides";
}
$con->close();
?>
