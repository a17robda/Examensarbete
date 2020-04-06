<?php

$folderPath = 'generate_datasets/outputJSON';
$files = scandir($folderPath);
$files = array_diff($files, array('.', '..'));

$server = "localhost";
$user = "admin";
$password = "123";

$timeNOW;
$dateNOW;

try {
    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);
    $conn = new PDO("mysql:host=$server;dbname=exjobb", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!";

    foreach($files as $f) {
        $qmarks = "";
        $valArr = array();
        $js = file_get_contents($folderPath."/".$f);
        $decoded = json_decode($js, true);
        // Last array in file
        $lastArr = end($decoded);
        $valueCounter = 0;
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
            array_push($valArr, $kod);
            // Dynamic placeholders
            if($arr["nyckelkod"] != $lastArr["nyckelkod"]) {
                $qmarks.= "(?),";
            } else {
                $qmarks.= "(?)";
            }
            $kod = "";
        }
        //var_dump($valArr);
        $sql = "INSERT INTO jsontable (nyckelkod) VALUES {$qmarks}";
        $stmt = $conn->prepare($sql);
        $stmt->execute($valArr);
    }
}
catch(PDOException $e)
    {
    echo $e->getMessage();
}

$timeDONE = time();
$dateDONE = date('Y-m-d h:i:sa', $timeDONE);

echo "Insertion started: ".$dateNOW."\n";
echo "Insertion complete: ".$dateDONE."\n";

$secondsElapsed = $timeDONE - $timeNOW;
$minutesElapsed = round($secondsElapsed / 60, 1);

echo "Seconds elapsed: ".$secondsElapsed." seconds.\n";
echo "Minutes elapsed: ".$minutesElapsed." minutes.\n";







