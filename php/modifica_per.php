<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}


$userId = $_SESSION['user'];

// Connexió a la base de datos
include "connexio.php";

$sql = "SELECT p.nom, p.llinatges, p.adreça, p.contrassenyaActual, p.foto, p.codiPostal 
        FROM persona p
        WHERE username = '".$userId."'";
$result = mysqli_query($con, $sql);
$userData = mysqli_fetch_assoc($result);

// Connexió a la base de datos

// Obtener los valores del formulario
$nom = isset($_POST['nom']) ? $_POST['nom'] : '';
$llinatges = isset($_POST['llinatges']) ? $_POST['llinatges'] : '';  
$adr = isset($_POST['adr']) ? $_POST['adr'] : '';           
$ciutat = isset($_POST['ciutat']) ? $_POST['ciutat'] : ''; 
$pais = isset($_POST['pais']) ? $_POST['pais'] : '';
$cp = isset($_POST['cp']) ? $_POST['cp'] : '';         
$email = isset($_POST['email']) ? $_POST['email'] : '';     
$tlf = isset($_POST['tlf']) ? $_POST['tlf'] : '';           
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';         
$newpass = isset($_POST['newpass']) ? $_POST['newpass'] : ''; 
$fotoPerfil = null;

// Comprobar si el usuario existe en la tabla de 'persona'
$sql = "SELECT p.contrassenyaActual FROM persona p WHERE p.username = '".$userId."'";
$passActual = mysqli_query($con, $sql);
$passActual = mysqli_fetch_assoc($passActual);
$passActualCrypt = password_hash($pass, PASSWORD_DEFAULT);
// Si no existe, proceder a crear el nuevo usuario
if (password_verify($pass, $passActual['contrassenyaActual'])) {

    if (($pass == $newpass)) {
        header("Location: ../modificar_perfil.php?error=contraseñaRep");
        exit();
    }
    // Verificación del código postal (ciudad)
    if (!empty($cp) ) {
        // Verificar si la ciudad ya existe en la base de datos
        $checkCiutat = "SELECT c.codiPostal FROM ciutat c WHERE c.codiPostal = '$cp'";
        $resultCiutat = mysqli_query($con, $checkCiutat);

        if (mysqli_num_rows($resultCiutat) == 0) {
            // Si no existe la ciudad, insertarla
            $insertCiutat = "INSERT INTO ciutat (codiPostal, nom, codiPais) VALUES ('$cp', '$ciutat', '$pais')";
            if (!mysqli_query($con, $insertCiutat)) {
                header("Location: ../modificar_perfil.php?error=errorCiutat");
                exit();
            }
        }

        $checkViure = "SELECT v.codiPostal FROM viure v WHERE v.codiPostal = '$cp' AND v.username = '$userId'";
        $resultViure = mysqli_query($con, $checkViure);

        if(mysqli_num_rows($resultViure) == 0){
            // Actualizar la ciudad (código postal) en la tabla 'viure'
            $updateViure = "INSERT INTO viure (codiPostal, username) VALUES ( '$cp', '$userId')";
            if (!mysqli_query($con, $updateViure)) {
                header("Location: ../modificar_perfil.php?error=errorViureUpdate");
                exit();
            }
        } 
    }

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


    // Insertar el nuevo usuario en la tabla persona
    $updateFields = [];
    if ($nom != $userData['nom'] && $nom != '') {
        $updateFields[] = "nom = '$nom'";
    }
    if ($llinatges != $userData['llinatges']&& $llinatges != '') {
        $updateFields[] = "llinatges = '$llinatges'";
    }
    if ($adr != $userData['adreça']&& $adr != '') {
        $updateFields[] = "adreça = '$adr'";
    }
    if ($cp != $userData['codiPostal']&& $cp != '') {
        $updateFields[] = "codiPostal = '$cp'";
    }

    if ($newpass != '' && password_verify($pass, $userData['contrassenyaActual'])) {

        // Usuario actual
        $username = $_SESSION['user']; 
    
        // Verifica si la nueva contraseña ya está en el historial
        $checkHistorial = "SELECT contrassenya FROM HISTORIAL_CONTRASSENYA WHERE username = '$username'";
        $result = mysqli_query($con, $checkHistorial);
    
        while ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($newpass, $row['contrassenya'])) {
                // La contraseña nueva ya está en el historial
                header("Location: ../modificar_perfil.php?error=contrassenyaRepetida");
                exit;
            }
        }
    
        // Inserta la contraseña actual en el historial antes de actualizarla
        $oldPass = $userData['contrassenyaActual'];
        $insertHistorial = "INSERT INTO HISTORIAL_CONTRASSENYA (contrassenya, username) VALUES ('$oldPass', '$username')";
        mysqli_query($con, $insertHistorial); // Ejecuta la inserción
    
        // Actualiza la contraseña actual
        $newPassCrypt = password_hash($newpass, PASSWORD_DEFAULT);
        $updateFields[] = "contrassenyaActual = '$newPassCrypt'";
    }
    
        // Si hay campos para actualizar, ejecutar el update
        if (count($updateFields) > 0) {
            $updatePersona = "UPDATE persona SET " . implode(", ", $updateFields) . " WHERE username = '".$userId."'";
            if (!mysqli_query($con, $updatePersona)) {
                header("Location: ../modificar_perfil.php?error=errorPersonaUpdate");
                exit();
            }
        }

        if($fotoPerfil != $userData['foto'] && $fotoPerfil!=null){
            $updatePersona = "UPDATE persona SET foto=? WHERE username = ?";

        $stmt = mysqli_prepare($con, $updatePersona);
        if (!$stmt) {
            die("Error al preparar la consulta: " . mysqli_error($con));
        }

        // Vincular los parámetros (binario para 'foto' y string para 'username')
        mysqli_stmt_bind_param($stmt, "bs", $fotoPerfil, $userId);

        // Enviar los datos binarios
        mysqli_stmt_send_long_data($stmt, 0, $fotoPerfil);

        // Ejecutar la consulta
        if (!mysqli_stmt_execute($stmt)) {
            header("Location: ../modificar_perfil.php?error=errorPersonaUpdate");
            exit();
        }

        // Cerrar la declaración
        mysqli_stmt_close($stmt);

        echo "Foto actualizada correctamente.";

        }

        if (!empty($email)) {
            // Verificar si el mail ya existe en la base de datos
            $checkCorreu = "SELECT e.email 
                            FROM email e
                            WHERE e.email = '$email' && e.username = '$userId'";
            $resultCorreu = mysqli_query($con, $checkCorreu);

            if (mysqli_num_rows($resultCorreu) == 0) {
                $insertCorreu = "INSERT INTO email (email, username) VALUES ('$email', '$userId')";
                if (!mysqli_query($con, $insertCorreu)) {
                    header("Location: ../modificar_perfil.php?error=errorEmailUpdate");
                    exit();
                }
            }
        }
            // Verificación del teléfono
        if (!empty($tlf)) {
            // Verificar si el telefono ya existe en la base de datos
            $checkTlf = "SELECT t.telefon 
                        FROM telefon t
                        WHERE t.telefon = '$tlf' && t.username = '$userId'";
            $resultTlf= mysqli_query($con, $checkTlf);

            if(mysqli_num_rows($resultTlf) == 0){
                $insertTelefon = "INSERT INTO telefon (telefon, username) VALUES ('$tlf', '$userId')";
                if (!mysqli_query($con, $insertTelefon)) {
                    header("Location: ../modificar_perfil.php?error=errorTelefonoUpdate");
                    exit();
                }
            }
        }
    } else {
        // Si hubo un error al insertar en la tabla 'persona', redirigir con error
        header("Location: ../modificar_perfil.php?error=contraseñaIncorrecta");
        exit();
    }
    header("Location: ../perfil.php");
?>
