<?php
header('content-type: text/html; charset=utf-8');

// holt Werte über GET
function getGET( $variable , $standardWert ) {
	if( isset( $_GET[$variable] ) )
		$variable2 = urldecode( $_GET[$variable] );
	else
		$variable2 = $standardWert;

	return $variable2;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kalender</title>
	
	<link rel="stylesheet" href="productions.css" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language="JavaScript" type="text/javascript">
	<!--
	window.focus();
	
	function returnDate(text) {
    window.opener.dateField.value = text;
    window.close();
	}
	-->
	</script>
	
	
	<style type="text/css"> 
	body {
		width:170px !important;
		text-align:center;
	}
	h3 {
		text-align:center;
		margin:0;
	}
	</style> 
</head>
<body>

<?php

$seldate = getGet('date',null);
if( !is_numeric($seldate) ) unset($seldate);

$return_time = getGet('time',0);
if($return_time==1) $return_date_format = isset($seldate) ? "d.m.y ".date("H:i",$seldate) : "d.m.y 00:00";
else $return_date_format = "d.m.Y";

$year = getGet('y',date("Y", isset($seldate) ? $seldate : time() ));
$month = getGet('m',date("m", isset($seldate) ? $seldate : time() ));
$first = mktime(0,0,0,$month,1,$year);

// print Year, Month
echo "<h3>".date("F Y",$first)."</h3>\n";
echo '<a href="calendar.php?y='.date("Y",$first-(3600*24)).'&m='.date("m",$first-(3600*24)).'&date='.$seldate.'&time='.$return_time.'">« früher</a> | <a href="calendar.php?y='.date("Y").'&m='.date("m").'&date='.$seldate.'&time='.$return_time.'">heute</a> | <a href="calendar.php?y='.date("Y",$first+(3600*24*32)).'&m='.date("m",$first+(3600*24*32)).'&date='.$seldate.'&time='.$return_time.'">später »</a></h3>'."\n";
echo '<table class="cal">'."\n<tr>";
// print Weekdays
$days = array( "Mo","Di","Mi","Do","Fr","Sa","So" );
foreach( $days as $d ) {
	echo '<td class="cal_head">'."$d</td>";
}

$startday = date("w",$first);
if( $startday==0 ) $startday=7;
$monthdays = date("t",$first);

// print Days
$i=0;
for( $d=1-$startday ; $d<$monthdays || ($i%7!=0)  ; $d++ ) {
	if($i++%7==0) echo "</tr>\n<tr>";
	$timestamp = $first+($d*3600*24);
	if( $seldate-$timestamp>=0 && $seldate-$timestamp<3600*24 ) echo '<td class="cal_sel">';
	else if( mktime()-$timestamp>0 && mktime()-$timestamp<3600*24 ) echo '<td class="cal_today">';
	else if( date("m",$timestamp)<>$month ) echo '<td class="cal_grey">';
	else echo '<td class="cal_day">';
	echo '<a href="javascript:returnDate(\''.date($return_date_format,$timestamp).'\')">'.date("d",$timestamp)."</a></td>";
}
echo "</tr>\n";



echo "</table>\n";
echo "<a href='javascript:window.close();'>schließen</a>\n";

?>

</body>
</html>