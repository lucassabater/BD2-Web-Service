<?php
session_start();

include "php/connexio.php";
include "php/user_photo.php";

if (isset($_SESSION['user'])){
  $userPhoto = userPhoto($_SESSION['user'], $con);
}

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

$pertenece = false;
$sqlGrupos = "SELECT pr.idGrup
          FROM persona p
          JOIN pertanyer pr ON pr.idUsuari = p.username 
          WHERE p.username = '".$_SESSION['user']."'";
$grupData = mysqli_query($con, $sqlGrupos);

if(mysqli_num_rows($grupData) > 0){
  $pertenece = true;
  $sqlAcciones = "SELECT DISTINCT a.id
          FROM ACCIO a
          JOIN PRIVILEGI pri ON a.id = pri.idAccio
          JOIN GRUP g ON pri.idGrup = g.id
          JOIN PERTANYER p ON g.id = p.idGrup
          JOIN USUARI u ON p.idUsuari = u.id
          WHERE (u.idUsuariCreador IS NOT NULL OR g.id = 1) AND u.id = '".$_SESSION['user']."'";
$permData = mysqli_query($con, $sqlAcciones);

$accionesPermitidas = [];
while($perm = mysqli_fetch_assoc($permData)){
    $accionesPermitidas[] = $perm['id'];
}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>LTWS</title>
  <link rel="stylesheet" href="css/paginaweb.css">
  <style>
    .category-buttons {
      margin: 20px 0;
    }

    .category-button {
      padding: 10px 20px;
      margin: 0 10px;
      cursor: pointer;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f0f0f0;
      font-size: 16px;
    }

    .category-button.active {
      background-color: #007bff;
      color: white;
    }

    .product-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .product {
      width: calc(33.333% - 20px);
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
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
  </div>
  </header>

  <div class="category-buttons">
    <button class="category-button <?php echo $category == 'all' ? 'active' : ''; ?>" data-category="all">Tot</button>
    <button class="category-button <?php echo $category == 'paas' ? 'active' : ''; ?>" data-category="paas">PaaS</button>
    <button class="category-button <?php echo $category == 'saas' ? 'active' : ''; ?>" data-category="saas">SaaS</button>
  </div>

  <?php
    include "php/connexio.php";

    $category = isset($_GET['category']) ? $_GET['category'] : 'all';

    if ($category == 'all') {
      $sql = "SELECT p.id, p.nom, p.foto, p.descripcio, p.idEtapa, c.nom AS categoria, e.nom AS etapa
              FROM PRODUCTE p
              JOIN CATEGORIA c ON p.idCategoria = c.id
              JOIN etapa e ON p.idEtapa = e.id";
  } else {
      $sql = "SELECT p.id, p.nom, p.foto, p.descripcio, p.idEtapa, c.nom AS categoria, e.nom AS etapa
              FROM PRODUCTE p
              JOIN CATEGORIA c ON p.idCategoria = c.id
              JOIN etapa e ON p.idEtapa = e.id
              WHERE c.nom = '$category'";
  }
    
    $result = $con->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<div class='product-container'>";
        while ($row = $result->fetch_assoc()) {
          if($row['idEtapa'] == 3 || $row['idEtapa'] == 4 ||  $_SESSION['mode'] == 'personal'){
            $productId = $row['id'];
            $canAccess = true;
            if ($pertenece){
              $canAccess = false;
              switch($productId){
                case 1 :
                  if(in_array(1 , $accionesPermitidas)) {
                    $canAccess = true;
                  }else{
                    $canAccess = false;
                  }
                  break;
                case 2 :
                  if(in_array(2 , $accionesPermitidas)) {
                    $canAccess = true;
                  }else{
                    $canAccess = false;
                  }
                  break;
                case 3 :
                  if(in_array(3 , $accionesPermitidas)) {
                    $canAccess = true;
                  }else{
                    $canAccess = false;
                  }
                  break;
              }
            }
            echo "<div class='product' onclick='goToProductPage($productId,$canAccess)'>";
            if($row['idEtapa'] != 4 ){
              echo "<h3> WARNING: Fase del producte: " . htmlspecialchars($row['etapa']) . "</h3>";
            }
            echo "<h3>" . htmlspecialchars($row['nom']) . "</h3>";
            echo "<p>" . htmlspecialchars($row['descripcio']) . "</p>";
            if (!empty($row['foto'])) {
              if (!empty($row['foto'])) {
                $base64Image = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
                echo '<img src="' . $base64Image . '" alt="Producto" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px;" />';
              } else {
                  echo '<img src="img/productes/bd.png" alt="No Photo Available" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px;" />';
              }
            } else {
                echo '<img src="img/productes/bd.png" alt="No Photo Available " />';
            }
              echo "<p>Categoria: " . htmlspecialchars($row['categoria']) . "</p>";
              echo "</div>";
          }
        }
        echo "</div>";
    } else {
        echo "<p>No s'han trobat productes</p>";
    }
  ?>

  <div class="menu" id="menu">
    <ul>
      <li><a href="index.php">Inici</a></li>
      <li><a href="perfil.php">Perfil</a></li>
      <li><a href="modificar_perfil.php">Configuració</a></li>
      <li><a href="tancar_sessio.php">Tancar Sessió</a></li>
    </ul>
  </div>

  <footer class="footer">
    <p>© 2024 LTWS</p>
  </footer>

  <script src="js/paginaweb.js"></script>
  <script src="js/buttonSelect.js"></script>
  <script>
    // Pasar el modo de sesión a JavaScript
    const sessionMode = "<?php echo isset($_SESSION['mode']) ? $_SESSION['mode'] : ''; ?>";

    function goToProductPage(productId, canAccess) {
      
      if (sessionMode === 'personal') {
        window.location.href = 'producto_personal.php?product=' + productId;
      } else if (canAccess) {
        window.location.href = 'producto.php?product=' + productId;
      }
    }
  </script>
</body>
</html>
