<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Liste des films de la BDD</h1>
    <table>
        <tr>
            <th> # </th>
            <th>Titre</th>
            <th>Nom Réalisateur</th>
            <th>Prénom Réalisateur</th>
            <th>Genre</th>
            <th>Année de sortie</th>
            <th>Recettes</th>
            <th><img src="modif.png" width="40"></th>
            <th><img src="sup.png" width="40"></th>
        </tr>
    
    <?php
    // Connexion au serveur de bases de données
    //                    adr serveur, user,   mdp,   base de données
    $id = mysqli_connect("localhost", "root", "root", "cinema");
    // Execution d'une requête de type SELECT
    $res = mysqli_query($id, "SELECT * FROM film 
                                order by titre");

    // Récupération des résultats
    while($ligne = mysqli_fetch_assoc($res))
    {
        $idfilm = $ligne['idfilm'];
    echo "<tr>
            <td>$idfilm</td>
            <td>".$ligne['titre']."</td>
            <td>".$ligne['nom_realisateur']."</td>
            <td>".$ligne['prenom_realisateur']."</td>
            <td>".$ligne['genre']."</td>
            <td>".$ligne['sortie']."</td>
            <td>".$ligne['recettes']."</td>
            <td><a href='modification.php?idfilm=$idfilm'><img src='modif.png' width='30'></a></td>
            <td><a href='sup.php?idfilm=$idfilm'><img src='sup.png' width='30'></td>
        </tr>";
    }
    ?>
    </table>
</body>
</html>