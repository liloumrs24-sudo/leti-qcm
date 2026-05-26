<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Première page php</h1><hr>
    <?php
        echo "Bonjour tout le monde !<br>";
        echo date("d - m - Y H:i:s");
        
    
    ?>

    <select name="" id="">
        <?php
        $an = date("Y")-120;
        for ($i=$an; $i <= $an+120 ; $i++) {
            echo "<option value='$i'>$i</option>";
        }       
        ?>
    </select>


</body>
</html>