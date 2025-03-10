<?php
session_start();  // Start the session

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include "php/connexio.php";

$userId = $_SESSION['user'];

include "php/user_photo.php";
$userPhoto = userPhoto($_SESSION['user'], $con);

// Fetch user data for pre-filling the form
$sql = "SELECT 
        p.username, p.nom, p.llinatges, p.adreça, p.foto, 
        emails.emails, 
        telefons.telefons,  
        p.codiPostal, c.nom AS ciutat, pa.nom AS pais
        FROM persona p
        LEFT JOIN (SELECT username, GROUP_CONCAT(email SEPARATOR ', ') AS emails FROM email GROUP BY username) emails 
                  ON p.username = emails.username
        LEFT JOIN (SELECT username, GROUP_CONCAT(telefon SEPARATOR ', ') AS telefons FROM telefon GROUP BY username) telefons 
                  ON p.username = telefons.username
        JOIN ciutat c ON p.codiPostal = c.codiPostal
        JOIN pais pa ON pa.codi = c.codiPais
        WHERE p.username = '".$userId."' 
        GROUP BY p.username, p.nom, p.llinatges, p.adreça, p.foto, p.codiPostal, c.nom, pa.nom";

$userData = mysqli_query($con, $sql);

// Check if there is any data
if ($userData && mysqli_num_rows($userData) > 0) {
    // Fetch the result as an associative array
    $userData = mysqli_fetch_assoc($userData);
} else {
    echo "User data not found!";
    exit;  // Stop execution if no data is found
}

$sql2 = "SELECT DISTINCT g.nomGroup
            FROM GRUP g
            LEFT JOIN PERTANYER pr ON pr.idGrup = g.id
            LEFT JOIN PRIVILEGI pri ON pri.idGrup = g.id
            LEFT JOIN ACCIO a ON a.id = pri.idAccio
            LEFT JOIN PERSONA p ON p.username = pr.idUsuari
            WHERE pr.idGrup IS NOT NULL 
            AND p.username = '".$userId."'
            GROUP BY g.id; ";
$groupData = mysqli_query($con, $sql2);

$sql23 = "SELECT DISTINCT a.nom
          FROM ACCIO a
          JOIN PRIVILEGI pri ON a.id = pri.idAccio
          JOIN GRUP g ON pri.idGrup = g.id
          JOIN PERTANYER p ON g.id = p.idGrup
          WHERE p.idUsuari = '$userId'";
$permData = mysqli_query($con, $sql23);

$sql22 = "SELECT e.nom 
          FROM empresa e
          JOIN treballar t ON  t.CIF = e.CIF
          JOIN persona p ON p.username = t.idUsuari
          WHERE p.username = '".$userId."' ";

$empresaData = mysqli_query($con, $sql22);


$sql3 = "SELECT pr.nom, ip.id
          FROM producte pr
          JOIN inst_producte ip ON ip.idProducte = pr.id
          JOIN persona p ON p.username = ip.idUsuari
          WHERE p.username = '".$userId."'";

$productsData = mysqli_query($con, $sql3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Perfil d'Usuari - LTWS</title>
  <link rel="stylesheet" href="css/perfil.css"> 
  <link rel="stylesheet" href="css/paginaweb.css">
</head>
<body>
  
    <header class="header">
        <div class="menu-icon" id="menuButton">☰</div>
        <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image" /></div>
        <div class="user-icon">
          <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
            <a href="perfil.php">
              <img src="<?php echo !empty($userPhoto) ? 'data:image/jpeg;base64,'.base64_encode($userPhoto) : 'img/default_avatar.png'; ?>" alt=" " style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" />
            </a>
          <?php else: ?>
            <a href="login.php">Login</a>
          <?php endif; ?>
        </div>
    </header>

    <div class="menu" id="menu">
      <ul>
        <li><a href="index.php">Inici</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="modificar_perfil.php">Configuració</a></li>
        <li><a href="tancar_sessio.php">Tancar Sessió</a></li>
      </ul>
    </div>

    <main class="container">
        <?php if (mysqli_num_rows($productsData) > 0): ?>
          <div class="products-container">
            <div class="products-header">
              <h2>Els meus productes</h2>
            </div>
            <div class="products-details">
              <?php while ($product = mysqli_fetch_assoc($productsData)): ?>
                  <p><?php echo htmlspecialchars($product['nom']); echo htmlspecialchars($product['id']); ?></p>
                      <?php  
                      if ($product['nom'] == 'Màquina Virtual'){
                        $stmt = "SELECT DISTINCT 
                                        mv.IP AS DireccioIP,
                                        mv.mascaraSubxarxa AS MascaraSubxarxa,
                                        mv.gateWay AS Gateway,
                                        mv.servidorDNS AS ServidorDNS,
                                        ram.velocitat AS VelocitatRAM,
                                        GROUP_CONCAT(DISTINCT CONCAT(cp.numNuclis, ' núcleos a ', cp.frequencia, ' GHz') SEPARATOR ', ') AS CPUs,
                                        GROUP_CONCAT(DISTINCT CONCAT(disc.tipus, ' (', disc.velocitat, ' MB/s)') SEPARATOR ', ') AS Discs,
                                        GROUP_CONCAT(DISTINCT CONCAT(txarxa.tipus, IF(txarxa.compatibilitatIPv6, ' (IPv6)', ' (No IPv6)')) SEPARATOR ', ') AS TargetesDeXarxa,
                                        so.nom AS SistemaOperatiu,
                                        mv.nombreVersio AS VersioSO
                                    FROM 
                                        MAQUINA_VIRTUAL mv
                                    LEFT JOIN 
                                        RAM ram ON mv.idRAM = ram.id
                                    LEFT JOIN 
                                        TENIR_CPU tenir_cpu ON mv.id = tenir_cpu.idMaqVir
                                    LEFT JOIN 
                                        CPU cp ON tenir_cpu.idCPU = cp.id
                                    LEFT JOIN 
                                        TENIR_DISC tenir_disc ON mv.id = tenir_disc.idMaqVir
                                    LEFT JOIN 
                                        DISC disc ON tenir_disc.idDisc = disc.id
                                    LEFT JOIN 
                                        TENIR_XARXA tenir_xarxa ON mv.id = tenir_xarxa.idMaqVir
                                    LEFT JOIN 
                                        TARGETA_XARXA txarxa ON tenir_xarxa.idTXarxa = txarxa.id
                                    LEFT JOIN 
                                        SISTEMA_OPERATIU so ON mv.nomSO = so.nom
                                    WHERE mv.id= '".$product['id']."'";

                      }else if ($product['nom'] == 'Base de Dades'){
                        $stmt = "SELECT DISTINCT 
                                emmagatz.tamany AS TamanyEmmagatzematge,
                                tipus_em.tipusEm AS TipusEmmagatzematge,
                                rdbms.nom AS SistemaGestorBD,
                                acces.nombreVersio AS VersioRDBMS
                            FROM 
                                ACCES_BD acces
                            LEFT JOIN 
                                EMMAGATZEMATGE emmagatz ON acces.idEm = emmagatz.id
                            LEFT JOIN 
                                TIPUS_EMMAGATZEMATGE tipus_em ON acces.idTipusEm = tipus_em.id
                            LEFT JOIN 
                                RDBMS rdbms ON acces.idRDBMS = rdbms.id
                            LEFT JOIN 
                                PREURDBMS preuRDBMS ON acces.idRDBMS = preuRDBMS.idRDBMS AND acces.nombreVersio = preuRDBMS.nombreVersio
                            WHERE	acces.id = '".$product['id']."'";

                      }else if ($product['nom'] == 'Emmagatzematge al Núvol'){
                        $stmt = "SELECT DISTINCT 
                                    emmagatz.tamany AS TamanyEmmagatzematge,
                                    tipus_em.tipusEm AS TipusEmmagatzematge
                                FROM 
                                    EMMAGATZEMATGE_NUVOL nuvol
                                LEFT JOIN 
                                    EMMAGATZEMATGE emmagatz ON nuvol.idEm = emmagatz.id
                                LEFT JOIN 
                                    TIPUS_EMMAGATZEMATGE tipus_em ON nuvol.idTipusEm = tipus_em.id
                                WHERE nuvol.id = '".$product['id']."'";
                      }  

                      $caracteristicasData = mysqli_query($con, $stmt);

                      if (mysqli_num_rows($caracteristicasData) > 0) {
                        while ($row = mysqli_fetch_assoc($caracteristicasData)) {
                            echo "<h4>Característiques del producte</h4>";
                            echo "<br>";
                            echo "<ul>";
                            foreach ($row as $key => $value) {
                                echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
                            }
                            echo "</ul>";
                        }
                    } else {
                        echo "No se encontraron resultados.";
                    }?>
                    <hr><br>
                <?php endwhile; ?>
            </div>
          </div>
        <?php endif; ?>

      <div class="profile-container">
        <div class="profile-header">
          <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
            <a href="perfil.php">
              <img src="<?php echo !empty($userPhoto) ? 'data:image/jpeg;base64,'.base64_encode($userPhoto) : 'img/default_avatar.png'; ?>"/>
            </a>
          <?php endif; ?>
          <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
        </div>
        <div class="profile-details">
          <p><strong>Nom:</strong> <?php echo htmlspecialchars($userData['nom']); ?></p>
          <p><strong>Cognoms:</strong> <?php echo htmlspecialchars($userData['llinatges']); ?></p>
          <p><strong>Correu:</strong> <?php echo htmlspecialchars($userData['emails']); ?></p>
          <p><strong>Telèfon:</strong> <?php echo htmlspecialchars($userData['telefons']); ?></p>
          <p><strong>Direcció:</strong> <?php echo htmlspecialchars($userData['adreça']); ?></p>
          <p><strong>País:</strong> <?php echo htmlspecialchars($userData['pais']); ?></p>
          <p><strong>Ciutat:</strong> <?php echo htmlspecialchars($userData['ciutat']); ?></p>
          <p><strong>CP:</strong> <?php echo htmlspecialchars($userData['codiPostal']); ?></p>
          <div class="edit-button">
          <a href="modificar_perfil.php" class="btn">Editar Perfil</a>
        </div>
      </div>

      </div>
      <?php if (mysqli_num_rows($empresaData) > 0): ?>
        <?php while($empresa = mysqli_fetch_assoc($empresaData)):?>
          <?php if (mysqli_num_rows($groupData) > 0): ?>
            <div class="groups-container">
              <div class="groups-header">
              <h2><?php echo "Empresa: " .htmlspecialchars($empresa['nom']); ?></h2>
                <h3>Grups</h3>
              </div>
              <div class="groups-details">
                <?php while ($group = mysqli_fetch_assoc($groupData)): ?>
                  <p><?php echo htmlspecialchars($group['nomGroup']); ?></p>
                <?php endwhile; ?>

                <h3>Accions:</h3>
                <?php while ($perm = mysqli_fetch_assoc($permData)): ?>
                  <p>
                    <?php if (isset($perm['nom']) && $perm['nom'] == 'Crear un usuari'): ?> 
                      <a href="cpersona.php" class="btn">Afegir persona</a>
                    <?php endif; ?>
                  </p>
                  <?php if ((mysqli_num_rows($empresaData) > 0) && isset($perm['nom']) && $perm['nom'] == 'Crear un grup'): ?>
                    <a href="cgrupo.php" class="btn">Crear Grup</a>  <br>
                    <a href="gestionar_grupo.php" class="btn">Gestionar Grup</a>
                  <?php endif; ?>
                <?php endwhile; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endwhile; ?>
        <?php endif; ?>


      </div>
    </main>
  <footer class="footer">
    <p>© 2024 LTWS</p>
  </footer>
  <script src="js/paginaweb.js"></script>
</body>
</html>
