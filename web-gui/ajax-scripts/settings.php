<?php

// column position of data in status and log files
define("TIME_STAMP_COL",	0);
define("ROOM_TEMP_COL",		1);
define("BOILER_TEMP_COL",	2);
define("INLET_TEMP_COL",	3);
define("OUTLET_TEMP_COL",	4);
define("POWER_COL",			5);
define("HEATER_1_COL",		6);
define("HEATER_2_COL",		7);
define("RATE_COL",			8);
define("BOOST_COL",			9);

// plot setings
define("MIN_SAMPLES", 4);
define("MAX_SAMPLES", 1440);
define("DEFAULT_SAMPLES", 180);

// directory and file settings
$dataDir = '/var/www/data/';
$reqDir = '/var/www/req/';
$statusFile = 'status.csv';
$reqFile = 'new_requests.csv';
$logFileExt = '_log.csv';

?>
