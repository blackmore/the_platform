<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Tracking Sheet</title>		
	
<style type="text/css"> 
<!--	
body {
	padding:5px;
	font: 12px/1.6em "Lucida Grande",Arial, Geneva, Verdana, Helvetica, sans-serif black;
	color: black;
}

h1, h2, h3 {
	margin:0px;
	display:inline;
}

h1 {
	font-size: 2.2em;
}

h3 {
	font-size: 1.2em;
}


#sheet {
	width:17cm;
	border-right:1px dotted silver;
	padding-right:1em;
	page-break-inside:avoid;
}

#title {
}

#production_no {
	border:1px solid silver;
	float:right;
	padding:0 1em;
	width:3.5cm;
}

#production_title {
	clear:right;
	border-bottom:2px solid gray;
}

.production_box {
	float:right;
	width:3.6cm;
}

.table1 {
	text-align:right;
	vertical-align:top;
}
.table2 {
	border:1px solid gray;
	padding-left:0.5em;
}
.table3 {
	border:1px solid gray;
	text-align:center;
}
.table4 {
	vertical-align:bottom;
	text-align:center;
	padding-bottom:0px;
}

.checkbox {
	float:left;
	margin-right:1.5em;
}
.checkbox_box {
	width:14px;
	border:1px solid black;
	float:left;
	height:14px;
	margin-right:3px;
	font: 14px Arial;
	text-align:center;
	vertical-align:middle;
	overflow:hidden;
}
-->
</style>
	
</head>
<body>

<?php

//if( !$auth->checkAuth() || $auth->getAuthData("GroupID") != $PRODUCTION_GRP ) {
//	die( "<b style=\"color:red;\">Zugriff verweigert.</b>\n" ); 
//}

// ### Formular-Generierung ### ---------------------------------------------------------------------------------------

// ### Datensatz laden ----------------------------------------------------------------------------------
	$ProductionID = getGet( 'ProductionID' , 'null' );
	
	$datensatz = SQLLQuery("SELECT ProductionID, ProductionNo, CategoryID, CustomerID, Title, ManagerID, DATE_FORMAT(CreatedDate,'%d.%m.%Y %H:%i') AS CreatedDate, CreatedByID , StatusID FROM Production WHERE ProductionID=$ProductionID" );
	$tasks = SQLQuery("SELECT TaskID, Position, FunctionID, StaffID, DATE_FORMAT(EndDate,'%d.%m.%Y %H:%i') AS EndDate, Notes, StatusID FROM Task WHERE ProductionID=$ProductionID ORDER BY Position ASC" );
	$trackingfields = reduce_keys( $create_tracking['Alle'] );
	$trackingdata = SQLLQuery("SELECT Duration, LanguageFromID, LanguageToID, SpecialNotes, ProjectType, MasterType, $trackingfields FROM Tracking WHERE ProductionID=$ProductionID LIMIT 1" );
	?>
	
	<div id="sheet" style="">
	<div id="production_no">
		Prod.-Nr.<br>
		<h3><?php echo $datensatz['ProductionNo']; ?></h3>
	</div>
	<h1 id="title"><?php echo $category[$datensatz['CategoryID']]; ?> Tracking Sheet</h1>
	<span style="margin-left:1em;"><?php echo date("d.m.Y"); ?></span><br>
	<div id="production_title">
		<div class="production_box" style="clear:right;">
			<u>Sprachen:</u><br>
			<?php echo $language[$trackingdata['LanguageFromID']]."&ndash;".$language[$trackingdata['LanguageToID']]; ?>
		</div>
		<div class="production_box" style="text-align:center;">
			<u>Gesamtlänge:</u><br>
			<b><?php echo $trackingdata['Duration']; ?></b>
		</div>
		<u>Produktionstitel:</u><br>
		<h3><?php echo $datensatz['Title']; ?></h3>
	</div>
	
	
	<table style="table-layout:fixed;width:100%;empty-cells:show;">
	<colgroup>
		<col style="width:3.5cm">
		<col style="width:6cm;">
		<col style="width:3.5cm;">
		<col style="width:3.5cm;">
	</colgroup>
	
	<?php
	// Hier muss angepasst werden, wenn sich die Funktionen ändern!
	$untertitler = SQLLQuery("SELECT StaffID, FunctionID, DATE_FORMAT(EndDate,'%d.%m.%Y %H:%i') AS EndDate FROM Task WHERE ( FunctionID=".implode(" OR FunctionID=",$FUNCTIONID_UB)." ) AND ProductionID=$ProductionID ORDER BY Position ASC LIMIT 1" );
	$qa = SQLLQuery("SELECT StaffID, DATE_FORMAT(EndDate,'%d.%m.%Y %H:%i') AS EndDate FROM Task WHERE ( FunctionID=".implode(" OR FunctionID=",$FUNCTIONID_QA)." ) AND ProductionID=$ProductionID ORDER BY Position ASC LIMIT 1" );

	?>
	
	<tr><td class="table1">Kunde:</td><td class="table2"><?php echo $customer[$datensatz['CustomerID']]; ?>&nbsp;</td><td></td><td></td></tr>
	<tr><td class="table1">Projektleiter/in:</td><td class="table2"><?php echo $allstaff[$datensatz['ManagerID']]; ?>&nbsp;</td><td class="table4">Deadline</td><td></td></tr>
	<tr><td class="table1"><?php echo $function[$untertitler['FunctionID']]; ?>:</td><td class="table2"><?php echo $allstaff[$untertitler['StaffID']]; ?></td><td class="table3"><?php echo $untertitler['EndDate']; ?>&nbsp;</td><td></td></tr>
	<tr><td class="table1">QA:</td><td class="table2"><?php echo $allstaff[$qa['StaffID']]; ?>&nbsp;</td><td class="table3"><?php echo $qa['EndDate']; ?>&nbsp;</td><td></td></tr>

	<tr><td></td><td colspan="3" style="border-bottom:2px solid gray;"></td></tr>
	<tr><td colspan="4"></td></tr>
	
	<tr><td class="table1">Bes. Anmerkungen:</td><td colspan="3" class="table2"><?php echo nl2br($trackingdata['SpecialNotes']); ?>&nbsp;</td>
	
	<?php addLoadedFormElements( $trackingdata , $datensatz['CategoryID'] ); ?>
	
	<tr><td colspan="4" style="border-bottom:2px solid gray;"></td></tr>
	</table>
	
	<table style="table-layout:fixed;width:100%;empty-cells:show;">
	<colgroup>
		<col style="width:3.5cm;">
		<col style="width:4cm;">
		<col style="width:3.5cm;">
		<col style="width:5.5cm;">
	</colgroup>
	
	<tr><td></td><td>Name</td><td style="text-align:center;">Datum</td><td>Bemerkungen</td></tr>
	
	<?php addLoadedFormTasks( $tasks ); ?>
	</table>
	
	</div>
	
	<?php


// ------------------------------------------------------------------------------------------------------------------

function addLoadedFormElements( $trackingdata , $CategoryID  ) {
	global $create_tracking, $category, $allstaff, $customer , $allcustomer, $allstaff, $create_projecttype, $create_mastertype;
	
	if( isset( $create_projecttype[$category[$CategoryID]] ) )
		echo '	<tr><td class="table1">Art des Projekts:</td><td class="table2" colspan="3">'.$trackingdata['ProjectType']."&nbsp;</td></tr>\n"; 
	
	if( isset( $create_mastertype[$category[$CategoryID]] ) )
		echo '	<tr><td class="table1">Master/Film Typ:</td><td class="table2" colspan="3">'.$trackingdata['MasterType']."&nbsp;</td></tr>\n"; 
	
	// ### Tracking -----------------------------------------------------------------------------------------------
	
	// Erstelle Gruppenweise neue Eingabefelder aus dem $create_tracking-Array
	foreach( $create_tracking['Alle'] as $abschnitt => $trackingline ) {
		// Textfelder
		foreach( $trackingline['text'] as $trackingfield => $trackingitem ) {
			if( ( isset($trackingdata) && !is_null($trackingdata[$trackingfield])) ) {
		
			echo '	<tr><td class="table1">'.$trackingitem.':</td><td class="table2" colspan="3">'.$trackingdata[$trackingfield]."&nbsp;</td></tr>\n"; 
			}
		}
		// Checkboxen
		$checks = null;
		foreach( $trackingline['bool'] as $trackingfield => $trackingitem ) {
			if( ( isset($trackingdata) && $trackingdata[$trackingfield] != null ) ) {
				//if( $create ) $checks[] = &HTML_QuickForm::createElement('advcheckbox', "$trackingfield", null , $trackingitem );
				if( $trackingdata[$trackingfield]=="1" ) $box='X'; else $box=' ';
				$checks[] = '<div class="checkbox"><span class="checkbox_box">'.$box.'</span>'.$trackingitem."</div>\n"; 
			}
		}
		if( isset($checks) ) {
			echo '	<tr><td class="table1">'.$abschnitt.':</td><td colspan="3">'.implode("", $checks)."</td>\n";
		}
	}	
}

function addLoadedFormTasks( $tasks ) {
	
	// ### Tasks --------------------------------------------------------------------------------------------------
				
	if( isset($tasks) ) {
		foreach( $tasks as $task ) {
			
			addFormTask( $task );
			
			// Hier werden die Checkboxen eingefüget	
			$checkboxen = SQLQuery("SELECT CheckboxID , Name , Value , Notes FROM Checkbox WHERE TaskID=".$task['TaskID']." ORDER BY CheckboxID ASC" );
			//if( isset($datensatz['TaskGroup_check']) ) $checkboxen = $datensatz['TaskGroup_check'][$pos];
			if( count($checkboxen) > 0 ) {
				foreach( $checkboxen as $checkbox ) {
					addFormCheckbox( $checkbox );
				}
				echo '<tr><td></td><td colspan="3" style="border-bottom:2px solid gray;"></td></tr>';
				echo '<tr><td colspan="4"></td></tr>';	
			}
		}
	}
}

function addFormTask( $task ) {
	global $function, $allstaff, $status;
	if( $task['StatusID'] == 3 ) $haken = " &#x2714;"; else $haken = "";
	echo '<tr><td class="table1">'.$function[$task['FunctionID']].':</td><td class="table2">'.$allstaff[$task['StaffID']].$haken.'&nbsp;</td><td class="table3" style="border:none;">'.$task['EndDate'].'&nbsp;</td><td class="table2">'.$task['Notes'].'&nbsp;</td></tr>';		
}

function addFormCheckbox( $checkbox  ) {
	global $function, $allstaff, $status;
	if( $checkbox['Value']=="1" ) $box='X'; else $box=' ';
	echo '<tr><td class="table1"></td><td colspan="2"><div class="checkbox"><span class="checkbox_box">'.$box.'</span>'.$checkbox['Name'].'</div></td><td class="table2">'.$checkbox['Notes'].'&nbsp;</td></tr>';		
}

$mdb2->disconnect(); ?>

</body>
</html>
