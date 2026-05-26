<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
   <h1>Deuxième page</h1>


   <?php
   $nom = $_GET['nom'];
    $age = $_GET['age'];
    echo "Bonjour $nom tu as $age ans";
   ?>

</body>
</html>