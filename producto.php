<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include "php/connexio.php";
include "php/user_photo.php";
$userPhoto = userPhoto($_SESSION['user'], $con);

if (!isset($_SESSION['user'])) {
    echo "Session 'user' is not set!";
    exit;
}

$userId = $_SESSION['user'];

// Obtenim l'ID del producte amb la url (per exemple, producto.php?product=1)
$productId = isset($_GET['product']) ? $_GET['product'] : null;

if ($productId) {
  $sql = "SELECT p.nom, p.descripcio, p.idCategoria, c.nom AS nomCategoria, p.foto FROM PRODUCTE p
                  JOIN CATEGORIA c ON p.idCategoria = c.id
                  WHERE p.id = ?";
                  
  // Preparamos la consulta
  $stmt = $con->prepare($sql);
  $stmt->bind_param("i", $productId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      $product = $result->fetch_assoc();
  } else {
      echo "Producte no disponible.";
      exit;
  }

  $stmt->close();

  $atributs = [];

  $preuTotal = 0;

  // Si é una Màquina Virtual
  if ($productId == 1) {
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


      $sql_disc = "SELECT d.id, d.velocitat, d.tipus, e.tamany, te.id AS idTipus, te.tipusEm, d.preu
                   FROM DISC d
                   JOIN EMMAGATZEMATGE e ON d.idEm = e.id
                   JOIN TIPUS_EMMAGATZEMATGE te ON d.idTipusEm = te.id";
      $atributs['disc'] = $con->query($sql_disc);


      $sql_red = "SELECT id AS idTXarxa, compatibilitatIPv6, tipus, protocol, rangPorts, origen, preu
                  FROM TARGETA_XARXA";
      $atributs['red'] = $con->query($sql_red);

  // Si és una Base de Dades
  } elseif ($productId == 2) {
      $sql_rdbms = "SELECT r.id, r.nom, pr.preu, pr.nombreVersio
                    FROM RDBMS r
                    JOIN PREURDBMS pr ON r.id = pr.idRDBMS";
      $atributs['rdbms'] = $con->query($sql_rdbms);

      $sql_storage_combined = "SELECT e.id AS idEm, e.tamany, te.tipusEm, te.id AS idTipus, e.preu, te.multiplicadorPreu
                                  FROM EMMAGATZEMATGE e
                                  JOIN PERTANYER_TIPUS_EM pte ON e.id = pte.idEm
                                  JOIN TIPUS_EMMAGATZEMATGE te ON pte.idTipusEm = te.id";
      $atributs['storage'] = $con->query($sql_storage_combined);


  // Siées un Emmagatzematge al Núvol
  } elseif ($productId == 3) {
      $sql_storage_combined = "SELECT e.id AS idEm, e.tamany, te.tipusEm, te.id AS idTipus, e.preu, te.multiplicadorPreu
                                  FROM EMMAGATZEMATGE e
                                  JOIN PERTANYER_TIPUS_EM pte ON e.id = pte.idEm
                                  JOIN TIPUS_EMMAGATZEMATGE te ON pte.idTipusEm = te.id";
      $atributs['storage'] = $con->query($sql_storage_combined);

  }

} else {
  echo "ID de producte no especificada.";
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
        <div class="user-icon">
        <?php if (isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
        <a href="perfil.php">
            <img src="<?php echo !empty($userPhoto) ? 'data:image/jpeg;base64,'.base64_encode($userPhoto) : 'img/default_avatar.png'; ?>" alt=" " style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" />
        </a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
      </header>

      <div class="menu" id="menu">
        <ul>
        <li><a href="index.php">Inici</a></li>
        <li><a href="perfil.php">Perfil</a></li>
        <li><a href="modificar_perfil.php">Configuració</a></li>
        <li><a href="tancar_sessio.php">Tancar Sessió</a></li>
        </ul>
    </div>

      <main>
        <div class="pagina_producte">
          <!-- PRODUCTE -->
          <span id="soName"></span>
          <?php if (!empty($product['foto'])): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['foto']); ?>" alt="Producte principal" class="main-image" style="width: 150px; height: auto;">
          <?php else: ?>
            <img src="img/default-producto.jpg" alt="Producte no disponible" class="main-image">
          <?php endif; ?>

          <!-- INFO DEL PRODUCTE-->
          <div class="info_producte">
            <h1><?php echo htmlspecialchars($product['nom']); ?></h1>

            <!-- Característiques -->
            <div class="detalls_producte">
              <p><strong>Categoria:</strong> <?php echo htmlspecialchars($product['nomCategoria']); ?></p>
              <p><strong>Descripció:</strong> <?php echo nl2br(htmlspecialchars($product['descripcio'])); ?></p>
            </div>
          </div>

          <!-- SELECCIÓ D'ATRIBUTS -->
          <div class="seleccio_atributs">
            <h2>Selecció d'atributs</h2>

            <!-- MÀQUINA VIRTUAL -->
            <?php if ($productId == 1): ?>

                <!-- SO -->
                <div class="atribut">
                <label for="so">Selecciona el Sistema Operatiu:</label>
                <select name="so" id="so" required>
                    <?php while ($row = $atributs['so']->fetch_assoc()): ?>
                    <option value="<?php echo $row['nom']; ?>" data-version="<?php echo $row['nombre']; ?>" data-preu="<?php echo number_format($row['preu'], 2); ?>">
                        <?php echo $row['nom'] . " " . $row['nombre'] . " → " . $row['preu'] ."€"; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                </div>

              
                <!-- RAM -->
                <div class="atribut">
                    <label for="ram">Selecciona la RAM:</label>
                    <select name="ram" id="ram" required>
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
                    <fieldset id="cpu" style="border: 1px solid #ccc; padding: 10px; margin-top: 10px;">
                        <legend style="font-weight: bold; padding: 0 10px;">Selecciona 1 o més CPUs:</legend>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <?php while ($row = $atributs['cpu']->fetch_assoc()): ?>
                            <?php $preuCpu = $row['preu']; ?>
                            <label style="display: inline-block; width: calc(33.33% - 10px); margin-bottom: 10px; font-size: 14px;">
                                <input type="checkbox" name="cpu[]" value="<?php echo $row['id']; ?>" data-preu="<?php echo number_format($preuCpu, 2); ?>" style="margin-right: 5px;">
                                <?php echo $row['numNuclis'] . " nuclis a " . $row['frequencia'] . " GHz → " . number_format($preuCpu, 2) . "€"; ?>
                            </label>
                            <?php endwhile; ?>
                        </div>
                    </fieldset>
                </div>

                <!-- DISC -->
                <div class="atribut">
                    <fieldset id="disc" style="border: 1px solid #ccc; padding: 10px; margin-top: 10px;">
                    <legend style="font-weight: bold; padding: 0 10px;">Selecciona 1 o més Discos:</legend>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php while ($row = $atributs['disc']->fetch_assoc()): ?>
                        <?php $preuDisc = $row['preu']; ?>
                        <label style="display: inline-block; width: calc(33.33% - 10px); margin-bottom: 10px; font-size: 14px;">
                            <input type="checkbox" name="disc[]" value="<?php echo $row['id']; ?>" data-version="<?php echo $row['idTipus']; ?>"data-preu="<?php echo number_format($preuDisc, 2); ?>" style="margin-right: 5px;">
                            <?php echo $row['tamany'] . " " . $row['tipusEm'] . " → " . number_format($preuDisc, 2) . "€"; ?>
                        </label>
                        <?php endwhile; ?>
                    </div>
                    </fieldset>
                </div>

                <!-- TARGETA DE XARXA -->
                <div class="atribut">
                    <fieldset style="border: 1px solid #ccc; padding: 10px; margin-top: 10px;">
                    <legend style="font-weight: bold; padding: 0 10px;">Selecciona cap o més Targetes de Xarxa:</legend>
                    <div id="xarxa" style="display: flex; flex-direction: column; gap: 10px; margin-left: 20px;">
                        <label style="font-size: 14px;">
                        <input type="checkbox" name="xarxa[]" value="none" id="none-xarxa" onclick="toggleXarxaCheckboxes()" style="margin-right: 5px;">
                        Cap
                        </label>
                        
                        <?php while ($row = $atributs['red']->fetch_assoc()): ?>
                        <?php 
                        $idTXarxa = $row['idTXarxa'];
                        $compatibilitatIPv6 = $row['compatibilitatIPv6'] ? 'Si' : 'No';
                        $tipus = $row['tipus'];
                        $protocol = $row['protocol'];
                        $rangPorts = $row['rangPorts'];
                        $origen = $row['origen'];
                        $preuRed = $row['preu'];
                        ?>
                        <label style="font-size: 14px;">
                            <input type="checkbox" name="xarxa[]" value="<?php echo $idTXarxa; ?>" data-preu="<?php echo number_format($preuRed, 2); ?>" class="xarxa-checkbox" style="margin-right: 5px;">
                            <?php echo "ID: $idTXarxa | IPv6: $compatibilitatIPv6 | Tipus: $tipus | Protocol: $protocol | Rang Ports: $rangPorts | Origen: $origen → " . number_format($preuRed, 2) . "€"; ?>
                        </label>
                        <?php endwhile; ?>
                    </div>
                    </fieldset>
                </div>

                <script>
                    function toggleXarxaCheckboxes() {
                    var checkboxes = document.querySelectorAll('.xarxa-checkbox');
                    var noneCheckbox = document.getElementById('none-xarxa');
                        if (noneCheckbox.checked) {
                            checkboxes.forEach(function(checkbox) {
                            checkbox.disabled = true;
                            });
                        } else {
                            checkboxes.forEach(function(checkbox) {
                            checkbox.disabled = false;
                            });
                        }
                    }
                </script>
              <?php endif; ?>


              <!-- BASE DE DADES -->
              <?php if ($productId == 2): ?>

                <!-- RDBMS -->
                <div class="atribut">
                  <label for="rdbms">Selecciona el RDBMS:</label>
                  <select name="rdbms" id="rdbms" required>
                    <?php while ($row = $atributs['rdbms']->fetch_assoc()): 
                      $preuRDBMS = $row['preu'];?>
                      <option value="<?php echo $row['id']; ?>" data-version="<?php echo $row['nombreVersio']; ?>"data-preu="<?php echo number_format($preuRDBMS, 2); ?>">
                        <?php echo $row['nom'] . " " . $row['nombreVersio'] . " → " . number_format($preuRDBMS, 2) . "€";?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- EMMAGATZEMATGE -->
                <div class="atribut">
                  <label for="em">Selecciona l'emmagatzematge:</label>
                  <select name="em" id="em" required>
                    <?php while ($row = $atributs['storage']->fetch_assoc()):
                      $preuStorage = $row['preu'] * $row['multiplicadorPreu']; ?>
                      <option value="<?php echo $row['idEm']; ?>"  data-version="<?php echo $row['idTipus']; ?>"data-preu="<?php echo number_format($preuStorage, 2); ?>">
                        <?php echo $row['tamany'] . " " . $row['tipusEm'] . " → " . number_format($preuStorage, 2) . "€"; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>


              <?php endif; ?>

              <!-- EMMAGATZEMATGE AL NÚVOL -->
              <?php if ($productId == 3): ?>

                <!-- EMMAGATZEMATGE -->
                <div class="atribut">
                  <label for="em">Selecciona l'emmagatzematge:</label>
                  <select name="em" id="em" required>
                    <?php while ($row = $atributs['storage']->fetch_assoc()):
                      $preuStorage = $row['preu'] * $row['multiplicadorPreu']; ?>
                      <option value="<?php echo $row['idEm']; ?>"data-version="<?php echo $row['idTipus']; ?>" data-preu="<?php echo number_format($preuStorage, 2); ?>">
                        <?php echo $row['tamany'] . " " . $row['tipusEm'] . " → " . number_format($preuStorage, 2) . "€"; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botó de comprar -->
        <h2>Detalls de Pagament</h2>
        <div class="boto_comprar">  
          <p class="preu_total">
            <span id="preuTotal"><?php echo number_format($preuTotal, 2); ?></span>€
          </p>
          
          <form action="php/pagament.php" method="POST" class="forPag">
          <?php
                if (isset($_GET['product']) && $_GET['product'] == 1) {
                    echo '
                        <div>
                            <label>
                                Direcció IP<input type="text" name="direccioIP" required maxlength="16">
                            </label>
                            <label>
                                Màscara de Subxarxa<input type="text" name="mascaraSubxarxa" required maxlength="16">
                            </label>
                            <label>
                                GateWay<input type="text" name="gateway" required maxlength="16">
                            </label>
                            <label>
                                Servidors DNS<input type="text" name="dns" required maxlength="39">
                            </label>
                        </div>
                    ';
                }
            ?>
            <label for="titular">Titular de la targeta</label>
            <input type="text" id="titular" name="titular" required maxlength="64" placeholder="Titular de la tarjeta">

            <label for="emissor">Emissor de la Tarjeta</label>
            <input type="text" id="emissor" name="emissor" required maxlength="64" placeholder="Emissor de la tarjeta">

            <label for="numTar">Número de targeta</label>
            <input type="text" id="numTar" name="numTar" required maxlength="16" placeholder="XXXXXXXXXXXXXXXX">

            <label for="dataV">Data de venciment</label>
            <input type="date" id="dataV" name="dataV" required>

            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required maxlength="3" placeholder="XXX">

            <input type="hidden" name="producte" value="<?php echo $productId; ?>">

            <input type="hidden" id="soSelected" name="soSelected">
            <input type="hidden" id="ramSelected" name="ramSelected">
            <input type="hidden" id="cpuSelected" name="cpuSelected">
            <input type="hidden" id="discSelected" name="discSelected">
            <input type="hidden" id="xarxaSelected" name="xarxaSelected">
            <input type="hidden" id="rdbmsSelected" name="rdbmsSelected">
            <input type="hidden" id="storageSelected" name="storageSelected">

            <?php
                // Mostrar mensaje si existe el error en la URL
                if (isset($_GET['error']) && $_GET['error'] == 'cardDataMismatch') {
                    echo '<div style="color: red; margin-top: 10px;">Les dades de la targeta són incorrectes.</div>';
                }
            ?>
            <button type="submit" class="boto_compra" id="comprarBtn" disabled>Comprar</button>
          </form>
        </div>

    <footer class="footer">
      <p>© 2024 LTWS</p>
    </footer>

    <script src="js/register.js"></script>
    <script src="js/paginaweb.js"></script>

  </body>
</html>

<script>
    function validateForm() {
        const productId = <?php echo json_encode($productId); ?>;
        const buyButton = document.getElementById('comprarBtn'); 
        
        if (productId == 1) {
            // Solo verificamos si el producto es 1
            const cpuChecked = document.querySelectorAll('input[name="cpu[]"]:checked').length > 0;
            const discChecked = document.querySelectorAll('input[name="disc[]"]:checked').length > 0;
            const xarxaChecked = document.querySelectorAll('input[name="xarxa[]"]:checked').length > 0;

            if (cpuChecked && discChecked && xarxaChecked) {
                buyButton.disabled = false; 
            } else {
                buyButton.disabled = true;
            }
        } else {
            buyButton.disabled = false;
        }
    }

    document.querySelectorAll('input[name="cpu[]"], input[name="disc[]"], input[name="xarxa[]"]').forEach(function(input) {
        input.addEventListener('change', validateForm);
    });

    document.addEventListener('DOMContentLoaded', validateForm);
</script>


<!-- Script per enviar les dades a la compra -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productId = <?php echo json_encode($productId); ?>;

    if (productId == 1) {
        let productIds = {
            so: null,
            ram: null,
            cpu: [],
            disc: [],
            xarxa: []
        };
        function updateProductIds() {
            console.log("updateProductIds() triggered");

            productIds = {
                so: null,
                ram: null,
                cpu: [],
                disc: [],
                xarxa: []
            };

            const selectedSo = document.querySelector('#so') ? document.querySelector('#so').selectedOptions[0] : null;
            const selectedRam = document.querySelector('#ram') ? document.querySelector('#ram').selectedOptions[0] : null;
            const selectedCpuOptions = document.querySelectorAll('#cpu input[type="checkbox"]:checked');
            const selectedDiscOptions = document.querySelectorAll('#disc input[type="checkbox"]:checked');
            const selectedXarxaOptions = document.querySelectorAll('#xarxa input[type="checkbox"]:checked');

            if (selectedSo) {
                productIds.so = {
                    nom: selectedSo.value,
                    versio: selectedSo.dataset.version
                };
                const soSelectedElement = document.querySelector('#soSelected');
                if (soSelectedElement) {
                    soSelectedElement.value = JSON.stringify(productIds.so);
                }
            }

            // RAM
            if (selectedRam) {
                productIds.ram = selectedRam.value;
                const ramSelectedElement = document.querySelector('#ramSelected');
                if (ramSelectedElement) {
                    ramSelectedElement.value = productIds.ram;
                }
            }

            // CPU
            selectedCpuOptions.forEach(function(checkbox) {
                productIds.cpu.push(checkbox.value);
            });
            const cpuSelectedElement = document.querySelector('#cpuSelected');
            if (cpuSelectedElement) {
                cpuSelectedElement.value = JSON.stringify(productIds.cpu);
            }

            // Disc
            selectedDiscOptions.forEach(function(checkbox) {
                const discOption = {
                    nom: checkbox.value,
                    versio: checkbox.dataset.version 
                };
                productIds.disc.push(discOption);
            });

            const discSelectedElement = document.querySelector('#discSelected');
            if (discSelectedElement) {
                discSelectedElement.value = JSON.stringify(productIds.disc);
            }

            // Xarxa
            selectedXarxaOptions.forEach(function(checkbox) {
                productIds.xarxa.push(checkbox.value);
            });
            const xarxaSelectedElement = document.querySelector('#xarxaSelected');
            if (xarxaSelectedElement) {
                xarxaSelectedElement.value = JSON.stringify(productIds.xarxa);
            }
        }

        document.querySelector('#ram').addEventListener('change', updateProductIds);
        document.querySelector('#cpu').addEventListener('change', updateProductIds);
        document.querySelector('#disc').addEventListener('change', updateProductIds);
        document.querySelector('#xarxa').addEventListener('change', updateProductIds);

        const cpuCheckboxes = document.querySelectorAll('#cpu input[type="checkbox"]');
        const discCheckboxes = document.querySelectorAll('#disc input[type="checkbox"]');
        const xarxaCheckboxes = document.querySelectorAll('#xarxa input[type="checkbox"]');

        cpuCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', updateProductIds);
        });
        discCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', updateProductIds);
        });
        xarxaCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', updateProductIds);
        });

        updateProductIds();
    } else if(productId == 2) {
        let productIds = {
            rdbms: null,
            storage: null
        };

        function updateProductIds() {
            console.log("updateProductIds() triggered");

            productIds = {
                rdbms: null,
                storage: null
            };

            const selectedRdbms = document.querySelector('#rdbms') ? document.querySelector('#rdbms').selectedOptions[0] : null;
            const selectedStorage = document.querySelector('#em') ? document.querySelector('#em').selectedOptions[0] : null;

            // RDBMS
            if (selectedRdbms) {
                productIds.rdbms = {
                    nom: selectedRdbms.value,
                    versio: selectedRdbms.dataset.version
                };
                const rdbmsSelectedElement = document.querySelector('#rdbmsSelected');
                if (rdbmsSelectedElement) {
                    rdbmsSelectedElement.value = JSON.stringify(productIds.rdbms);
                }
            }

            // Emmagatzematge
            if (selectedStorage) {
                productIds.storage = {
                    nom: selectedStorage.value,
                    versio: selectedStorage.dataset.version
                };
                const storageSelectedElement = document.querySelector('#storageSelected');
                if (storageSelectedElement) {
                    storageSelectedElement.value = JSON.stringify(productIds.storage);
                }
            }
        }

        document.querySelector('#rdbms').addEventListener('change', updateProductIds);
        document.querySelector('#em').addEventListener('change', updateProductIds);

        updateProductIds();
    } else if (productId == 3) {
        let productIds = {
            storage: null
        };

        function updateProductIds() {
            console.log("updateProductIds() triggered");

            productIds = {
                storage: null
            };

            const selectedStorage = document.querySelector('#em') ? document.querySelector('#em').selectedOptions[0] : null;

            // Emmagatzematge
            if (selectedStorage) {
                productIds.storage = {
                    nom: selectedStorage.value,
                    versio: selectedStorage.dataset.version
                };
                const storageSelectedElement = document.querySelector('#storageSelected');
                if (storageSelectedElement) {
                    storageSelectedElement.value = JSON.stringify(productIds.storage);
                }
            }
        }

        document.querySelector('#em').addEventListener('change', updateProductIds);

        updateProductIds();
    }
});
</script>

<!-- Script de preu dinámic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let preuTotal = <?php echo $preuTotal; ?>;
    const preuTotalElement = document.querySelector('#preuTotal');

    preuTotalElement.innerHTML = preuTotal.toFixed(2);

    function updatePreuTotal() {
        console.log("updatePreuTotal() triggered");
        preuTotal = <?php echo $preuTotal; ?>;

        const selectedSo = document.querySelector('#so') ? document.querySelector('#so').selectedOptions[0] : null;
        const selectedRam = document.querySelector('#ram') ? document.querySelector('#ram').selectedOptions[0] : null;
        const selectedCpuOptions = document.querySelectorAll('#cpu input[type="checkbox"]:checked');
        const selectedDiscOptions = document.querySelectorAll('#disc input[type="checkbox"]:checked');
        const selectedXarxaOptions = document.querySelectorAll('#xarxa input[type="checkbox"]:checked');
        const selectedRdbms = document.querySelector('#rdbms') ? document.querySelector('#rdbms').selectedOptions[0] : null;
        const selectedStorage = document.querySelector('#em') ? document.querySelector('#em').selectedOptions[0] : null;

        console.log("selectedSo: ", selectedSo);
        console.log("selectedRam: ", selectedRam);
        console.log("selectedCpuOptions: ", selectedCpuOptions);
        console.log("selectedDiscOptions: ", selectedDiscOptions);
        console.log("selectedXarxaOptions: ", selectedXarxaOptions);

        // SO
        if (selectedSo) {
            const preuSo = parseFloat(selectedSo.getAttribute('data-preu'));
            console.log("Preu SO: ", preuSo);
            if (!isNaN(preuSo)) {
                preuTotal += preuSo;
            }
        }

        // RAM
        if (selectedRam) {
            const preuRam = parseFloat(selectedRam.getAttribute('data-preu'));
            console.log("Preu RAM: ", preuRam);
            if (!isNaN(preuRam)) {
                preuTotal += preuRam;
            }
        }

        // CPU
        selectedCpuOptions.forEach(function(checkbox) {
            const preuCpu = parseFloat(checkbox.getAttribute('data-preu'));
            console.log("Preu CPU: ", preuCpu);
            if (!isNaN(preuCpu)) {
                preuTotal += preuCpu;
            }
        });

        // Disco
        selectedDiscOptions.forEach(function(checkbox) {
            const preuDisc = parseFloat(checkbox.getAttribute('data-preu'));
            console.log("Preu Disc: ", preuDisc);
            if (!isNaN(preuDisc)) {
                preuTotal += preuDisc;
            }
        });

        // Targeta de Xarxa
        selectedXarxaOptions.forEach(function(checkbox) {
            const preuXarxa = parseFloat(checkbox.getAttribute('data-preu'));
            console.log("Preu Xarxa: ", preuXarxa);
            if (!isNaN(preuXarxa)) {
                preuTotal += preuXarxa;
            }
        });

        // RDBMS
        if (selectedRdbms) {
            const preuRdbms = parseFloat(selectedRdbms.getAttribute('data-preu'));
            console.log("Preu RDBMS: ", preuRdbms);
            if (!isNaN(preuRdbms)) {
                preuTotal += preuRdbms;
            }
        }

        // Emmagatzematge
        if (selectedStorage) {
            const preuStorage = parseFloat(selectedStorage.getAttribute('data-preu'));
            console.log("Preu Storage: ", preuStorage);
            if (!isNaN(preuStorage)) {
                preuTotal += preuStorage;
            }
        }

        console.log("Preu Total: ", preuTotal);
        preuTotalElement.innerHTML = preuTotal.toFixed(2);
    }

    const soSelect = document.querySelector('#so');
    const ramSelect = document.querySelector('#ram');
    const cpuSelect = document.querySelector('#cpu');
    const discSelect = document.querySelector('#disc');
    const xarxaSelect = document.querySelector('#xarxa');
    const rdbmsSelect = document.querySelector('#rdbms');
    const storageSelect = document.querySelector('#em');

    if (soSelect) soSelect.addEventListener('change', updatePreuTotal);
    if (ramSelect) ramSelect.addEventListener('change', updatePreuTotal);
    if (cpuSelect) cpuSelect.addEventListener('change', updatePreuTotal);
    if (discSelect) discSelect.addEventListener('change', updatePreuTotal);
    if (xarxaSelect) xarxaSelect.addEventListener('change', updatePreuTotal);
    if (rdbmsSelect) rdbmsSelect.addEventListener('change', updatePreuTotal);
    if (storageSelect) storageSelect.addEventListener('change', updatePreuTotal);

    const cpuCheckboxes = document.querySelectorAll('#cpu input[type="checkbox"]');
    const discCheckboxes = document.querySelectorAll('#disc input[type="checkbox"]');
    const xarxaCheckboxes = document.querySelectorAll('#xarxa input[type="checkbox"]');

    cpuCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updatePreuTotal);
    });
    discCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updatePreuTotal);
    });
    xarxaCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updatePreuTotal);
    });
});
</script>