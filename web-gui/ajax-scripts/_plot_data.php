<?php

include('settings.php');

$numberOfSamples = DEFAULT_SAMPLES;
$col = BOILER_TEMP_COL;
$className = 'unknown';

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
			$col = INLET_TEMP_COL;
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
	if ($numberOfSamples > MAX_SAMPLES)
		$numberOfSamples = MAX_SAMPLES;
}

$file = file($dataDir.date('Ymd').$logFileExt);
$p_data = array();

// show at least 90 samples in a graph
$showEvery = intval(ceil($numberOfSamples/90));
$numberOfLines = count($file);

$sampleAverage = 0;
$sampleCount = 0;

// if there are not enough samples into todays log file
// read remaining samples from yesterdays log file
if ($numberOfSamples > $numberOfLines) {
	$prevSamples = $numberOfSamples - $numberOfLines;
	$prev_file = file("/var/www/data/".date('Ymd', time() - (86400)).$logFileExt);
	for ($i = count($prev_file)-$prevSamples; $i < count($prev_file); $i++) {
		if ($i % $showEvery == 0) {
			$linedata = explode(",", $prev_file[$i]);
			//$sampleAverage += floatval($linedata[BOILER_TEMP_COL]);
			$sampleAverage += floatval($linedata[$col]);
			$sampleCount++;
			if ($sampleCount == $showEvery)
				array_push($p_data, array("x"=>date('y-m-d H:i', $linedata[0]), "y"=>$sampleAverage/$showEvery));
			$sampleAverage = 0;
			$sampleCount = 0;
		}
		else {
			$linedata = explode(",", $prev_file[$i]);
			//$sampleAverage += floatval($linedata[BOILER_TEMP_COL]);
			$sampleAverage += floatval($linedata[$col]);
			$sampleCount++;
		}
	}
	$numberOfSamples = $numberOfSamples - $prevSamples;
}

for ($i = $numberOfLines - $numberOfSamples; $i < $numberOfLines; $i++) {
	if ($i % $showEvery == 0) {
		$linedata = explode(",", $file[$i]);
		//$sampleAverage += floatval($linedata[BOILER_TEMP_COL]);
		$sampleAverage += floatval($linedata[$col]);
		$sampleCount++;
		if ($sampleCount == $showEvery)
			array_push($p_data, array("x"=>date('y-m-d H:i', $linedata[0]), "y"=>$sampleAverage/$showEvery));
		$sampleAverage = 0;
		$sampleCount = 0;
	}
	else {
		$linedata = explode(",", $file[$i]);
		//$sampleAverage += floatval($linedata[BOILER_TEMP_COL]);
		$sampleAverage += floatval($linedata[$col]);
		$sampleCount++;
	}
}

$main = array("showEvery"=>$showEvery, "className"=>".".$className."-temp-channel", "data"=>$p_data);
$general = array("xScale"=> "time", "yScale"=>"linear", "main"=>array($main));
$json = json_encode($general);
echo $_GET['callback']."(".$json.");";	// Add callback to responce	
;?>
