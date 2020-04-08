<?php

$server = "localhost";
$user = "admin";
$password = "123";

echo "Hello world";
echo '
    <form action="" method="POST">
    <input type="radio" id="mysql" name="aggregation" value="mysql">
    <label for="mysql">MySQL</label><br>
    <input type="radio" id="spark" name="aggregation" value="spark">
    <label for="spark">Apache Spark</label><br>
    <input type="submit">
    </form>
';

$aggChoice = $_POST['aggregation'];

if(!empty ($aggChoice)) { 
    echo $aggChoice;
    if($aggChoice == "mysql") {
        echo "Mysql chosen";
        try {
            $conn = new PDO("mysql:host=$server;dbname=exjobb", $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected!";
        } catch (PDOException $e) {
            echo $e;
        }
    } else if($aggChoice == "spark") {
        echo "Apache Spark chosen";
        $output = shell_exec('~/Documents/Examensarbete/sparkTests/launch.sh');
        //echo $output;

        foreach (glob("/home/robin/Documents/Examensarbete/sparkTests/sparkOut.json/*.json") as $file) {
            $js = file_get_contents($file);
            $decoded = json_decode($js, true);
        }
    }
} else {
    echo "No choice";
}

?>