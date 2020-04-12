<?php

$aggChoice;
$complexity;

$aggChoice = $_POST['aggregation'];
$complexity = $_POST['queryChoice'];
$iter = (int)$_POST['iteration'];

initialize();

function initialize() {
    buildFront();
}

function buildFront() {
    echo '
    <form action="" method="POST">
    <select name="queryChoice">
    <option value="simple">Simple Query</option>
    <option value="complex">More Complex Query</option>
    </select><br>
    <label for="iteration">Iteration:</label>
    <input type="text" id="iteration" name="iteration"><br>
    <input type="radio" id="mysql" name="aggregation" value="mysql">
    <label for="mysql">MySQL</label><br>
    <input type="radio" id="spark" name="aggregation" value="spark">
    <label for="spark">Apache Spark</label><br>
    <input type="submit">
    </form>
';
}

// Aggregation choice
if(!empty ($aggChoice) && !empty ($complexity)) {
    switch ($aggChoice) {
        case "mysql":
            $qstr = query("mysql", $iter, $complexity);
            try {
                $conn = new PDO("mysql:host=localhost;dbname=exjobb;charset=UTF8;", "admin", "123");
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "Connected!";
                $stmt = $conn->query($qstr);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                render($data, "mysql");
                /*
                $f= fopen("jsontest.json", "w+");
                fwrite($f, $data["jsonrow"]);
                fclose($f);

                $test = file_get_contents("jsontest.json");
                $decode = json_decode($test, true);
                var_dump($decode);
                //echo $test['tvalue'];
                */

            } catch (PDOException $e) {
                echo $e;
            }
        break;
        case "spark":
            $qstr = query("spark", $iter, $complexity);
            $output = shell_exec($qstr);
            foreach (glob("/home/robin/Documents/Examensarbete/sparkTests/sparkOut.json/*.json") as $file) {
                clearstatcache();
                // File is not empty
                if(filesize($file)) {
                    $js = file_get_contents($file);
                    render($js, "spark");
                }
    }
    break;
}
} else {

}

function query($platform, &$iter, $complexity) {
    if($platform == "mysql") {
        $maxval = file_get_contents("../generate_datasets/outputJSON/max/maxId.txt");
        srand($iter);
        $maxrand = rand(0, $maxval);
        switch($complexity) {
            case "simple":
                return 'SELECT * FROM jsontable WHERE jsonrow->>"$.nyckelkod" = '.$maxrand;
            break;
            case "complex":
                return 'SELECT MAX(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS maximum,
                MIN(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS minimum,
                AVG(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS average,
                STD(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS std_dev,
                VARIANCE(CAST(jsonrow->>"$.tvalue" AS UNSIGNED)) AS variance
                FROM jsontable WHERE jsonrow->>"$.nyckelkod" < '.$maxrand;
            break;
        }
    }
    if ($platform == "spark") {
        switch($complexity) {
            case "simple":
                return '~/Documents/Examensarbete/sparkTests/launch.sh 0 '.$iter;
            break;
            case "complex":
                return '~/Documents/Examensarbete/sparkTests/launch.sh 1 '.$iter;
            break;
        }
    }
}

function render($data, $platform) {
    if($platform == "mysql") {
        var_dump($data);
    }
    if($platform == "spark") {
        var_dump($data);
    }


}



?>