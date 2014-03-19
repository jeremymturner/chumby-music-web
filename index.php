<?php
# PHP Script to control the Chumby and play Internet Radio streams
#
# By: Jeremy Turner jeremy@linuxwebguy.com
# 2011-11-12 0.1 - First version
#

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);

################################################################################
$CHUMBY_IP = "172.16.1.101";
$CHUMBY_USER = "root";

$CMD = "ssh $CHUMBY_USER@$CHUMBY_IP ";

$stations = array(
  array("Callsign" => "KUFX", 
		"TagLine" => "The Fox 98.5", 
		"Loc" => "San Jose", 
		"Url" => "http://6693.live.streamtheworld.com/KUFXFM_SC"
		),
  
  array("Callsign" => "KCBS",
		"TagLine" => "News Radio 106.1",
		"Loc" =>"San Francisco",
		"Url" =>"http://208.80.54.69/KCBSAMDIALUPCMP3"
		),
  
  array("Callsign" => "KEXP",
		"TagLine" => "College Radio",
		"Loc" => "Seattle",
		"Url" => "http://kexp-mp3-2.cac.washington.edu:8000"
		)
);  

###############################################################################
## CHECK FOR ARGUMENTS
$op = ""; $val = "";
$url = ""; $brightness = ""; $volume = "";

if (isset($_POST["op"])) {
	$op = $_POST["op"];
}

if (isset($_POST["value"])) {
	$val = $_POST["val"];
}

if (isset($_POST["url"])) {
	$url = $_POST["url"];
}

if (isset($_POST["brightness"])) {
	$brightness = $_POST["brightness"];
}

if (isset($_POST["volume"])) {
	$volume = $_POST["volume"];
}

###############################################################################
## CHECK FOR ARGUMENTS
function set_volume($volume = "") {
	global $CMD;
	$line = exec($CMD . "chumby_set_volume " . $volume);
}

###############################################################################
## CHECK FOR ARGUMENTS
function get_volume() {
	global $CMD;
	$line = exec($CMD . "chumby_set_volume");
	return $line;
}

###############################################################################
## CHECK FOR ARGUMENTS
function volume_fade_out($vol = 0) {
	$cur_vol = get_volume();
	
	while ($cur_vol > $vol) {
		$cur_vol -= 5;
		if ($cur_vol < $vol) { $cur_vol = $vol; }
		set_volume($cur_vol);
		usleep(100);
	}
}

###############################################################################
function volume_fade_in($vol = "60") {
	$cur_vol = get_volume();
	
	while ($cur_vol < $vol) {
		$cur_vol += 5;
		if ($cur_vol > $vol) { $cur_vol = $vol; }
		set_volume($cur_vol);
		usleep(100);
	}
}

###############################################################################
## CHECK FOR ARGUMENTS
function play_url($url = "") {
	global $CMD;
	
	# get system volume
	$cur_volume = get_volume();
	# fade volume out
	volume_fade_out();
	# play new url
	$line = exec($CMD . "btplay " . $url);
	# fade volume in
	volume_fade_in($cur_volume);
	
}

###############################################################################

function set_brightness($brightness = "") {
	global $CMD;
	$line = exec($CMD . "\"echo " . $_POST["brightness"] . " > /sys/devices/platform/silvermoon-bl/backlight/silvermoon-bl/brightness\"");
}

###############################################################################
# DO STUFF
if ($url != "") { play_url($url); }
if ($volume != "") { set_volume($volume); }
if ($brightness != "") { set_brightness($brightness); }

?>

<html>
<head>
<!-- download from http://webdesignerwall.com/tutorials/css3-gradient-buttons -->
<!--
<link rel="stylesheet" href="/css/buttons.css" type="text/css">
-->
<meta name="viewport" content="width=320">
<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
<script src="http://code.jquery.com/mobile/1.0rc2/jquery.mobile-1.0rc2.min.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.0rc2/jquery.mobile-1.0rc2.min.css" />


<style type="text/css">
#buttons {
  width: 320px;
}

.button {
  padding: 0.5em 0.5em 0.5em 0.5em;
}

.stop, .station, .volume {
  width: 300px;  
  height: 60px;
  float: left;
  font-weight: bold;
  margin-bottom: 20px;
}

.stop {
  width: 170px;
}

.volume {
  width: 60px;
}

div pre {
  clear: both;
  width: 300px;
}
</style>
</head>
<body>
<?php
$old_volume = get_volume();
$volu = $old_volume+5;
$vold = $old_volume-5;
?>
<div id='buttons'>
<form name='myForm' method='post'>
<button class='button stop red' name='url' value='stop'>Stop Playing</button>
<button class='button volume orange' name='volume' value='<?=$volu?>'>Vol <?=$volu?></button>
<button class='button volume orange' name='volume' value='<?=$vold?>'>Vol <?=$vold?></button>
<button class='button station gray' name='brightness' value='20'>Screen Dim</button>
<button class='button station gray' name='brightness' value='100'>Screen Full</button>
<?php foreach ($stations as $station) { ?>
<button class='button station white' name='url' value='<?=$station["Url"]?>'><?=$station["Callsign"]?> <?=$station["TagLine"]?> <?=$station["Loc"]?></button>
<?php } ?>

</form>
</div>
<div id="debug">
<PRE>DEBUG:
<?php
print "url:$url:<br>\n";
print "brightness:$brightness:<br>\n";
print "volume:$volume:<br>\n";

if (isset($_POST["url"]) && $_POST["url"] != "") {
  print "Playing " . $_POST['url'] . "<br>\n";
  echo.system($CMD . "btplay '" . $_POST["url"] . "'");# == 0 or die "system play failed: $?";
}

if (isset($_POST["brightness"]) && $_POST["brightness"] != "") {
  print "Changing brightness to " . $_POST["brightness"] . "<br>\n";
#  echo.system($CMD . "\"echo " . $_POST["brightness"] . " > /sys/devices/platform/silvermoon-bl/backlight/silvermoon-bl/brightness\"");# == 0 or die "system brightness failed: $?";
}

if (isset($_POST["volume"]) && $_POST["volume"] != "") {
  print "Changing volume to " . $_POST["volume"] . "<br>";
#  echo.system($CMD . "chumby_set_volume " . $_POST["volume"]);
}

#system($CMD . "chumby_set_volume");
#$tmp = get_volume();
#volume_fade_out();
#volume_fade_in($tmp);

?>

</PRE>
</div>
</body>
</html>
