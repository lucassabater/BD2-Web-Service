<?php
session_start();
// Connexió a la base de datos
include "connexio.php";
var_dump($_POST);  // Esto imprimirá todo el contenido de $_POST
// Obtener los valores del formulario
$user = $_POST['user'];
$nom = $_POST['nom'];
$llinatges = $_POST['llinatges'];
$adr = $_POST['adr'];
$ciutat = $_POST['ciutat'];
$pais = $_POST['pais'];
$email = $_POST['email'];
$tlf = $_POST['tlf'];
$pass = $_POST['pass'];
$cp = $_POST['cp'];
$mode = $_POST['mode'];
$fotoPerfil = null;

// Comprobamos si se subió una foto y si no hay errores en la carga
if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
    // Obtenemos el tipo de archivo
    $fileType = mime_content_type($_FILES['fotoPerfil']['tmp_name']);
    echo "Tipo de archivo: $fileType"; // Depuración

    // Leer el contenido del archivo si se cargó correctamente
    $fotoPerfil = file_get_contents($_FILES['fotoPerfil']['tmp_name']);
} else {
    // Si no se subió archivo, no hacemos nada y $fotoPerfil queda como null
    echo "No se ha subido una foto.";
}

// Comprobar si el usuario existe en la tabla de 'persona'
$userExists = "SELECT * FROM persona p WHERE p.username = '$user'";
$resultatUs = mysqli_query($con, $userExists);

// Si no existe, proceder a crear el nuevo usuario
if (mysqli_num_rows($resultatUs) == 0) {
    // Comprobamos si se subió una foto y si no hay errores en la carga
    if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
        // Obtenemos el tipo de archivo
        $fileType = mime_content_type($_FILES['fotoPerfil']['tmp_name']);
        echo "Tipo de archivo: $fileType"; // Depuración

        // Leer el contenido del archivo si se cargó correctamente
        $fotoPerfil = file_get_contents($_FILES['fotoPerfil']['tmp_name']);
    } else {
        // Si no se subió archivo, no hacemos nada y $fotoPerfil queda como null
        echo "No se ha subido una foto.";
    }

    $passCrypt = password_hash($pass, PASSWORD_DEFAULT);

    // Verificar si la ciudad ya existe en la base de datos
    $checkCiutat = "SELECT c.codiPostal
                        FROM ciutat c
                        WHERE c.codiPostal = '$cp'";
    $resultCiutat = mysqli_query($con, $checkCiutat);
    if (mysqli_num_rows($resultCiutat) == 0) {
        // Si no existe la ciudad, insertarla
        $insertCiutat = "INSERT INTO ciutat (codiPostal, nom, codiPais) VALUES ('$cp', '$ciutat', '$pais')";
        if (!mysqli_query($con, $insertCiutat)) {
            // Si hubo un error al insertar la ciudad, mostrar error
            header("Location: ../registre.php?mode=$mode&error=errorCiutat");
            exit();
        }
    }

    // Preparar la declaración SQL
    $insertPersona = "INSERT INTO persona (username, nom, llinatges, adreça, contrassenyaActual, foto, codiPostal) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Preparar la declaración
    $stmt = mysqli_prepare($con, $insertPersona);
    if (!$stmt) {
        echo "Error al preparar la consulta: " . mysqli_error($con);
        exit();
    }

    // Vincular los parámetros (5 cadenas y 1 parámetro BLOB)
    mysqli_stmt_bind_param($stmt, 'sssssss', $user, $nom, $llinatges, $adr, $passCrypt, $fotoPerfil, $cp);

    // Enviar el archivo binario al parámetro correspondiente (en este caso, 'foto' es el 6º parámetro)
    mysqli_stmt_send_long_data($stmt, 5, $fotoPerfil); // Índice 5 porque es el último parámetro

    // Ejecutar la declaración
    if (mysqli_stmt_execute($stmt)) {
        echo "Usuario insertado correctamente.";
    } else {
        echo "Error al insertar el usuario: " . mysqli_error($con);
    }

    // Cerrar la declaración
    mysqli_stmt_close($stmt);
    if ($mode =="usuari") {
        $insert = "INSERT INTO usuari (id, idUsuariCreador) VALUES ('$user', NULL)";
    } else if ($mode == "personal") {
        $insert = "INSERT INTO personal (id) VALUES ('$user')";
    } else {
        // Si el modo no es válido, redirigir con error
        header("Location: ../registre.php?mode=$mode&error=modeerror");
        exit();
    }

    if (!mysqli_query($con, $insert)) {
        // Si hubo un error al insertar en la tabla 'usuari' o 'personal', redirigir con error
        header("Location: ../registre.php?mode=$mode&error=errorUsuari");
        exit(); 
    }
    
    $insertViure = "INSERT INTO viure (codiPostal, username) 
                    VALUES ('$cp', '$user')";
    if (!mysqli_query($con, $insertViure)) {
        // Si hubo un error al insertar la ciudad, mostrar error
        header("Location: ../registre.php?mode=$mode&error=errorViure");
        exit();
    }

    // Insertar el teléfono
    $insertTelefon = "INSERT INTO telefon (telefon, username) VALUES ('$tlf', '$user')";
    $telefonInsertado = mysqli_query($con, $insertTelefon);

    // Insertar el correo electrónico
    $insertCorreu = "INSERT INTO email (email, username) VALUES ('$email', '$user')";
    $correuInsertado = mysqli_query($con, $insertCorreu);

    // Verificamos si se debe crear una empresa
    if (!empty($_POST['empNom']) && !empty($_POST['cif'])) {
        $empNom = mysqli_real_escape_string($con, $_POST['empNom']);
        $cif = mysqli_real_escape_string($con, $_POST['cif']);
         // Verificar si la ciudad ya existe en la base de datos
        $checkEmpr = "SELECT e.CIF
                        FROM empresa e
                        WHERE e.CIF = '$cif'";
        $resultEmprs = mysqli_query($con, $checkEmpr);
        if (mysqli_num_rows($resultEmprs) == 0) {
            // Insertar la empresa en la base de datos
            $insertEmpresa = "INSERT INTO empresa (CIF, nom) 
                            VALUES ('$cif', '$empNom')";
            $empresaInsertada = mysqli_query($con, $insertEmpresa);
            if (!$empresaInsertada) {
                header("Location: ../registre.php?mode=$mode&error=errorEmpresa");
            }
            $sql = "CALL DescompteEmpreses()";
            if (!($con->query($sql) === TRUE)) {
                // Si hubo un error en algún insert, redirigir con un error general
                header("Location: ../registre.php?mode=$mode&error=errorProc");
                exit();
            }
        }

        // Si la empresa se insertó correctamente, crear relaciones
            $insertTreballar = "INSERT INTO treballar (idUsuari, CIF) 
                                VALUES ('$user', '$cif')";
            if (mysqli_query($con, $insertTreballar)) {
                // Crear grupo administradores de la empresa
                $insertGrup = "INSERT INTO grup (nomGroup, idUsuari, CIF) 
                               VALUES ('Admins', '$user', '$cif')";

                if (mysqli_query($con, $insertGrup)) {
                    // Obtener el idGrup generado por la inserción anterior
                    $idGrup = mysqli_insert_id($con);
                            
                    $insertPertanyer = "INSERT INTO pertanyer (idUsuari, idGrup) 
                                        VALUES ('$user', '$idGrup')";
                    if (mysqli_query($con, $insertPertanyer)) {
                        for ($i=1; $i<6; $i++) {
                            $insertPriv = "INSERT INTO privilegi (idGrup, idAccio) 
                                        VALUES ('$idGrup', $i)";
                            if (!mysqli_query($con, $insertPriv)) {
                                // Si hubo un error al insertar los privilegios, redirigir con error
                                header("Location: ../registre.php?mode=$mode&error=errorPrivilegis");
                                exit();
                            }
                        }
                    } else {
                        // Si hubo un error al insertar la relación entre el usuario y el grupo
                        header("Location: ../registre.php?mode=$mode&error=errorPertanyer");
                        exit();
                    }
                } else {
                    // Si hubo un error al insertar el grupo
                    header("Location: ../registre.php?mode=$mode&error=errorGrup");
                    exit();
                }
            } else {
                // Si hubo un error al insertar la relación entre el usuario y la empresa
                header("Location: ../registre.php?mode=$mode&error=errorTreballar");
                exit();
            }
    }

    // Comprobamos si todos los inserts fueron correctos (si no, no detenemos el proceso)
    if ($telefonInsertado && $correuInsertado) {
        // Si todo está bien, redirigir al usuario
        header("Location: ../login.php?error=register");
        exit();
    } else {
        // Si hubo un error en algún insert, redirigir con un error general
        header("Location: ../registre.php?mode=$mode&error=errorInsert");
        exit();
    }
} else {
    // Si el usuario ya existe, redirigir al login
    header("Location: ../registre.php?mode=$mode&error=userexists");
    exit();
}



?>