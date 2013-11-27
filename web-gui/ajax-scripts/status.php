<?php

include('settings.php');

$error = "";
$message = "";

$content = file_get_contents($dataDir.$statusFile);
if ($content == FALSE) {
	$error .= "Status file read error";
}
else {
	$status = explode(",", $content);
	$temp = $status[BOILER_TEMP_COL];
	$inlet = $status[INLET_TEMP_COL];
	$outlet = $status[OUTLET_TEMP_COL];
	$room = $status[ROOM_TEMP_COL];
	$power = $status[POWER_COL];

	$heater_1 = $status[HEATER_1_COL] == "1" ? "on" : "off";
	$heater_2 = $status[HEATER_2_COL] == "1" ? "on" : "off";
	$rate = $status[RATE_COL] == "1" ? "high" : "low";
	$boost = $status[BOOST_COL] == "1" ? "1" : "0";
	if(isset($_GET['boost']) && $_GET['boost'] != $boost) {
		$boost = "-1";
	}
}

if(forwardRequest()) {
	$fh = fopen($reqDir.$reqFile, 'a');
	if ($fh) {
		$stringData = strval(time()).','.$_SERVER['REMOTE_ADDR'];
		foreach ( $_GET as $key => $value ) {
			$stringData .= ','.$key.','.$value;
		}
		$stringData .= "\n";
		fwrite($fh, $stringData);
		fclose($fh);
		$message .= "> forwarded" ;
	}
	else {
		$message .= "> unable to forward request";
	}
}

$general =	array("time"=> $status[TIME_STAMP_COL],
						"boiler_temp"=>$temp,
						"room_temp"=>$room,
						"inlet_temp"=>$inlet,
						"outlet_temp"=>$outlet,
						"power"=>$power,
						"rate"=>$rate,
						"heater_1"=>$heater_1,
						"heater_2"=>$heater_2,
						"boost"=>$boost,
						"error"=>$error,
						"message"=>$message
				);

$statusJsonString = json_encode(array("boiler"=>$general));
echo $_GET['callback']."(".$statusJsonString.");";	// Add callback to responce


// Check if a request needs to be forwarded to the controller
function forwardRequest() {
	$retVal = false;
	foreach ( $_GET as $key => $value ) {
		if ($key == 'boost') {
			$retVal = true;
		}
	}
	return $retVal;
}
;?>
