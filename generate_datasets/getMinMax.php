<?php
$energyData;
$values = array();
$min;
$max;

// Gets the jsonFile from argument in cmd.
if (isset($argc)) {
	for ($i = 0; $i < $argc; $i++) {
        $energyData = file_get_contents($argv[$i]);
        $energyData = json_decode($energyData, true);
	}
}
else {
    echo "No arguments given.\n";
}

for($i = 0; $i < count($energyData); $i++) {
    $tmp = $energyData[$i]["varde"];
    array_push($values, $tmp);
}

$min = min($values);
$max = max($values);

echo $min." ".$max;

$times = array();


foreach($energyData as $arr) {
    array_push($times, $arr['tidpunkt']);
}

echo min($times)."\n";
echo max($times)."\n";

$unixMin = strtotime(min($times));
$unixMax = strtotime(max($times));

// Total days between two dates
$days = unixtojd($unixMax) - unixtojd($unixMin);
echo $days."\n";
    
