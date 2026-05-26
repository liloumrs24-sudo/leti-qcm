<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1> Liste des films de la BDD </h1>
    <?php
    // connexion a la base de donné
    //                    adr serveur user   pwd     bdd
    $id = mysqli_connect("localhost","root","root","cinema");
    //requete de cnx
    $result = mysqli_query($id,"SELECT  * FROM film");
    $ligne =mysqli_fetch_assoc($result);
    echo $ligne["titre"] ;   
    ?>
</body>
</html>