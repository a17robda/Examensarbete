<?php
date_default_timezone_set("Europe/Stockholm");

hello();

function setup() {
    echo "WELCOME TO SETUP! \n";
    echo "Create a new preferences file? [y/n]\n";
    $s = readline();
    if(yesNo($s)) {
        echo "Folder name: \n";
        $folder = readline();
        if(!file_exists($folder)) {
            mkdir($folder);
        }
        $separator = checkOS();
        echo "File name \n";
        $fileName = readline();
        $f = fopen("preferences.json", "w");
        $jsonArr = array('folderName' => $folder, 'separator' => $separator, 'fileName' => $fileName);
        fwrite($f, json_encode($jsonArr));
        echo "Preferences file created!";
        hello();
    } else if(!yesNo($s)) {
        if(!file_exists("preferences.json")) {
            echo "No preferences are set, aborting!";
            exit();
        } else {
            hello();
        }
    }
}

function yesNo(&$inp) {
    switch($inp) {
        case "y":
        case "Y":
            return true;
        break;
        case "n":
        case "N":
            return false;
        break;
    }
}

function hello() {
    if(!file_exists("preferences.json")) {
        echo "It seems like there is no preference file present. Please set up one. \n";
        setup();
    } else {
    echo "1. CREATE NEW DATAFILE | 2. SETUP | 3. EXIT\n";
    echo "Selection: \n";
        $selection = (int)readline();
        switch($selection) {
            case 1: 
                initialize();
            break;
            case 2:
                setup();
            break;
            case 3:
                exit();
            break;
            default:
                exit();
            break;
        }
    }
}

function initialize() {
    echo "File size goal(Mb): \n";
    $inp = readline();
    $inp = str_replace(' ', '', $inp);

    try {
        $inp = (int)$inp;
    } catch(Exception $e) {
        echo $e."\n";
    }

    if(is_int($inp) && $inp !=0) {
        echo $inp."Is valid\n";
        confirmGenerate($inp);
    } else if (!is_int($inp)){
        echo $inp."Is NOT an integer (Or 0)\n";
    } else if ($inp == 0) {
        echo "Integer cannot be 0\n";
        hello();
    }

}

function confirmGenerate(&$inp) {
    echo "This will generate a file of ".$inp." Megabytes [".kbGoal($inp)." kilobytes]. Continue? [y/n]\n";
    echo "Continue?: ", $s = readline();
    if (yesNo($s)) {
        echo "\nGenerating data\n", generate($inp * 1024);
    } else if(!yesNo($s)) {
        echo "\nAborting...\n", hello();
    }
}

function returnPreferences() {
    $preferences = file_get_contents("preferences.json");
    $decoded = json_decode($preferences, true);
    return $decoded;
}

// IMPLEMENT FILE SPLITTING AND CREATION
function createFile(&$folderName, &$separator, &$fileName, &$fileSplit) {
    $f = fopen($folderName.$separator.$fileName."_".$fileSplit.".json", "w+");
    fwrite($f, "[");
    return $f;
}

function checkOS() {
    // Check OS
    if (DIRECTORY_SEPARATOR === '/') {
        // Running under Linux 
        return "/";
    } else if (DIRECTORY_SEPARATOR === '\\') {
        // Running under Windows
        return "\\";
    }
}

function generate($kbGoal) {
    // File size
    $kbCount = 0;
    $kbTemp = 0;
    $targetKb = $kbGoal;
    $iter = 0;
    $splitMbLimit = 10;

    $fileSplit = 0;

    // File management
    $folderName = returnPreferences()['folderName'];
    $separator = returnPreferences()['separator'];
    $fileName = returnPreferences()['fileName'];

    $testArray = array('nyckelkod' => 123307, 'period' => 201907, 'tidpunkt' => '2019-07-06T00:00:00+00:00', 'detaljniva' => 'Dag', 'enhet' => 'kWh', 'forbrukningstyp' => 'EL', 'varde' => 82763.784511);
    
    // File name as String
    $fullFileName = $folderName.$separator.$fileName."_".$fileSplit."json";

    mkdir($folderName);

    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);

    $separator;

    $f = createFile($folderName, $separator, $fileName, $fileSplit);
    //fwrite($f, "[");
    while(reachedGoal($kbCount, $targetKb) == false) {
        $iter = $iter + 1;
        $tmpStr = "Number: ".$iter." Kb: ".round($kbCount)." File size: ".filesize($folderName.$separator.$fileName."_".$fileSplit.".json")."\n";
        echo $tmpStr;
        kbSize($testArray, $kbCount, $kbTemp);
        fwrite($f, json_encode($testArray));
        if(reachedGoal($kbCount, $targetKb) == false && (round($kbTemp) % 10240 != 0) || round($kbTemp) == 0) {
            fwrite($f, ",\n");
        }
        if(round($kbTemp) % 10240 == 0 && round($kbTemp) != 0) {
            $kbTemp = 0;
            echo "Splitting file!\n";
            fwrite($f, "]");
            fclose($f);
            $fileSplit++;
            $f = createFile($folderName, $separator, $fileName, $fileSplit);
        }
        
    }
    finalize($f, $timeNOW, $dateNOW);
}

// Bytes to kilobytes count
function kbSize(&$inp, &$kbCount, &$kbTemp) {
        foreach ($inp as $k => $v) {
            $kbCount += strlen($k) * 0.00124;
            $kbCount += strlen($v) * 0.00124;
            $kbTemp += strlen($k) * 0.00124;
            $kbTemp += strlen($v) * 0.00124;
        }
}

function kbGoal($mb) {
    return $mb * 1024; 
}

function reachedGoal($kbCount, $targetKb) {
    if($kbCount >= $targetKb) {
        return true;
    } else {
        return false;
    }
}

function finalize(&$f, &$timeNOW, &$dateNOW) {
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
