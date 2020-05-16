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
    echo "This will generate a file of ".$inp." Megabytes. Continue? [y/n]\n";
    echo "Continue?: ", $s = readline();
    if (yesNo($s)) {
        $pretty;
        echo "Would you like to pretty-print?\n", $s = readline();
        if(yesNo($s)) {
            $pretty = true;
        } else {
            $pretty = false;
        }
        echo "\nGenerating data\n", generate($inp * 1000, $pretty);
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
    fwrite($f, "[\n");
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
    if((int)$year > 2020) {
        $year = 1977;
    }
    if((int)$month > 12) {
        $month = 1;
        $year++;
    }
    if((int)$day > 31) {
        $day = 1;
        $month++;
    }
    if((int)$hour > 23) {
        $hour = 0;
        $day++;
    }
    if((int)$minute > 59) {
        $minute = 0;
        $hour++;
    }

    $rndValue = rand(3704, 112292);
    // Fixes apparant bug with rand();
    if($rndValue > 112292) {
        $rndValue = 112292;
    }

    $randArr = array('tkeycode' => $keycode, 'tstamp' => gmdate('c',mktime($hour,$minute,0,$month,$day,$year)), 'tunit' => 'kwh', 'tvalue' => $rndValue);
    
    $keycode++;
    $minute = $minute + 30;

    return $randArr;
}

// Generate arrays and split files accordingly
function generate($kbGoal, $pretty) {
    // File size
    $kbCount = 0;
    $kbTemp = 0;
    $targetKb = $kbGoal;
    $iter = 0;
    $splitMbLimit = 10;

    $pretty;

    $fileSplit = 0;
    $mbSplit = 100;

    // Array vars (nonrandom)
    $keycode = 0; // "ID"
    $year = 1977;
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
        kbSize($theArray, $kbCount, $kbTemp);
        fwrite($f, json_encode($theArray));
        if(reachedGoal($kbCount, $targetKb) == false && (round($kbTemp) % ($mbSplit * 1000) != 0) || round($kbTemp) == 0) {
            if($pretty) {
                fwrite($f, ",\n");
            } else {
                fwrite($f, ",");
            }
        }
        if(round($kbTemp) % ($mbSplit * 1000) == 0 && round($kbTemp) != 0) {
            $kbTemp = 0;
            echo "Splitting file!\n";
            fwrite($f, "\n]");
            fclose($f);
            $fileSplit++;
            $f = createFile($folderName, $separator, $fileName, $fileSplit);
        }
        
    }
    finalize($f, $timeNOW, $dateNOW, $keycode);
}

// Bytes to kilobytes count
function kbSize(&$inp, &$kbCount, &$kbTemp) {
        foreach ($inp as $k => $v) {
            $kbCount += strlen($k) * 0.001;
            $kbCount += strlen($v) * 0.001;
            $kbTemp += strlen($k) * 0.001;
            $kbTemp += strlen($v) * 0.001;
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
function finalize(&$f, &$timeNOW, &$dateNOW, &$maxId) {
    $timeDONE = time();
    $dateDONE = date('Y-m-d h:i:sa', $timeDONE);

    echo "Generation started: ".$dateNOW."\n";
    echo "Generation complete: ".$dateDONE."\n";

    $secondsElapsed = $timeDONE - $timeNOW;
    $minutesElapsed = round($secondsElapsed / 60, 1);

    echo "Seconds elapsed: ".$secondsElapsed." seconds.\n";
    echo "Minutes elapsed: ".$minutesElapsed." minutes.\n";
    
    fwrite($f, "\n]");
    fclose($f);

    mkdir(returnPreferences()['folderName']."/max");
    $maxIdFile = fopen(returnPreferences()['folderName']."/max/maxId.txt", "w+");
    fwrite($maxIdFile, $maxId -1);
    fclose($maxIdFile);

    clearstatcache();
}
