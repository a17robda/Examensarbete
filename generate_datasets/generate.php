<?php
date_default_timezone_set("Europe/Stockholm");

hello();

function hello() {
    /*
    echo "Target file size(Gb) [INTEGER ONLY]: \n";
    $inp = readline();
    $inp = (int)$inp;
    generate(kbGoal($inp));
    */
    generate(100);
}

function generate($kbGoal) {
    $baseFileName = "testout";

    $testArray = array('nyckelkod' => 123307, 'period' => 201907, 'tidpunkt' => '2019-07-06T00:00:00+00:00', 'detaljniva' => 'Dag', 'enhet' => 'kWh', 'forbrukningstyp' => 'EL', 'varde' => 82763.784511);
    $kbCount = 0;
    $kbTemp = 0;
    $targetKb = $kbGoal;
    $iter = 0;

    // File management
    $currFile = "testout";
    $fileSplit = 0;

    $folderName = "outputJSON";
    mkdir($folderName);

    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);

    $separator;

    // Check OS
    if (DIRECTORY_SEPARATOR === '/') {
        // Running under Linux 
        $separator = "/";
    } else if (DIRECTORY_SEPARATOR === '\\') {
        // Running under Windows
        $separator = "\\";
    }

    $f = fopen($folderName.$separator.$currFile."_".$fileSplit.".json", "w");
    fwrite($f, "[");
    while(reachedGoal($kbCount, $targetKb) == false) {
        $iter = $iter + 1;
        $tmpStr = "Number: ".$iter." Kb: ".$kbCount."\n";
        echo $tmpStr;
        kbSize($testArray, $kbCount);
        fwrite($f, json_encode($testArray));
        if(reachedGoal($kbCount, $targetKb) == false) {
            fwrite($f, ",\n");
        }
        splitFile($f, $fileSplit);
    }
    finalize($f, $timeNOW, $dateNOW);
}


function splitFile(&$f, &$fileSplit) {

}


function kbSize($inp, &$kbCount) {
        foreach ($inp as $k => $v) {
            $kbCount += strlen($k) * 0.00124;
            $kbCount += strlen($v) * 0.00124;
            //echo strlen($k)." ".strlen($v). " \n";
        }
}

function kbGoal($gb) {
    return $gb * 1000000; 
}

function reachedGoal($kbCount, $targetKb) {
    if($kbCount >= $targetKb) {
        return true;
    } else {
        return false;
    }
}

function finalize($f, $timeNOW, $dateNOW) {
    $timeDONE = time();
    $dateDONE = date('Y-m-d h:i:sa', $timeDONE);

    echo "Generation started: ".$dateNOW."\n";
    echo "Generation complete: ".$dateDONE."\n";

    $secondsElapsed = $timeDONE - $timeNOW;
    $minutesElapsed = round($secondsElapsed / 60, 1);

    echo "Seconds elapsed: ".$secondsElapsed." seconds.\n";
    echo "Minutes elapsed: ".$minutesElapsed." minutes.\n";
    
    fwrite($f, "]");
    fclose($f);

    clearstatcache();
}
