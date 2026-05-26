<?phpif(isset($_POST("inscription")))
{
$nom=$_POST("nom");
$prenom=$_POST("prenom");
$mail=$_POST("mail");
$mdp=$_POST("mdp");
$id= mysqli_connect("localhost","root","root","cinemas");
$requete="INSERT INTO users(nom,prenom,mail,mdp)
          values('$nom','$prenom','$mail','$mdp')";
mysqli_query($id, $requete);
echo"Inscription réussite ! Vous pouvez maintenant vous connecter....";
header("refresh:3;url=connexion.php");            

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Formulaire dinscription</h1><hr>
    <from action="" method="post">
      <input type="text" name="nom" placeholder="votre nom"><br><br>
      <input type="text" name="prenom" placeholder="votre prenom"><br><br>
      <input type="email" name="mail" placeholder="votre mail"><br><br>
      <input type="password" name="mdp" placeholder="votre mot de passe"><br><br>
      <input type="submit" value="S'INSCRIRE" name="inscription"><br><br>
</from><hr>
        </body>
        </html>