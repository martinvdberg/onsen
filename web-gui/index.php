<!DOCTYPE html>
<html lang="en">
<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>-->

<?php include("includes/header.php") ?>
			<div class="fullWidthContanainer" id="mainControlContainer">
				<div id="tempContainer"></div>
				<div id="controlsContainer">
					<div class="controlContainer">
						<div class="controlLabel">HEATER I</div>
						<div class="controlBox" id="heater1Control">1200W</div>
					</div>
					<div class="controlContainer">
						<div class="controlLabel">HEATER II</div>
						<div class="controlBox" id="heater2Control">1500W</div>
					</div>
					<div class="controlContainer">
						<div class="controlLabel">RATE</div>
						<div class="controlBox"  id="rateControl">&nbsp;</div>
					</div>
					<div class="controlContainer">
						<div class="controlLabel">BOOST</div>
						<div class="controlBox" id="boostControl">
							<button id="boostButton"></button>
						</div>
					</div>
				</div>
			</div>
			<!--<div class="fullWidthContanainer" id="environmentContainer">
				<div class="labelContainer">Room Temp:</div><div class="valueContainer" id="roomTempContainer"></div>
				<div class="labelContainer">Inlet Temp:</div><div class="valueContainer" id="inletTempContainer"></div>
			</div>-->
			<div class="fullWidthContanainer" id="mainChartContainer">
				<div class="chartContainer" id="tempChartContainer">
					<div id='loader'><!-- loading icon --></div>
					<ul>
						<li><a href="#tabs-1" id="boiler-tab">Boiler</a></li>
						<li><a href="#tabs-2" id="inlet-tab">Inlet</a></li>
						<li><a href="#tabs-3" id="outlet-tab">Outlet</a></li>
						<li><a href="#tabs-4" id="room-tab">Room</a></li>
					</ul>
					
					<div id="tabs-1">
						<div class="chart">
							<figure style="width: 800px; height: 300px;" id="boilerChart"></figure>
							<div class="span-container" id="chart-span-boiler"></div>
						</div>
						<div class="zoomButtonContainer">
							<button class="zoomButton" id="zoomInBoilerButton">Zoom in</button>
							<button class="zoomButton" id="zoomOutBoilerButton">Zoom out</button>
						</div>
					</div>
					
					<div id="tabs-2">
						<div class="chart">
							<figure style="width: 800px; height: 300px;" id="inletChart"></figure>
							<div class="span-container" id="chart-span-inlet"></div>
						</div>
						<div class="zoomButtonContainer">
							<button class="zoomButton" id="zoomInInletButton">Zoom in</button>
							<button class="zoomButton" id="zoomOutInletButton">Zoom out</button>
						</div>
					</div>
					
					<div id="tabs-3">
						<div class="chart">
							<figure style="width: 800px; height: 300px;" id="outletChart"></figure>
							<div class="span-container" id="chart-span-outlet"></div>
						</div>
						<div class="zoomButtonContainer">
							<button class="zoomButton" id="zoomInOutletButton">Zoom in</button>
							<button class="zoomButton" id="zoomOutOutletButton">Zoom out</button>
						</div>
					</div>
					
					<div id="tabs-4">
						<div class="chart">
							<figure style="width: 800px; height: 300px;" id="roomChart"></figure>
							<div class="span-container" id="chart-span-room"></div>
						</div>
						<div class="zoomButtonContainer">
							<button class="zoomButton" id="zoomInRoomButton">Zoom in</button>
							<button class="zoomButton" id="zoomOutRoomButton">Zoom out</button>
						</div>
					</div>
				</div>
				<!-- <div class="chartContainer" id="powerChartContainer">
					<div class="chartHeaderContainer" id="chartHeader_2">Power Consumption</div>
					<figure class="chart" style="width: 900px; height: 300px;" id="powerChart"></figure>
				</div> -->
			</div>
			<div class="fullWidthContanainer" id="dataTableContainer">
				<form name="myform">
					<textarea id="console" name="console" rows="10" cols="80" readonly></textarea>
				</form>
			</div>
			
<?php include("includes/footer.php") ?>
		</div>
		<script type="text/javascript">
			var bZoom = new ZoomControl("#zoomInBoilerButton", "#zoomOutBoilerButton");
			var iZoom = new ZoomControl("#zoomInInletButton", "#zoomOutInletButton");
			var oZoom = new ZoomControl("#zoomInOutletButton", "#zoomOutOutletButton");
			var rZoom = new ZoomControl("#zoomInRoomButton", "#zoomOutRoomButton");

			$("#boostButton").button({icons: {primary: "ui-icon-power"},text: false});

			$(function() {
				$( "#tempChartContainer" ).tabs();
			});

			var statusRequestParameters = [];

			$(document).ready(function() {
				setInterval("ajaxstatus()",3500);
				ajaxstatus();

				setInterval("ajaxplot('boiler', bZoom.getLevel())",16000);
				ajaxplot('boiler', bZoom.getLevel());
								
				setInterval("ajaxplot('inlet', iZoom.getLevel())",16200);
				ajaxplot('inlet', iZoom.getLevel());

				setInterval("ajaxplot('outlet', oZoom.getLevel())",16400);				
				ajaxplot('outlet', oZoom.getLevel());
				
				setInterval("ajaxplot('room', rZoom.getLevel())",16600);
				ajaxplot('room', rZoom.getLevel());
			});

			$(function() {
				$("#boostButton").button().click(function() {
					var param = [];
					var state = boostState ? 0 : 1;
					statusRequestParameters.push({key:'boost',value:state});
					ajaxstatus();
				});
			});

			$(function() {
				$("#zoomInBoilerButton").button().click(function() {
					ajaxplot('boiler', bZoom.zoomIn());
				});
			});

			$(function() {
				$("#zoomOutBoilerButton").button().click(function() {
					ajaxplot('boiler', bZoom.zoomOut());
				});
			});				
			
			$(function() {
				$("#zoomInRoomButton").button().click(function() {
					ajaxplot('room', rZoom.zoomIn());
				});
			});

			$(function() {
				$("#zoomOutRoomButton").button().click(function() {
					ajaxplot('room', rZoom.zoomOut());
				});
			});				

			$(function() {
				$("#zoomInInletButton").button().click(function() {
					ajaxplot('inlet', iZoom.zoomIn());
				});
			});

			$(function() {
				$("#zoomOutInletButton").button().click(function() {
					ajaxplot('inlet', iZoom.zoomOut());
				});
			});
			
 			$(function() {
				$("#zoomInOutletButton").button().click(function() {
					ajaxplot('outlet', oZoom.zoomIn());
				});
			});

			$(function() {
				$("#zoomOutOutletButton").button().click(function() {
					ajaxplot('outlet', oZoom.zoomOut());
				});
			});
			
			var opts_auto = {
				"dataFormatX": function (x) { return d3.time.format('%y-%m-%d %H:%M').parse(x); },
				"tickFormatX": function (x) { return d3.time.format('%H:%M')(x); }
			};

			var opts = {
				"dataFormatX": function (x) { return d3.time.format('%y-%m-%d %H:%M').parse(x); },
				"tickFormatX": function (x) { return d3.time.format('%H:%M')(x); },
				"yMin": 20,
				"yMax": 35
			};			

			var start_data = {"xScale":"time","yScale":"linear","main":[{"className":".temp_boiler","data":[{"x":"00-01-01 0:00","y":20}]}]};
			var boilerTempChart = new xChart('line', start_data, '#boilerChart', opts_auto);
			var inletTempChart = new xChart('line', start_data, '#inletChart', opts_auto);
			var outletTempChart = new xChart('line', start_data, '#outletChart', opts_auto);
			var roomTempChart = new xChart('line', start_data, '#roomChart', opts_auto);

			//var myPowerChart = new xChart('bar', power_data, '#powerChart');

		</script>
	</body>
</html>
