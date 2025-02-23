<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<! https://1stwebdesigner.com/css-effects/ -->
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Flot Examples: Basic Usage</title>
	<link href="flot-master/examples/examples.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.canvaswrapper.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.colorhelpers.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.saturated.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.browser.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.drawSeries.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.uiConstants.js"></script>
	<script type="text/javascript">

	$(function() {

		var d1 = [];
		for (var i = 0; i < 14; i += 0.5) {
			d1.push([i, Math.sin(i)]);
		}

		var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

		// A null signifies separate line segments

		var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];

		$.plot("#placeholder", [ d1, d2, d3 ]);

		// Add the Flot version string to the footer

		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
	});

	</script>
</head>
<body>

	<div id="header">
		<h2>Basic Usage</h2>
	</div>

	<div id="content">

		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder" style="padding: 0px; position: relative;"><canvas class="flot-base" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><canvas class="flot-overlay" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><div class="flot-svg" style="position: absolute; top: 0px; left: 0px; height: 100%; width: 100%; pointer-events: none;"><svg style="width: 100%; height: 100%;"><g class="flot-x-axis flot-x1-axis xAxis x1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="28.1015625" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">0</text><text x="143.36082175925924" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">2</text><text x="258.6200810185185" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">4</text><text x="373.87934027777777" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">6</text><text x="489.138599537037" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">8</text><text x="600.5072337962963" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">10</text><text x="715.7664930555555" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">12</text></g><g class="flot-y-axis flot-y1-axis yAxis y1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="8.78125" y="331.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">0</text><text x="8.78125" y="278.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">3</text><text x="8.78125" y="225.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">5</text><text x="8.78125" y="172.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">8</text><text x="1" y="13.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">15</text><text x="1" y="119.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">10</text><text x="1" y="66.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">13</text><text x="4.125" y="384.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">-2</text></g></svg></div></div>
		</div>

		<p>You don't have to do much to get an attractive plot.  Create a placeholder, make sure it has dimensions (so Flot knows at what size to draw the plot), then call the plot function with your data.</p>

		<p>The axes are automatically scaled.</p>

	</div>

	<div id="footer">Flot 3.0.0 – 
		Copyright © 2007 - 2014 IOLA and Ole Laursen
	</div>



</body></html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Flot Examples: Basic Usage</title>
	<link href="flot-master/examples/examples.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.canvaswrapper.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.colorhelpers.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.saturated.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.browser.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.drawSeries.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.uiConstants.js"></script>
	<script type="text/javascript">

	$(function() {

		var d1 = [];
		for (var i = 0; i < 14; i += 0.5) {
			d1.push([i, Math.sin(i)]);
		}

		var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

		// A null signifies separate line segments

		var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];

		$.plot("#placeholder", [ d1, d2, d3 ]);

		// Add the Flot version string to the footer

		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
	});

	</script>
</head>
<body>

	<div id="header">
		<h2>Basic Usage</h2>
	</div>

	<div id="content">

		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder" style="padding: 0px; position: relative;"><canvas class="flot-base" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><canvas class="flot-overlay" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><div class="flot-svg" style="position: absolute; top: 0px; left: 0px; height: 100%; width: 100%; pointer-events: none;"><svg style="width: 100%; height: 100%;"><g class="flot-x-axis flot-x1-axis xAxis x1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="28.1015625" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">0</text><text x="143.36082175925924" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">2</text><text x="258.6200810185185" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">4</text><text x="373.87934027777777" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">6</text><text x="489.138599537037" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">8</text><text x="600.5072337962963" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">10</text><text x="715.7664930555555" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">12</text></g><g class="flot-y-axis flot-y1-axis yAxis y1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="8.78125" y="331.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">0</text><text x="8.78125" y="278.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">3</text><text x="8.78125" y="225.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">5</text><text x="8.78125" y="172.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">8</text><text x="1" y="13.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">15</text><text x="1" y="119.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">10</text><text x="1" y="66.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">13</text><text x="4.125" y="384.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">-2</text></g></svg></div></div>
		</div>

		<p>You don't have to do much to get an attractive plot.  Create a placeholder, make sure it has dimensions (so Flot knows at what size to draw the plot), then call the plot function with your data.</p>

		<p>The axes are automatically scaled.</p>

	</div>

	<div id="footer">Flot 3.0.0 – 
		Copyright © 2007 - 2014 IOLA and Ole Laursen
	</div>



</body>
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Flot Examples: Basic Usage</title>
	<link href="flot-master/examples/examples.css" rel="stylesheet" type="text/css">
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.canvaswrapper.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.colorhelpers.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.saturated.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.browser.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.drawSeries.js"></script>
	<script language="javascript" type="text/javascript" src="flot-master/source/jquery.flot.uiConstants.js"></script>
	<script type="text/javascript">

	$(function() {

		var d1 = [];
		for (var i = 0; i < 14; i += 0.5) {
			d1.push([i, Math.sin(i)]);
		}

		var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

		// A null signifies separate line segments

		var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];

		$.plot("#placeholder", [ d1, d2, d3 ]);

		// Add the Flot version string to the footer

		$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
	});

	</script>
</head>
<body>

	<div id="header">
		<h2>Basic Usage</h2>
	</div>

	<div id="content">

		<div class="demo-container">
			<div id="placeholder" class="demo-placeholder" style="padding: 0px; position: relative;"><canvas class="flot-base" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><canvas class="flot-overlay" width="818" height="413" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 818px; height: 413px;"></canvas><div class="flot-svg" style="position: absolute; top: 0px; left: 0px; height: 100%; width: 100%; pointer-events: none;"><svg style="width: 100%; height: 100%;"><g class="flot-x-axis flot-x1-axis xAxis x1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="28.1015625" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">0</text><text x="143.36082175925924" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">2</text><text x="258.6200810185185" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">4</text><text x="373.87934027777777" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">6</text><text x="489.138599537037" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">8</text><text x="600.5072337962963" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">10</text><text x="715.7664930555555" y="407.99609375" class="flot-tick-label tickLabel" style="position: absolute; text-align: center;" transform="">12</text></g><g class="flot-y-axis flot-y1-axis yAxis y1Axis" style="position: absolute; top: 0px; left: 0px; bottom: 0px; right: 0px;"><text x="8.78125" y="331.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">0</text><text x="8.78125" y="278.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">3</text><text x="8.78125" y="225.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">5</text><text x="8.78125" y="172.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">8</text><text x="1" y="13.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">15</text><text x="1" y="119.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">10</text><text x="1" y="66.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">13</text><text x="4.125" y="384.00390625" class="flot-tick-label tickLabel" style="position: absolute; text-align: right;" transform="">-2</text></g></svg></div></div>
		</div>

		<p>You don't have to do much to get an attractive plot.  Create a placeholder, make sure it has dimensions (so Flot knows at what size to draw the plot), then call the plot function with your data.</p>

		<p>The axes are automatically scaled.</p>

	</div>

	<div id="footer">Flot 3.0.0 – 
		Copyright © 2007 - 2014 IOLA and Ole Laursen
	</div>



</body></html>