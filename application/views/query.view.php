<?php

$database = "exjobb";
$aggChoice;
$complexity;
$dataSize;

$iter;

function databaseSelector($size) {
    switch($size) {
        case "one":
            return "_1";
        break;
        case "ten":
            return "_10";
        break;
        case "fifty":
            return "_50";
        break;
    }
}

// Aggregation choice

if(!empty($_POST)) {

    $dataSize = $_POST['dataSize'];
    $aggChoice = $_POST['aggregation'];
    $complexity = $_POST['queryChoice'];
    $iter = (int)$_POST['iteration'];

    $dbSize = databaseSelector($dataSize);

    switch ($aggChoice) {
        case "mysql":
            $qstr = query("mysql", $iter, $complexity, $dbSize);
            try {
                $conn = new PDO("mysql:host=localhost;dbname=$database"."$dbSize;charset=UTF8;", "admin", "123");
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $conn->query($qstr);
                $data = $stmt->fetchall(PDO::FETCH_ASSOC);
                render($data, "mysql");
            } catch (PDOException $e) {
                echo $e;
            }
        break;
        case "spark":
            $qstr = query("spark", $iter, $complexity, $dbSize);
            $output = shell_exec($qstr);
            foreach (glob("/home/robin/Documents/Examensarbete/sparkTests/sparkOut.json/*.json") as $file) {
                clearstatcache();
                // File is not empty
                if(filesize($file)) {
                    $data = fopen($file, "r");
                    render($data, "spark");
                }
    }
    break;
}
} else {
    
}

function query($platform, &$iter, $complexity, $dbSize) {
    if($platform == "mysql") {
        $maxval = file_get_contents("/home/robin/Documents/Examensarbete/generate_datasets/outputJSON{$dbSize}/max/maxId.txt");
        srand($iter);
        $maxrand = rand(0, $maxval);
        switch($complexity) {
            case "simple":
                return 'SELECT * FROM jsontable WHERE jsonrow->>"$.tkeycode" = '.$maxrand;
            break;
            case "complex":
                return 'SELECT MAX(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS maximum,
                MIN(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS minimum,
                AVG(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS average,
                STD(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS std_dev,
                VARIANCE(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS variance
                FROM jsontable WHERE jsonrow->>"$.tkeycode" < '.$maxrand;
            break;
            case "batch":
                return 'SELECT jsonrow FROM jsontable WHERE jsonrow->>"$.tkeycode" < '.$maxrand.' ORDER BY CAST(jsonrow->>"$.tkeycode" AS UNSIGNED) DESC LIMIT 100';
            break;
        }
    }
    if ($platform == "spark") {
        switch($complexity) {
            case "simple":
                return '~/Documents/Examensarbete/sparkTests/launch.sh 0 '.$iter.' '.$dbSize;
            break;
            case "complex":
                return '~/Documents/Examensarbete/sparkTests/launch.sh 1 '.$iter.' '.$dbSize;
            break;
            case "batch":
                return '~/Documents/Examensarbete/sparkTests/launch.sh 2 '.$iter.' '.$dbSize;
            break;
        }
    }
}

function render($data, $platform) {
    $table = "<table id='outTable'>";
    $table .= "<tr>
        <th>Keycode</th>
        <th>Timestamp</th>
        <th>Unit</th>
        <th>Value</th>
    <tr>";
    if($platform == "mysql") {
        $tmpArr = array();
        foreach($data as $arr) {
            foreach($arr as $k => $v) {
                $js = json_decode($v, true);
                $table.= "
                <tr>
                    <td>{$js['tkeycode']}</td>
                    <td>{$js['tstamp']}</td>
                    <td>{$js['tunit']}</td>
                    <td>{$js['tvalue']}</td>
                </tr>
                ";
            }
        }
    }
    if($platform == "spark") {
        while(!feof($data))  {
            $result = fgets($data);
            $js = json_decode($result, true);
            $table.= "
            <tr>
                <td>{$js['tkeycode']}</td>
                <td>{$js['tstamp']}</td>
                <td>{$js['tunit']}</td>
                <td>{$js['tvalue']}</td>
            </tr>
            ";
          }
        fclose($data);
        $table.="</table>";
    }
    echo $table;
}

?>