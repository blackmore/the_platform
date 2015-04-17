
<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD</title>
	
	<link rel="stylesheet" href="productions.css" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language="JavaScript" type="text/javascript">
	<!--
	function gotoStaff() {
		top.location.href = "staff.php";
	}
	function gotoProduction() {
		top.location.href = "production.php";
	}
	function openCustomers() {
		// 1. Fenster öffnen
		window.open( "customer.php" , "customer_window" , "height=590, width=460, top=70,left="+((screen.width/2)-350)+",location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes" );
	}
	function gotoTracking() {
		top.location.href = "tracking.php";
	}
	-->
	</script>
	
	<style type="text/css"> 
	button {
		font: 12px "Lucida Grande",Arial, Geneva, Verdana, Helvetica, sans-serif black !important;

	}
	</style> 
		
</head>
<body>

<?php

// Was soll getan werden? action=[logout]
$action = getGet('action','');

// ### Links zum Erstellen neuer Produktionen -------------------------------------------------------------------------
echo "<div id=\"createbuttons\">";

if( $action == 'logout' ) {
	$auth->logout();
	$auth->start();
}

if( $auth->checkAuth() ) {
	echo "<div id=\"login\">Eingeloggt als: ".$auth->getUsername()." [ <a href=\"$PHP_SELF?action=logout\">ausloggen</a> ]</div>";
} else {
	echo "<div id=\"login\">Bitte einloggen.</div>";
}

echo "</div>\n";
// --------------------------------------------------------------------------------------------------------------------



echo "<div id=\"startbildschirm\">";
echo "<div class=\"productionlogo\" style=\"width:100%;\"><img src=\"images/titelbild_gross.gif\" alt=\"\" style=\"border:none;\"></div><br>";

if( $auth->checkAuth() == false ) die("<div style=\"height:3em;padding-right:10px;\">$login_box</div>\n</div>\n</body></html>");

switch( $auth->getAuthData("GroupID") ) {
case $GROUPID_OFFICE:
	echo "<button type=button value=\"Produktionen\" onClick=\"gotoProduction()\">Produktionen</button> ";
	echo "<button type=button value=\"Mitarbeiterliste\" onClick=\"gotoStaff()\">Mitarbeiterliste</button> ";
	echo "<button type=button value=\"Kunden bearbeiten\" onClick=\"openCustomers()\">Kunden bearbeiten</button> ";
	break;
}
		
echo "<button type=button value=\"Trackingsheet\" onClick=\"gotoTracking()\">Trackingsheet</button><br><br> ";
echo "</div>";

?>

</body>
</html>
