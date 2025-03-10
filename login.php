<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LTWS: Login</title>
    <link rel="stylesheet" type="text/css" href="css/paginaweb.css" />
    <link rel="stylesheet" type="text/css" href="css/login.css" />
</head>

<body>
    <header class="header">
        <div class="menu-logo"><img src="img/logoCabecera.png" class="menu-logo-image"/></div>
        <div class="user-icon">Login</div>
    </header>

    <br><br>

    <h1>Inici de sessió</h1>

    <div class="form-container">
        <form action="php/identifica.php" method="post">
            <label for="user">Usuari:</label>
            <input id="user" name="user" required maxlength="64"><br><br>
            <label for="pass">Contrasenya:</label>
            <input type="password" id="pass" name="pass" required maxlength="128"><br><br>
            <input name="Login" type="submit" required>
            <?php
            // Mostrar mensaje si existe el error en la URL
            if (isset($_GET['error']) && $_GET['error'] == 'notfound') {
                echo '<div style="color: red; margin-top: 10px;">El teu compte no està creat. Per favor, verifica les teves dades o registra\'t.</div>';
            }
            ?>
            <?php
            // Mostrar mensaje si existe el error en la URL
            if (isset($_GET['error']) && $_GET['error'] == 'register') {
                echo '<div style="color: green; margin-top: 10px;">El compte s\'ha creat correctament!</div>';
            }
            ?>
        </form>
        
        <!-- Enlace para registrarse debajo del formulario -->
        <div class="register-link">
            <a href="registre.php?mode=usuari">Registra't aquí com Usuari</a>
            <a> | </a>
            <a href="registre.php?mode=personal">Registra't aquí com Personal</a>
        </div>
    </div>

    <footer class="footer">
        <p>© 2024 LTWS</p>
    </footer>

    <script src="js/paginaweb.js"></script>
</body>

</html>