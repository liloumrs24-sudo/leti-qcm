<?php
$id = mysqli_connect("localhost", "root", "root", "cinema");
$idfilm = $_GET['idfilm'];
mysqli_query($id, "DELETE FROM film WHERE idfilm = $idfilm");
//Redirection vers la page listeFilms.php
header("location:listeFilms.php");
?>