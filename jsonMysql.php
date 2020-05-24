<?php

// PATH
$dataFolder = "outputJSON_5";
$tableName = "jsontable_1";
$folderPath = 'generate_datasets/'.$dataFolder;
// Files in directory
$files = scandir($folderPath);
// Exclusion of directories
$files = array_diff($files, array('.', '..', 'max'));

// Database-credentials
$server = "localhost";
$user = "admin";
$password = "123";
$database = "exjobb_5";

$timeNOW;
$dateNOW;

try {
    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);
    $conn = new PDO("mysql:host=$server;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!";

    $createDbSql = "CREATE TABLE {$tableName}(
        id int AUTO_INCREMENT,
        jsonrow JSON,
        PRIMARY KEY(id)
        ) ENGINE=InnoDB;
    ";
    $conn->exec($createDbSql);

    // Iterate through all files in folder
    foreach($files as $f) {
        $qmarks = "";
        $valArr = array();
        $js = file_get_contents($folderPath."/".$f);
        $decoded = json_decode($js, true);
        // Last array in file
        $lastArr = end($decoded);
        foreach($decoded as $arr) {
            foreach($arr as $k => $v) {
                    if($k == "tkeycode") {
                        $insert.= '{"'.$k.'"'.':'.$v.",";
                    }
                    if($k == "tstamp") {
                        $insert.= '"'.$k.'"'.':'.'"'.$v.'"'.",";
                    }
                    if($k == "tunit") {
                        $insert.= '"'.$k.'"'.':'.'"'.$v.'"'.",";
                    }
                    if($k == "tvalue") {
                        $insert.= '"'.$k.'"'.':'.$v."}";
                    }
            }
            echo $insert."\n";
            array_push($valArr, $insert);
            // Dynamic placeholders
            if($arr["tkeycode"] != $lastArr["tkeycode"]) {
                $qmarks.= "(?),";
            } else {
                $qmarks.= "(?)";
            }
            $insert = "";
        }
        $sql = "INSERT INTO {$tableName} (jsonrow) VALUES {$qmarks}";
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