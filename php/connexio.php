<?php
$con = mysqli_connect("localhost", "root", "") or die("Localhost no disponible");
$db = mysqli_select_db($con, "BDLOSPACOS") or die("Base de dades no disponible");
?>
