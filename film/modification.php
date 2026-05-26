<?php
$id = mysqli_connect("localhost", "root", "root", "cinema");
if(isset($_POST["modifier"])){

    //echo "<pre>";
    //var_dump($_POST);
    //echo "</pre>";

    $nom_realisateur = $_POST["nom_realisateur"];
    $prenom_realisateur = $_POST["prenom_realisateur"];
    $titre = $_POST["titre"];
    $genre = $_POST["genre"];
    $sortie = $_POST["sortie"];
    $recettes = $_POST["recettes"];
    $idfilm = $_POST["idfilm"];
    $requete = "update film set titre = '$titre',
                                nom_realisateur = '$nom_realisateur',
                                prenom_realisateur = '$prenom_realisateur',
                                genre = '$genre',
                                sortie = '$sortie',
                                recettes = $recettes
                where idfilm = $idfilm";
    mysqli_query($id, $requete);
    header("location:listeFilms.php");
}
$idfilm = $_GET['idfilm'];
$resultat = mysqli_query($id, "select * FROM film WHERE idfilm = $idfilm");
$ligne = mysqli_fetch_assoc($resultat);
$titre = $ligne["titre"]; 
$nom_realisateur = $ligne["nom_realisateur"]; 
$prenom_realisateur = $ligne["prenom_realisateur"]; 
$genre = $ligne["genre"]; 
$sortie = $ligne["sortie"]; 
$recettes = $ligne["recettes"]; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Modification du film : <?=$titre?></h1>
    <form action="" method="post">
         <input type="hidden" name="idfilm" value="<?=$idfilm?>"> 
       Titre:  <br> <input type="text" name="titre" value="<?=$titre?>"><br><br>
       Nom du réalisateur :   <br> <input type="text" name="nom_realisateur" value="<?=$nom_realisateur?>"><br><br>
       Prénom du réalisateur :  <br> <input type="text" name="prenom_realisateur" value="<?=$prenom_realisateur?>"><br><br>
       Genre :  <br> <input type="text" name="genre" value="<?=$genre?>"><br><br>
       Sortie:  <br> <input type="text" name="sortie" value="<?=$sortie?>"><br><br>
       Recettes :  <br> <input type="text" name="recettes" value="<?=$recettes?>"><br><br>
       <input type="submit" value="MODIFIER" name="modifier">
    </form>
</body>
</html>