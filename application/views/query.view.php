<?php

$database = "exjobb";

function databaseSelector($size) {
    switch($size) {
        case "one":
            return "_1";
        break;
        case "thirteen":
            return "_13";
        break;
        case "forty":
            return "_40";
        break;
    }
}

function returnQueryFile($dbSize, $complexity) {
    $folderName = "outputJSON".$dbSize."_"."queries";
    return file_get_contents("mysqlQueries/{$folderName}/{$complexity}.txt");
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
                render($data, "mysql", $complexity);
            } catch (PDOException $e) {
                echo $e;
            }
        break;
        case "spark":
            $qstr = query("spark", $iter, $complexity, $dbSize);
            $output = shell_exec($qstr);
            foreach (glob("/home/robin/Documents/Examensarbete/spark/sparkOut.json/*.json") as $file) {
                clearstatcache();
                // File is not empty
                if(filesize($file)) {
                    $data = fopen($file, "r");
                    render($data, "spark", $complexity);
                }
    }
    break;
}
}

// Randomizes a date for the complex MySQL queries
function randomizeDate($maxrand) {
    // Seed the generator
    srand($maxrand);
    $year = (string)rand(1977, 2020);
    $month = rand(1, 12);
    if($month < 10) {
        $month.= "0";
    }
    $day = rand(1, 31);
    if($day < 10) {
        $day.= "0";
    }
    return $year."-".$month."-".$day;
}

// Builds the queries accordingly
function query($platform, &$iter, $complexity, $dbSize) {
    if($platform == "mysql") {
        $maxval = file_get_contents("/home/robin/Documents/Examensarbete/generate_datasets/outputJSON{$dbSize}/max/maxId.txt");
        srand($iter);
        $maxrand = rand(0, $maxval);
        switch($complexity) {
            case "simple":
                $queryFileString = str_replace("|x|", $maxrand, returnQueryFile($dbSize, $complexity));
                return $queryFileString;
            break;
            case "complex":
                $queryFileString = str_replace("|x|", randomizeDate($maxrand), returnQueryFile($dbSize, $complexity));
                return $queryFileString;
            break;
        }
    }
    if ($platform == "spark") {
        switch($complexity) {
            case "simple":
                return '~/Documents/Examensarbete/spark/launch.sh 0 '.$iter.' '.$dbSize;
            break;
            case "complex":
                return '~/Documents/Examensarbete/spark/launch.sh 1 '.$iter.' '.$dbSize;
            break;
        }
    }
}

// Render table and data accordingly
function render($data, $platform, $complexity) {
    $table = "<table id='outTable'>";
    switch($complexity) {
        case "complex":
            $table .= "<tr>
            <th>Maximum</th>
            <th>Minimum</th>
            <th>Average</th>
            <th>Sum Energy</th>
            <th>Standard Deviation</th>
            <th>Variance</th>
            </tr>";
        break;
        default:
            $table .= "<tr>
            <th>Keycode</th>
            <th>Timestamp</th>
            <th>Unit</th>
            <th>Value</th>
            </tr>";
    break;
    }
    
    if($platform == "mysql") {
        $tmpArr = array();
        foreach($data as $arr) {
                if($complexity == "complex") {
                    $table.= "
                    <tr>
                        <td>{$arr['maximum']}</td>
                        <td>{$arr['minimum']}</td>
                        <td>{$arr['average']}</td>
                        <td>{$arr['sum_energy']}</td>
                        <td>{$arr['std_dev']}</td>
                        <td>{$arr['variance']}</td>
                    </tr>
                    ";
                }
            foreach($arr as $k => $v) {
                $js = json_decode($v, true);
                if($complexity != "complex") {
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
    }
    if($platform == "spark") {
        while(!feof($data))  {
            $result = fgets($data);
            $js = json_decode($result, true);
            if($complexity == "complex") {
                $table.= "
                <tr>
                    <td>{$js['maximum']}</td>
                    <td>{$js['minimum']}</td>
                    <td>{$js['average']}</td>
                    <td>{$js['sum_energy']}</td>
                    <td>{$js['std_dev']}</td>
                    <td>{$js['variance']}</td>
                </tr>";
            }
            if($complexity != "complex") {
                $table.= 
                "<tr>
                    <td>{$js['tkeycode']}</td>
                    <td>{$js['tstamp']}</td>
                    <td>{$js['tunit']}</td>
                    <td>{$js['tvalue']}</td>
                </tr>
            ";
            }
        }
        fclose($data);
    }
    $table.="</table>";
    echo $table;
}

?>
