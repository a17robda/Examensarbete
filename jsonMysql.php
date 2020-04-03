<?php

$folderPath = 'generate_datasets/outputJSON';
$files = scandir($folderPath);
$files = array_diff($files, array('.', '..'));

$server = "localhost";
$user = "admin";
$password = "123";


try {
    $conn = new PDO("mysql:host=$server;dbname=exjobb", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!";
    $stmt = $conn->prepare("INSERT INTO jsontable (nyckelkod)
    VALUES (:nyckelkod)");
    $stmt->bindParam(':nyckelkod', $kod);

    foreach($files as $f) {
        $js = file_get_contents($folderPath."/".$f);
        $decoded = json_decode($js, true);
        foreach($decoded as $arr) {
            foreach($arr as $k => $v) {
                    if($k == "nyckelkod") {
                        $kod.= '{"'.$k.'"'.':'.$v.",";
                    }
                    if($k == "tperiod") {
                        $kod.= '"'.$k.'"'.':'.'"'.$v.'"'.",";
                    }
                    if($k == "tstamp") {
                        $kod.= '"'.$k.'"'.':'.'"'.$v.'"'.",";
                    }
                    if($k == "tunit") {
                        $kod.= '"'.$k.'"'.':'.'"'.$v.'"'.",";
                    }
                    if($k == "tvalue") {
                        $kod.= '"'.$k.'"'.':'.$v."}";
                    }
            }
            echo $kod."\n";
            $stmt->execute();
            $kod = "";
        }
    }
}
catch(PDOException $e)
    {
    echo $e->getMessage();
}





