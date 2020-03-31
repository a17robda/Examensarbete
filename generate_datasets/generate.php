<?php
// Set timezone
date_default_timezone_set("Europe/Stockholm");

// Fix the seed to 1024
srand(1024);

hello();

// Setup
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

// Generic yes/no
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

// Start menu
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

// Initialize
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

// Confirm generation
function confirmGenerate(&$inp) {
    echo "This will generate a file of ".$inp." Megabytes [".kbGoal($inp)." kilobytes]. Continue? [y/n]\n";
    echo "Continue?: ", $s = readline();
    if (yesNo($s)) {
        echo "\nGenerating data\n", generate($inp * 1024);
    } else if(!yesNo($s)) {
        echo "\nAborting...\n", hello();
    }
}

// Return preferences created from setup function
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

// Checks the operating system structure
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

// Generate a pseudorandom array
function randArray(&$keycode, &$year, &$day, &$month, &$hour, &$minute) {
    // Timestamp
    if((int)$month < 10) {
        $month = "0"+$month;
    }
    if((int)$month > 12) {
        $month = 0;
        $year++;
    }
    if((int)$day < 10) {
        $day = "0".$day;
    }
    if((int)$day > 31) {
        $day = "00";
        $month++;
    }
    if((int)$hour < 10) {
        $hour = "0".$hour;
    }
    if((int)$hour > 24) {
        $hour = "00";
        $day++;
    }
    if((int)$minute < 10) {
        $minute = "0".$minute;
    }
    if((int)$minute > 59) {
        $minute = "00";
        $hour++;
    }

    $rndValue = rand(44872.13, 665063.04);

    $randArr = array('nyckelkod' => $keycode, 'period' => $year.$month, 'timestamp' => gmdate('c', mktime($hour,$minute,0,$day,$month,$year)), 'unit' => 'kWh', 'value' => $rndValue);
    
    $keycode++;
    $minute = $minute + 5;

    return $randArr;
}

// Generate arrays and split files accordingly
function generate($kbGoal) {
    // File size
    $kbCount = 0;
    $kbTemp = 0;
    $targetKb = $kbGoal;
    $iter = 0;
    $splitMbLimit = 10;

    $fileSplit = 0;

    // Array vars (nonrandom)
    $keycode = 1000000; // "ID"
    $year = 2019;
    $month = 1;
    $day = 1;
    $hour = 0;
    $minute = 0;

    $mTime;
    $detail;
    $unit;
    $usageType;

    //
    
    // File management
    $folderName = returnPreferences()['folderName'];
    $separator = returnPreferences()['separator'];
    $fileName = returnPreferences()['fileName'];

    //$testArray = array('nyckelkod' => 123307, 'period' => 201907, 'tidpunkt' => '2019-07-06T00:00:00+00:00', 'detaljniva' => 'Dag', 'enhet' => 'kWh', 'forbrukningstyp' => 'EL', 'varde' => 82763.784511);
    
    // File name as String
    $fullFileName = $folderName.$separator.$fileName."_".$fileSplit."json";

    mkdir($folderName);

    // Time measurements
    $timeNOW = time();
    $dateNOW = date('Y-m-d h:i:sa', $timeNOW);

    $separator;

    $f = createFile($folderName, $separator, $fileName, $fileSplit);
    while(reachedGoal($kbCount, $targetKb) == false) {
        $theArray = randArray($keycode, $year, $day, $month, $hour, $minute);

        $iter = $iter + 1;
        $tmpStr = "Number: ".$iter." Kb: ".round($kbCount)." File size: ".filesize($folderName.$separator.$fileName."_".$fileSplit.".json")."\n";
        echo $tmpStr;
        //echo gmdate('c', mktime(25,59,59,11,3,2019))."\n";
        kbSize($theArray, $kbCount, $kbTemp);
        fwrite($f, json_encode($theArray));
        if(reachedGoal($kbCount, $targetKb) == false && (round($kbTemp) % (10 * 10000) != 0) || round($kbTemp) == 0) {
            fwrite($f, ",\n");
        }
        if(round($kbTemp) % (10 * 10000) == 0 && round($kbTemp) != 0) {
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

// The kilobyte goal from megabytes
function kbGoal($mb) {
    return $mb * 1000; 
}

// Check if we have reached the total file goal
function reachedGoal($kbCount, $targetKb) {
    if($kbCount >= $targetKb) {
        return true;
    } else {
        return false;
    }
}

// Finalize the file - last thing called
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
