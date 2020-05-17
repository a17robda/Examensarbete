<?php

// PATH
$dataFolder = "outputJSON_1";
$prependFolder = "application/mysqlQueries/";
$queryFolder = $dataFolder."_queries";
$folderPath = 'generate_datasets/'.$dataFolder;
// Files in directory
$files = scandir($folderPath);
// Exclusion of directories
$files = array_diff($files, array('.', '..', 'max'));

// Database-credentials
$server = "localhost";
$user = "admin";
$password = "123";
$database = "exjobb_1";

// Number separator for table names
$tableCount = 0;
$tableInsert = -1;
$tableIterator = 0;

$timeNOW;
$dateNOW;

try {
    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);
    $conn = new PDO("mysql:host=$server;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!";

    // Create subfolders
    if(!file_exists($prependFolder)) {
        mkdir($prependFolder);
    }
    if (!file_exists($prependFolder.$queryFolder)) {
        mkdir($prependFolder.$queryFolder);
    }
    
    // Create query files
    $simpleQueryFile = fopen($prependFolder.$queryFolder."/simple.txt", "w+");
    $complexQueryFile = fopen($prependFolder.$queryFolder."/complex.txt", "w+");
   
    // Write the initial SELECT statement
    fwrite($simpleQueryFile, "SELECT * FROM (");
    fwrite($complexQueryFile,
    'SELECT AVG(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS average,
    SUM(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS sum_energy,
    MAX(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS maximum,
    MIN(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS minimum,
    STD(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS std_dev,
    VARIANCE(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS variance FROM (');

    // Keep track of iterations
    $foreachCount = 0;
    // Iterate through all files in folder
    foreach($files as $f) {
        $foreachCount++;

        // Create a new table every 4 files inserted.
        if($tableIterator % 40 == 0) {
        // Create new table
        $createDbSql = "CREATE TABLE jsontable_".(string)$tableCount."(
            id int AUTO_INCREMENT,
            jsonrow JSON,
            PRIMARY KEY(id)
            ) ENGINE=InnoDB;
        ";
        $conn->exec($createDbSql);
        // Write simple query
        $alias = "t".(string)$tableCount;
        $query = "SELECT {$alias}.id, {$alias}.jsonrow from jsontable_".$tableCount." {$alias}";
        if($tableIterator != count($files) && $tableIterator + 40 <= count($files)) {
            $query.= " UNION ALL ";
        }
        // Write to queries
        fwrite($simpleQueryFile, $query);
        fwrite($complexQueryFile, $query);

        // Increment
        $tableInsert++;
        $tableCount++;
        }
        // Finish query files
        if($foreachCount == count($files)) {
            fwrite($simpleQueryFile, ') tAlias where jsonrow->>"$.tkeycode" = |x|');
            fwrite($complexQueryFile, ') tAlias where jsonrow->>"$.tstamp" BETWEEN "1976-12-31%" AND "|x|%"');
        }

        ///*
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
        $sql = "INSERT INTO jsontable_".$tableInsert." (jsonrow) VALUES {$qmarks}";
        $stmt = $conn->prepare($sql);
        $stmt->execute($valArr);
        //*/
        // Increase table counter
        $tableIterator++;
    }
    echo "Foreach: ".$foreachCount;
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