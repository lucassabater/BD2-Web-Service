<?php

session_start();  // Start the session

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include "php/connexio.php";

if (!isset($_SESSION['user'])) {
    echo "Session 'user' is not set!";
    exit;  // Stop execution if 'user' session is not set
}

$userId = $_SESSION['user'];

// Obtenim l'ID del producte amb la url (per exemle, producto.php?product=1)
$productId = isset($_GET['product']) ? $_GET['product'] : null;

if ($productId) {
    // Obtenemos los detalles del producto desde la base de datos
    $sql = "SELECT p.nom, p.descripcio, p.idCategoria, c.nom AS nomCategoria, p.foto, e.nom AS nomEtapa
                FROM PRODUCTE p
                JOIN CATEGORIA c ON p.idCategoria = c.id
                JOIN ETAPA e ON p.idEtapa = e.id
                WHERE p.id = ?";
    // Preparamos la consulta
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Guardamos los detalles del producto a $product
        $product = $result->fetch_assoc();
    } else {
        // Si la consulta no tiene resultado, significa que el producto no se ha encontrado
        echo "Producte no disponible.";
        exit;
    }

    $stmt->close();

    // Obtenemos los atributos según el tipo de producto
    $atributs = [];

    // Declaramos la variable del precio total
    $preuTotal = 0;

    $sql_etapes = "SELECT id, nom FROM ETAPA";
    $atributs['etapes'] = $con->query($sql_etapes);

    // Si es una Máquina Virtual
    if ($productId == 1) {
        // Atributos: Sistema Operativo, RAM, CPU, Disco, Tarjeta de Red
        $sql_so = "SELECT s.nom, v.nombre, tv.preu
                          FROM SISTEMA_OPERATIU s
                          JOIN PREUSO tv ON s.nom = tv.nomSO
                          JOIN VERSIO v ON tv.nombreVersio = v.nombre";
        $atributs['so'] = $con->query($sql_so);

        $sql_ram = "SELECT r.velocitat, r.idEm, r.idTipusEm, e.tamany, te.tipusEm, r.preu, te.multiplicadorPreu
                  FROM RAM r
                  JOIN EMMAGATZEMATGE e ON r.idEm = e.id
                  JOIN TIPUS_EMMAGATZEMATGE te ON r.idTipusEm = te.id
                  WHERE r.idEm IS NOT NULL";
        $atributs['ram'] = $con->query($sql_ram);

        $sql_cpu = "SELECT id, numNuclis, frequencia, preu FROM CPU";
        $atributs['cpu'] = $con->query($sql_cpu);

        $sql_disc = "SELECT d.id, d.velocitat, d.tipus, e.tamany, te.tipusEm, d.preu
                   FROM DISC d
                   JOIN EMMAGATZEMATGE e ON d.idEm = e.id
                   JOIN TIPUS_EMMAGATZEMATGE te ON d.idTipusEm = te.id";
        $atributs['disc'] = $con->query($sql_disc);

        $sql_red = "SELECT id AS idTXarxa, compatibilitatIPv6, tipus, protocol, rangPorts, origen, preu
                  FROM TARGETA_XARXA";
        $atributs['red'] = $con->query($sql_red);

    // Si es una Base de Datos
    } elseif ($productId == 2) {
        // Atributos de la base de datos: RDBMS, versión de RDBMS, almacenamiento, tipo de almacenamiento
        $sql_rdbms = "SELECT r.id, r.nom, pr.preu, pr.nombreVersio
                    FROM RDBMS r
                    JOIN PREURDBMS pr ON r.id = pr.idRDBMS";
        $atributs['rdbms'] = $con->query($sql_rdbms);

        // Calculamos el precio del atributo RDBMS
        $rdbmsPreu = 50; 
        $preuTotal += $rdbmsPreu;

        $sql_storage = "SELECT id AS idEm, tamany, preu FROM EMMAGATZEMATGE";
        $atributs['storage'] = $con->query($sql_storage);

        $sql_storage_combined = "SELECT e.id AS idEm, e.tamany, te.tipusEm, e.preu, te.multiplicadorPreu
                                        FROM EMMAGATZEMATGE e
                                        JOIN PERTANYER_TIPUS_EM pte ON e.id = pte.idEm
                                        JOIN TIPUS_EMMAGATZEMATGE te ON pte.idTipusEm = te.id";
        $atributs['storage'] = $con->query($sql_storage_combined);


    // Si es un Almacenamiento en la Nube
    } elseif ($productId == 3) {
        $sql_storage_combined = "SELECT e.id AS idEm, e.tamany, te.tipusEm, e.preu, te.multiplicadorPreu
                                FROM EMMAGATZEMATGE e
                                JOIN PERTANYER_TIPUS_EM pte ON e.id = pte.idEm
                                JOIN TIPUS_EMMAGATZEMATGE te ON pte.idTipusEm = te.id";
        $atributs['storage'] = $con->query($sql_storage_combined);
    }

} else {
    echo "ID de producte no especificat.";
    exit;
}
?>

<!DOCTYPE html>
  <html lang="cat">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Producte - LTWS</title>
      <link rel="stylesheet" href="css/producto.css">
    </head>
    <body>
      <header class="header">
        <div class="menu-icon" id="menuButton">☰</div>
        <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image"/></div>
        <div class="user-icon">ICONO</div>
      </header>

      <div class="menu" id="menu">
        <ul>
          <li><a href="index.php">Inici</a></li>
          <li><a href="#">Perfil</a></li>
          <li><a href="#">Configuració</a></li>
          <li><a href="index.php">Sortir</a></li>
        </ul>
      </div>

    <main>
        <div class="pagina_producte">
            <!-- PRODUCTE -->
            <?php if (!empty($product['foto'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($product['foto']); ?>" alt="Producte principal" class="main-image" style="width: 150px; height: auto;">
            <?php else: ?>
                <img src="img/default-producto.jpg" alt="Producte no disponible" class="main-image">
            <?php endif; ?>

            <!-- Informació-->
            <div class="info_producte">
                <h1><?php echo htmlspecialchars($product['nom']); ?></h1>

                <!-- Característiques -->
                <div class="detalls_producte">
                    <!-- Etapa -->
                    <p>
                        <strong>Etapa:</strong> 
                        <span id="etapaText"><?php echo htmlspecialchars($product['nomEtapa']); ?></span>
                        <div id="etapaSelectWrapper" style="margin-left: 40px; margin-right: 1200px;">
                        <form id="etapaForm" action="php/guardar_etapa.php" method="POST">
                            <select name="etapa" id="etapaSelect">  
                                <?php 
                                // Verificamos que hay etapas disponibles
                                if ($atributs['etapes']->num_rows > 0) {
                                    // Recorremos las etapas
                                    while ($etapa = $atributs['etapes']->fetch_assoc()) {
                                        // Establecemos si la etapa es la seleccionada
                                        $selected = ($etapa['nom'] == $product['nomEtapa']) ? 'selected' : '';
                                        echo "<option value='" . $etapa['id'] . "' $selected>" . htmlspecialchars($etapa['nom']) . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>No hay etapas disponibles</option>";
                                }
                                ?>
                            </select>
                            <input type="hidden" name="producte" value="<?php echo $productId; ?>">
                            <button type="submit" id="guardarEtapaBtn" style="padding: 8px 16px;">Guardar</button>
                        </form>
                        </div>
                    </p>
                    <p>
                    <strong>Etapes Anteriors:</strong>
                    <?php
include "php/connexio.php";

// Consulta SQL para obtener las etapas anteriores
$sql_etapes_anteriors = "
    SELECT e.nom AS nomEtapa
    FROM producte p
    JOIN anterior an ON p.id = an.idProducte
    JOIN etapa e ON an.idEtapa = e.id
    WHERE p.id = " . intval($productId) . ";";

// Ejecutar la consulta
$reseultAn = mysqli_query($con, $sql_etapes_anteriors);

// Verificar si se obtuvo algún resultado
if ($reseultAn && mysqli_num_rows($reseultAn) > 0) {
    // Inicializamos un contenedor para las etapas anteriores
    $etapasAnteriores = [];

    // Iterar sobre todos los resultados
    while ($row = mysqli_fetch_assoc($reseultAn)) {
        // Agregar el nombre de la etapa anterior al array
        $etapasAnteriores[] = htmlspecialchars($row['nomEtapa']);
    }

    // Mostrar las etapas anteriores en el span (puedes personalizar cómo mostrar las etapas)
    echo '<span id="etapaText">' . implode(', ', $etapasAnteriores) . '</span>';
} else {
    // Si no hay resultados, mostrar un mensaje
    echo '<span id="etapaText">No hay etapas anteriores disponibles.</span>';
}

// Cerrar la conexión
mysqli_close($con);
?>

                    </p>        
                    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($product['nomCategoria']); ?></p>
                    <p><strong>Descripció:</strong> <?php echo nl2br(htmlspecialchars($product['descripcio'])); ?></p>
                </div>
            </div>

            <!-- SELECCIÓ D'ATRIBUTS -->
            <div class="seleccio_atributs">
                <h2>Visualització dels atributs disponibles</h2>

                <!-- MÀQUINA VIRTUAL -->
                <?php if ($productId == 1): ?>

                    <!-- SO -->
                    <div class="atribut">
                        <label for="so">Sistemes Operatius:</label>
                        <select name="so" id="so">
                        <?php while ($row = $atributs['so']->fetch_assoc()): ?>
                            <option value="<?php echo $row['nom']; ?>" data-preu="<?php echo $row['preu']; ?>">
                            <?php echo $row['nom'] . " " . $row['nombre'] . " → " . $row['preu'] ."€"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>

                
                    <!-- RAM -->
                    <div class="atribut">
                        <label for="ram">RAMs:</label>
                        <select name="ram" id="ram">
                        <?php while ($row = $atributs['ram']->fetch_assoc()): ?>
                            <?php $preuOpcio = $row['preu'] * $row['multiplicadorPreu']; ?>
                            <option value="<?php echo $row['idEm']; ?>" data-preu="<?php echo number_format($preuOpcio, 2); ?>">
                            <?php echo $row['tamany'] . " " . $row['tipusEm'] . " → " . number_format($preuOpcio, 2) ."€"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- CPU -->
                    <div class="atribut">
                        <label for="cpu">CPUs:</label>
                        <select name="cpu" id="cpu">
                        <?php while ($row = $atributs['cpu']->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" data-preu="<?php echo $row['preu']; ?>">
                            <?php echo $row['numNuclis'] . " nuclis a " . $row['frequencia'] . " GHz → " . number_format($row['preu'], 2) ."€"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- DISC -->
                    <div class="atribut">
                        <label for="disc">Discs:</label>
                        <select name="disc" id="disc">
                        <?php while ($row = $atributs['disc']->fetch_assoc()): ?>
                            <?php $preuDisc = $row['preu']; ?>
                            <option value="<?php echo $row['id']; ?>" data-preu="<?php echo number_format($preuDisc, 2); ?>">
                            <?php echo $row['tamany'] . " " . $row['tipusEm']. " → " . number_format($preuDisc, 2) ."€"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                
                    <!-- TARGETA DE XARXA -->
                    <div class="atribut">
                        <label for="xarxa">Targetes de Xarxa:</label>
                        <select name="xarxa" id="xarxa">
                        <?php 
                        while ($row = $atributs['red']->fetch_assoc()): 
                            // Determinam alias pels atributs
                            $idTXarxa = $row['idTXarxa'];
                            $compatibilitatIPv6 = $row['compatibilitatIPv6'] ? 'Si' : 'No';
                            $tipus = $row['tipus'];
                            $protocol = $row['protocol'];
                            $rangPorts = $row['rangPorts'];
                            $origen = $row['origen'];
                            $preuRed = $row['preu'];
                        ?>
                            <option value="<?php echo $idTXarxa; ?>" data-preu="<?php echo number_format($preuRed, 2); ?>">
                            <?php echo "ID: $idTXarxa | IPv6: $compatibilitatIPv6 | Tipus: $tipus | Protocol: $protocol | Rang Ports: $rangPorts | Origen: $origen → " . number_format($preuRed, 2) . "€"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>


                <!-- BASE DE DADES -->
                <?php if ($productId == 2): ?>

                    <!-- RDBMS -->
                    <div class="atribut">
                        <label for="rdbms">RDBMS disponibles:</label>
                        <select name="rdbms" id="rdbms">
                        <?php while ($row = $atributs['rdbms']->fetch_assoc()): ?>
                            <option value="<?php echo $row['nom']; ?>">
                            <?php echo $row['nom'] . " " . $row['nombreVersio'];?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- EMMAGATZEMATGE -->
                    <div class="atribut">
                        <label for="em">Tipus d'emmagatzematges disponibles:</label>
                        <select name="em" id="em">
                        <?php while ($row = $atributs['storage']->fetch_assoc()): ?>
                            <option value="<?php echo $row['idEm']; ?>">
                            <?php echo $row['tamany'] . " GB"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- EMMAGATZEMATGE AL NÚVOL -->
                <?php if ($productId == 3): ?>
                    <!-- EMMAGATZEMATGE -->
                    <div class="atribut">
                        <label for="em">Tipus d'emmagatzematges disponibles:</label>
                        <select name="em" id="em">
                        <?php while ($row = $atributs['storage']->fetch_assoc()): ?>
                            <option value="<?php echo $row['idEm']; ?>">
                            <?php echo $row['tamany'] . " GB"; ?>
                            </option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

    <footer class="footer">
        <p>© 2024 LTWS</p>
    </footer>
    <script src="js/register.js"></script>
    <script src="js/paginaweb.js"></script>
  </body>
</html>
