var STATUS_OK = 0;
var STATUS_WARNING = 1;
var STATUS_ERROR = 2;

var boostState = false;
var blocked = false;

var tempTabs = ['boiler', 'inlet', 'outlet', 'room'];

function updateData(data) {
	var color;
	var bg_color;

	// Set Date
	var d = new Date(data.boiler.time*1000);
	$("#timeContainer").html("updated " + d.toLocaleTimeString());
	$("#dateContainer").html(d.toLocaleDateString());
	
	// Set heater controls
	if(data.boiler.heater_1 === "on") {
		color = "White";
		bg_color = "OrangeRed";
	}
	else {
		color = "Black";
		bg_color = "#333";		
	}
	$("#heater1Control").css( "color", color);
	$("#heater1Control").css( "background-color", bg_color);

	if(data.boiler.heater_2 === "on") {
		color = "White";
		bg_color = "OrangeRed";
	}
	else {
		color = "Black";
		bg_color = "#333";
	}
	$("#heater2Control").css( "color", color);
	$("#heater2Control").css( "background-color", bg_color);

	// Set rate control
	if ( data.boiler.rate === "high" ) {
		$("#rateControl").css( "color", "White");
		$("#rateControl").css( "background-color", "OrangeRed");
		$("#rateControl").html( "high");
	} else if ( data.boiler.rate === "low" ) {
		$("#rateControl").css( "color", "White");
		$("#rateControl").css( "background-color", "LawnGreen");
		$("#rateControl").html( "low");
	} else {
		$("#rateControl").css( "color", "Black");
		$("#rateControl").css( "background-color", "#333");
		if (data.boiler.rate!="")
			$("#rateControl").html( data.boiler.rate );
	}

	// Set boost controls
	if(data.boiler.boost == 1) {
		bg_color = "OrangeRed";
		boostState = true;
	} else if (data.boiler.boost == 0) {
		bg_color = "#333";
		boostState = false;
	} else {
		bg_color = "#700000";
	}
	$("#boostControl").css( "background-color", bg_color);
	
	// Set temperature
	var temp = parseFloat(data.boiler.boiler_temp).toFixed(1);
	$("#tempContainer").html(temp+"&deg;C");
	$("#tempContainer").css( "color", valueToColor(temp) );

	// Set shower icon
	if (temp < 25)
		icon = 'shower_cold_smll.png';
	else if (temp > 30)
		icon = 'shower_hot_smll.png';
	else
		icon = 'shower_ok_smll.png';
	$("#tempContainer").css( "background-image", "url('css/images/"+icon+"')" );
	
	temp = parseFloat(data.boiler.inlet_temp).toFixed(1);
	$("#inlet-tab").html("Inlet: "+temp+"&deg;C");

	temp = parseFloat(data.boiler.outlet_temp).toFixed(1);
	temp = '--.-';
	$("#outlet-tab").html("Outlet: "+temp+"&deg;C");
	
	temp = parseFloat(data.boiler.room_temp).toFixed(1);
	$("#room-tab").html("Room: "+temp+"&deg;C");
}


function setStatusMessage(msg, type) {
	switch(type)
	{
	case STATUS_WARNING:
		$("#statusMsg").html("Warning: " + msg);
		$("#statusMsg").css( "color", "Yellow" );
		break;
	case STATUS_ERROR:
		$("#statusMsg").html("Error: " + msg);
		$("#statusMsg").css( "color", "OrangeRed" );
		break;  
	default:
		$("#statusMsg").html("Status: " + msg);
		$("#statusMsg").css( "color", "LawnGreen" );
	}
}


function writeToConsole(text) {
	var area = document.getElementById("console");
	var lines = area.value.split("\n");
	area.value = "";
	var i = (lines.length > 9) ? lines.length - 9 : 0;
	for(i; i<lines.length; i++) {
		area.value += lines[i]+"\n";
	}
	var rightNow = new Date();
	area.value += rightNow.toLocaleTimeString() + " " +  text;
}


//function ajaxstatus(req) {
function ajaxstatus() {
	var request = '';
	
//	if (req==undefined) {
	if (statusRequestParameters.length > 0) {
//		req = statusRequestParameters;
		
		request = '?';
		var length = statusRequestParameters.length, element = null;
		for (var i = 0; i < length; i++) {
			element = statusRequestParameters[i];
			request += element["key"] + "=" + element["value"];
			if (i < length - 1)
				request += "&"
		}
		statusRequestParameters = [];
	}
	writeToConsole("stat > " + request);
	
	$.ajax({
		url:"http://bergrans.xs4all.nl/ajax-scripts/status.php" + request,
		dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)
		success: function (responseData, textStatus, jqXHR) {
			writeToConsole("stat < " + textStatus + ' ' + responseData.boiler.message);
			if (responseData.boiler.error == "") {
				setStatusMessage("OK ", STATUS_OK);
				updateData(responseData);
			}
			else {
				setStatusMessage(responseData.boiler.error, STATUS_WARNING);
			}
		},
		error:function(){
			setStatusMessage("Error getting json data file", STATUS_ERROR);
		},
	});
}


function ajaxplot(chan, span) {
	span = Math.round(span);
	if (tempTabs[$( "#tempChartContainer" ).tabs( "option", "active" )] == chan )
		$('#loader').show();	// show loader gif only on active tab

	$("#chart-span-"+chan).html(Math.floor( span / 60) + ":" + ("0"+(span % 60)).slice(-2));

	writeToConsole("plot > " + chan + " " + span);
	$.ajax({
		url:"http://bergrans.xs4all.nl/ajax-scripts/plot_data_inv.php?span="+span+"&channel="+chan,
		dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)
		success: function (responseData, textStatus, jqXHR) {
			writeToConsole("plot < " + textStatus);
			switch(chan) {
				case 'boiler':
					//alert($( "#tempChartContainer" ).tabs( "option", "active" ));
					boilerTempChart.setData(responseData);
					break;
 				case 'inlet':
					inletTempChart.setData(responseData);
					break;
				case 'outlet':
					outletTempChart.setData(responseData);
					break;				  
				case 'room':
					roomTempChart.setData(responseData);
					break;				  
				}
			$('#loader').hide();
		},
		error:function(){
			setStatusMessage("Error getting json plot_data", STATUS_ERROR);
		},
	});	
}

// class(like) code for zoomlevels
function ZoomControl(zoomInButton, zoomOutButton) {
	this.level = 180;
	this.step = 2;
	this.zoomInButton = zoomInButton;
	this.zoomOutButton = zoomOutButton;

	$(this.zoomInButton).button({icons: {primary: "ui-icon-zoomin"},text: false});
	$(this.zoomOutButton).button({icons: {primary: "ui-icon-zoomout"},text: false});

	this.zoomIn = function() {
		this.level = this.level/this.step;
		if(this.level/this.step < 10)
			$(this.zoomInButton).button( "option", "disabled", true );
		$(this.zoomOutButton).button( "option", "disabled", false );
		return this.level;
	};

	this.zoomOut = function () {
		this.level = this.level*this.step;
		if(this.level*this.step > (8*24*60))
			$(this.zoomOutButton).button( "option", "disabled", true );
		$(this.zoomInButton).button( "option", "disabled", false );
		return this.level;
	};

	this.getLevel = function () {
		return this.level;
	};	
}


function valueToColor(value) {
	var RANGEMIN = 20;
	var RANGEMAX = 90;
	var MINCOLOR = [135, 206, 235];	//SkyBlue
	var MAXCOLOR = [255, 69, 0];	//RedOrange
	
	var setColor = [0, 0, 0];
	
	if (value < RANGEMIN) {
		setColor = MINCOLOR;
	} else if (value > RANGEMAX) {
		setColor = MAXCOLOR;
	} else {
		for (n=0; n < 3; n++) {
			setColor[n] = Math.round((MAXCOLOR[n] - MINCOLOR[n]) / (RANGEMAX-RANGEMIN) * (value-RANGEMIN) + MINCOLOR[n]);
			//setColor[n] = Math.abs(Math.abs(setColor[n] - 127) - 127) / 100 * setColor[n];
		}
	}
	
	var decColor =0x1000000 + setColor[2] + 0x100 * setColor[1] + 0x10000 * setColor[0] ;
	return '#'+decColor.toString(16).substr(1);
}
