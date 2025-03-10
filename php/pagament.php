<?php
session_start();  // Start the session

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include "connexio.php";

if (!isset($_SESSION['user'])) {
    echo "Session 'user' is not set!";
    exit;
}

$userId = $_SESSION['user'];

// Obtenemos los datos del formulario de atributos del producto
$soSelected = $_POST['soSelected'];
$ramSelected = $_POST['ramSelected'];
$cpuSelected = $_POST['cpuSelected'];
$discSelected = $_POST['discSelected'];
$xarxaSelected = $_POST['xarxaSelected'];
$rdbmsSelected = $_POST['rdbmsSelected'];
$storageSelected = $_POST['storageSelected'];

// Obtenemos el tipo de producto
$producte = $_POST['producte'];

// Obtenemos los datos de la IP solo si el producto es una Máquina Virtual
if ($producte == 1) {
    $ipSelected = $_POST['direccioIP'];
    $mascaraSelected = $_POST['mascaraSubxarxa'];
    $gatewaySelected = $_POST['gateway'];
    $dnsSelected = $_POST['dns'];
}

// Obtenemos los datos del formulario de pagamiento
$numTar = $_POST['numTar'];
$titular = $_POST['titular'];
$emissor = $_POST['emissor'];
$dataV = $_POST['dataV'];
$numTarCrypt = password_hash($numTar, PASSWORD_DEFAULT);

// Comprobar si la tarjeta ya existe en la base de datos
$checkCardQuery = "SELECT * FROM targeta WHERE num = '".$numTar."'";
$result = mysqli_query($con, $checkCardQuery);

if (mysqli_num_rows($result) > 0) {
    // Si encuentra la tarjeta, verificar si los datos coinciden
    $cardData = mysqli_fetch_assoc($result);

    if (!($cardData['nomTitular'] == $titular && $cardData['emissor'] == $emissor && $cardData['dataCaducitat'] == $dataV)) {
        // Si los datos no coinciden, redirigir con un mensaje de error
        header("Location: ../producto.php?product=$producte&error=cardDataMismatch");
        exit();
    }
} else {
    // Si no encuentra la tarjeta, insertar los nuevos datos en la base de datos
    $insertTargeta = "INSERT INTO targeta (num, nomTitular, emissor, dataCaducitat) VALUES ('".$numTar."', '".$titular."', '".$emissor."', '".$dataV."')";     
    if (!mysqli_query($con, $insertTargeta)) {
        header("Location: ../compra.php?error=dbInsertFailed");
        exit();
    }
}

$Scomprovar = "SELECT * FROM enregistrar WHERE numTargeta = '".$numTar."' AND idUsuari = '".$_SESSION['user']."'";
$result = mysqli_query($con, $Scomprovar);
if (!($result)) {
    // Si hay un error al insertar, redirigir a la página de login con un mensaje de error
    header("Location: ../compra.php?error=dbInsertFailed");
    exit();
} else if (mysqli_num_rows($result) == 0) {
    $insertEnr = "INSERT INTO enregistrar (idUsuari, numTargeta) VALUES ('".$_SESSION['user']."','".$numTar."')";
    if (!mysqli_query($con, $insertEnr)) {
        header("Location: ../compra.php?error=dbInsertFailed");
        exit();
    }
}

// Insertamos el producto en la cuenta del cliente
$insertProducte = "INSERT INTO inst_producte (dataCompra, idCopia, idProducte, numTargeta, idUsuari) 
                   VALUES (NOW(), NULL, '".$producte."', '".$numTar."', '".$_SESSION['user']."')";

if (!mysqli_query($con, $insertProducte)) {
    header("Location: ../compra.php?error=dbInsertFailed");
    exit();
}

$idInst = mysqli_insert_id($con);

if ($producte == 1) {
    // Recogemos bien los datos del Sistema Operativo, separando nombre y versión
    $soData = json_decode($soSelected, true);
    $soNom = $soData['nom'];
    $soVersio = $soData['versio'];

    // La RAM ya está guardada en ramSelected

    // Decodificamos las cpus
    $cpuArray = json_decode($cpuSelected);
    if (is_array($cpuArray)) {
        // Creamos un array que guarde todas las CPUs seleccionadas
        $cpus = [];

        // Llenamos el array cpus con los valores del array original
        foreach ($cpuArray as $index => $cpu) {
            $cpus[] = $cpu;
        }
    } else {
        echo "Error al decodificar el JSON de CPUs.";
    }

    // Recogemos bien los datos de los Discos, separando nombre, versión, y disco
    $discArray = json_decode($discSelected);
    if (is_array($discArray)) {
        // Iteramos sobre el array para acceder a cada disco
        foreach ($discArray as $index => $disc) {
            // Asignamos los nombres y versiones de cada uno
            $nom = $disc->nom;      
            $versio = $disc->versio; 
        }
    } else {
        echo "Error al decodificar el JSON de Discos.";
    }

    // Recogemos bien los datos del Sistema Operativo, separando nombre y versión
    $xarxaArray = json_decode($xarxaSelected);
    if (is_array($xarxaArray)) {
        // Creamos un array que guarde todas las Tarjetas de Red seleccionadas
        $xarxes = [];

        // Llenamos el array xarxes con los valores del array original
        foreach ($xarxaArray as $index => $xarxa) {
            $xarxes[] = $xarxa;
        }
    } else {
        echo "Error al decodificar el JSON de Tarjetas de Red.";
    }

    // Empezamos a insertar la Máquina Virtual con los atributos seleccionados
    $insertMaqVirQuery = "INSERT INTO MAQUINA_VIRTUAL (id, IP, idRAM, nomSO, nombreVersio, mascaraSubxarxa, gateWay, servidorDNS)
                            VALUES ('$idInst', '$ipSelected', '$ramSelected', '$soNom', '$soVersio', '$mascaraSelected', '$gatewaySelected', '$dnsSelected')";

    if (!mysqli_query($con, $insertMaqVirQuery)) {
        header("Location: ../pagament.php?error=dbInsertFailed");
        exit();
    }

    // Insertamos en la tabla TENIR_XARXA, una inserción por Tarjeta de Red seleccionada
    foreach ($xarxes as $xarxa) {
        if ($xarxa == 'none') {
            break;
        }
        $insert_TenirXarxa = "INSERT INTO TENIR_XARXA (idMaqVir, idTXarxa)
                                    VALUES ($idInst, $xarxa)";
            
        if (!mysqli_query($con, $insert_TenirXarxa)) {
            header("Location: ../pagament.php?error=dbInsertFailedXarxa");
            exit();
        }
    }

    // Insertamos en la tabla TENIR_DISC, una inserción por disco seleccionado
    foreach ($discArray as $disc) {
        $nom = $disc->nom;
        $insert_TenirDisc = "INSERT INTO TENIR_DISC (idMaqVir, idDisc)
                                VALUES ($idInst, $nom)";
        
        if (!mysqli_query($con, $insert_TenirDisc)) {
            header("Location: ../pagament.php?error=dbInsertFailedDisc");
            exit();
        }
    }

    // Insertamos en la tabla TENIR_CPU, una inserción por CPU seleccionada
    foreach ($cpus as $cpu) {
        $insert_TenirXarxa = "INSERT INTO TENIR_CPU (idMaqVir, idCPU)
                                VALUES ($idInst, $cpu)";
        
        if (!mysqli_query($con, $insert_TenirXarxa)) {
            header("Location: ../pagament.php?error=dbInsertFailedCPU");
            exit();
        }
    }

} else if ($producte == 2) {
    // Recogemos bien los datos del RDBMS, separando nombre y versión
    $rdbmsData = json_decode($rdbmsSelected, true);
    $rdbmsNom = $rdbmsData['nom'];
    $rdbmsVersio = $rdbmsData['versio'];

    // Recogemos bien los datos del almacenamiento, separando nombre y versión
    $storageData = json_decode($storageSelected, true);
    $storageNom = $storageData['nom'];
    $storageVersio = $storageData['versio'];
    

    // Insertamos la Base de Datos con los atributos seleccionados
    $insertBD = "INSERT INTO ACCES_BD (id, idEm, idTipusEm, idRDBMS, nombreVersio) 
                 VALUES ('$idInst', '$storageNom', '$storageVersio', '$rdbmsNom', '$rdbmsVersio')";
    if (!mysqli_query($con, $insertBD)) {
        header("Location: ../pagament.php?error=dbInsertFailedBD");
        exit();
    }
    
} else if ($producte == 3) {
    // Recogemos bien los datos del almacenamiento, separando nombre y versión
    $storageData = json_decode($storageSelected, true);
    $storageNom = $storageData['nom'];
    $storageVersio = $storageData['versio'];

    $insertEmNu = "INSERT INTO EMMAGATZEMATGE_NUVOL (id, idEm, idTipusEm)
                   VALUES ('$idInst', '$storageNom', '$storageVersio')";
    if (!mysqli_query($con, $insertEmNu)) {
        header("Location: ../pagament.php?error=dbInsertFailedBD");
        exit();
    }
}
header("Location: ../compra_exitosa.php");
exit();
?>