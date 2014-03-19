<?php 
#
# play.pl
#
# This script will play the parameter passed to it.

if (isset($_POST["url"])) {
	$url = $_POST["url"];
} elseif (isset($_GET["url"])) {
	$url = $_GET["url"];
}


################################################################################
$CHUMBY_IP = "172.16.1.101";
$CHUMBY_USER = "root";

$CMD = "ssh $CHUMBY_USER@$CHUMBY_IP -C \"btplay $url\"";

echo $CMD;
echo "<br>\n";
echo system($CMD);
