<?php

include('settings.php');

// Set default values
$numberOfSamples = DEFAULT_SAMPLES;
$col = BOILER_TEMP_COL;
$className = 'unknown';
$sampleOffset = 0;

if(isset($_GET['channel'])) {
	switch ($_GET['channel']) {
		case 'boiler':
			$col = BOILER_TEMP_COL;
			$className = $_GET['channel'];
			break;
		case 'inlet':
			$col = INLET_TEMP_COL;
			$className = $_GET['channel'];
			break;
		case 'outlet':
			// TODO change to outlet when this column is available
			$col = OUTLET_TEMP_COL;
			$className = $_GET['channel'];
			break;
		case 'room':
			$col = ROOM_TEMP_COL;
			$className = $_GET['channel'];
			break;
		default:
			$col = BOILER_TEMP_COL;
			break;		
	}
}


if(isset($_GET['span'])) {
	if (intval($_GET['span']) >= MIN_SAMPLES)
		$numberOfSamples = $_GET['span'];
	if ($numberOfSamples > MAX_SAMPLES*10)
		$numberOfSamples = MAX_SAMPLES*10;
}

if(isset($_GET['offset'])) {
	if (intval($_GET['offset']) < 10000)
		$sampleOffset = $_GET['offeset'];
}

// show at least 90 samples in a graph
$showEvery = intval(ceil($numberOfSamples/90));

$sampleAverage = 0;
$sampleCount = 0;

$filedate = time();

function getNextFileName() {
	global $filedate,$logFileExt;
	$fn = "/var/www/data/".date('Ymd', $filedate).$logFileExt;
	$filedate = $filedate - 86400;
	return $fn;
}

$p_data = array();
$n = 0;
$lines = 0;
$samples = $numberOfSamples;
$samplesDone = 0;

while ($samplesDone != $numberOfSamples) {
	$logFile = file(getNextFileName());
	
	if (($numberOfSamples-$samplesDone) > count($logFile))
		$last = 0;
	else
		$last = count($logFile) - ($numberOfSamples-$samplesDone);
	
	for ($i = count($logFile)-1; $i >= $last; $i--) {
		if ($samplesDone % $showEvery == 0) {
			$linedata = explode(",", $logFile[$i]);
			$sampleAverage += floatval($linedata[$col]);
			$sampleCount++;
			if ($sampleCount == $showEvery)
				array_push($p_data, array("x"=>date('y-m-d H:i', $linedata[0]), "y"=>$sampleAverage/$showEvery));
			$sampleAverage = 0;
			$sampleCount = 0;
		}
		else {
			$linedata = explode(",", $logFile[$i]);
			$sampleAverage += floatval($linedata[$col]);
			$sampleCount++;
		}
		$samplesDone++;
	}
	$n++;
}

$main = array("showEvery"=>$showEvery, "className"=>".".$className."-temp-channel", "data"=>array_reverse($p_data));
$general = array("xScale"=> "time", "yScale"=>"linear", "main"=>array($main));
$json = json_encode($general);
echo $_GET['callback']."(".$json.");";	// Add callback to responce	
//echo '<h3>'.$samplesDone.'</h3>';
;?>
