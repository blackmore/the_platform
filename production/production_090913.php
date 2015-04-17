<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Produktionen</title>
	
	<link rel="stylesheet" href="productions.css?date=0801" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language='JavaScript' src='include/date.js'></script>
	<script language='JavaScript' src='include/production.js'></script>
	
	<style type="text/css"> 
	body {
		width:1200px !important;
	}
	#createbuttons {
		background-image:url('images/face-red.png') !important;
	}
	</style> 
	<!--[if gte IE 6]>
	<style type="text/css"> 
	button {
		font: 10px "Lucida Grande",Arial, Geneva, Verdana, Helvetica, sans-serif black !important;

	}
	.spalten span {
		margin-right:1px;
	}
	</style> 
	<![endif]-->
		
</head>
<body onLoad="onLoadStart()" onUnload="askSave()">
<a name="top"></a>
<?php

// Was soll getan werden? action=[create]|load|insert|update
$action = getGet('action','');


if( $action == 'load' && getGet('ProductionID',null) == "" ) $action = '';

// ### Links zum Erstellen neuer Produktionen -------------------------------------------------------------------------
echo "<div id=\"createbuttons\">\n";


if( $action == 'logout' ) {
	$auth->logout();
	$auth->start();
}

echo $login_box;
if( $auth->checkAuth() ) {
	echo "<div id=\"login\">Eingeloggt als: ".$auth->getUsername()." [ <a href=\"".$_SERVER['PHP_SELF']."?action=logout\">ausloggen</a> ]</div>";
	if( $auth->getAuthData("GroupID") == $GROUPID_OFFICE ) {
		echo "neu erstellen: ";
		while( $cat=each($category) ) {
			echo "<button type=button border=\"0\" value=\"$cat[1] erstellen\" onClick=\"createProduction($cat[0])\">$cat[1]</button> ";
		}
		echo "&nbsp; Ansicht:";
		echo "&nbsp; <button type=button value=\"Zur Mitarbeiterliste\" onClick=\"gotoStaff()\">Zur Mitarbeiterliste</button>";
	}
}	
echo "&nbsp; <button type=button value=\"Zum Trackingsheet\" onClick=\"gotoTracking()\">Zum Trackingsheet</button>";
echo "</div>\n";

// --------------------------------------------------------------------------------------------------------------------

if( $auth->checkAuth() == false ) die("\n<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");
if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE ) die("\n<b style=\"color:red;\">Zugriff verweigert.</b>\n</body></html>");


// Zeitfaktoren aus der Datenbank lesen und das Javascript-Array erstellen
// functiontimefactor[FunctionID][FactorType][strengthfactor]

$function_timefactor = SQLQuery(  "SELECT FunctionID, Richtwert_A1 , Richtwert_A2 , Richtwert_A3 ,
 																											Richtwert_B1 , Richtwert_B2 , Richtwert_B3 ,
 																											Richtwert_C1 , Richtwert_C2 , Richtwert_C3  FROM Function" );
$staff_timefactor = SQLQuery( "SELECT Staff.StaffID, Strength.FunctionID , Strength.Value FROM Staff INNER JOIN Strength ON Staff.StaffID = Strength.StaffID" );

// TODO: falls das hier zu viel ist, könnte man die Werte in eine .js-Datei auslagern und nur bei Änderung generieren...

echo "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--
	var functiontimefactor = new Array();
	var stafftimefactor = new Array();\n";
foreach( $function_timefactor as $function_timefactor_zeile ) {	
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."] = new Array();\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][0] = new Array();\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][0][0] = ".$function_timefactor_zeile[Richtwert_A1].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][0][1] = ".$function_timefactor_zeile[Richtwert_A2].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][0][2] = ".$function_timefactor_zeile[Richtwert_A3].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][1] = new Array();\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][1][0] = ".$function_timefactor_zeile[Richtwert_B1].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][1][1] = ".$function_timefactor_zeile[Richtwert_B2].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][1][2] = ".$function_timefactor_zeile[Richtwert_B3].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][2] = new Array();\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][2][0] = ".$function_timefactor_zeile[Richtwert_C1].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][2][1] = ".$function_timefactor_zeile[Richtwert_C2].";\n";
	echo "	functiontimefactor[".$function_timefactor_zeile[FunctionID]."][2][2] = ".$function_timefactor_zeile[Richtwert_C3].";\n";
}
$last_staffid = 0;
$i=0;
foreach( $staff_timefactor as $zeile ) {
	if( $zeile['StaffID'] == $last_staffid ) $i++;
	else { 
		echo "	stafftimefactor[".$zeile['StaffID']."] = new Array();\n";
		$i = 0;
	}	
	echo "	stafftimefactor[".$zeile['StaffID']."][".$zeile['FunctionID']."] = ".$strengthfactor_value[$zeile['Value']].";\n";
	$last_staffid = $zeile['StaffID'];
}
echo "-->\n</script>\n";

// neues Formular
$form = new HTML_QuickForm('frmProduction', 'post' , '?action=update' , null , 'class="formtable" onSubmit="duration_reshedule()"' , true );
// Script für die Datumserkennung vorbereiten
$java_process_dates = new JavaScriptGenerator( 'process_dates()' );

// Defaultwert, der aber angepasst wird
$display_form = false;

// ### Formular-Generierung ### ---------------------------------------------------------------------------------------

// Gruppe Detail Information
$form->addElement('header', 'ProductionHeader', '&nbsp;Informationen zur Produktion');

	$form->addElement('hidden', 'ProductionID');
	$form->addElement('hidden', 'CurrentStaffID' , $auth->getAuthData("StaffID") );
	
	//$field_ref['ProductionNo'] = &HTML_QuickForm::createElement('text', 'ProductionNo', 'Produktion Nr.', 'class="input_short"');
	//$form->addElement($field_ref['ProductionNo']);
	//$form->addElement('hidden', 'ProductionNo_prev');
	
	//$grid =& new HTML_QuickForm_ElementGrid( null , "ProductionNo" );
	unset($buffer);
	$buffer['ProductionNo1'] = &HTML_QuickForm::createElement('text', 'ProductionNo1', null, 'class="input_short"');
	$buffer['ProductionNo2'] = &HTML_QuickForm::createElement('text', 'ProductionNo2', null, 'class="input_char"');
	//$custom_errors['ProductionNo1'] =& $buffer['ProductionNo1'];
	$field_ref['ProductionNo1'] =& $buffer['ProductionNo1'];
	$field_ref['ProductionNo2'] =& $buffer['ProductionNo2'];
	//$grid->addRow($buffer);
	//$form->addElement($grid);
	$form->addGroup($buffer, "ProductionNoGrp" , "Produktion Nr." , ' ');
	$form->addElement('hidden', 'ProductionNo_prev');
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Kunde" );
	unset($buffer);
	$buffer['CustomerID'] = &HTML_QuickForm::createElement('select', 'CustomerID', 'Kunde', $customer, 'class="input_dropdown" style="width:196px;"');
	$field_ref['CustomerID'] =& $buffer['CustomerID'];
	$buffer[] = new HTML_QuickForm_xbutton_freeze('btnCustomers', "Kunden..." , 'type=button onClick="openCustomers()"');
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'CustomerID_prev');
	
	$form->addElement('text', 'Title', 'Produktionstitel', 'class="input_extralong"');
	$form->addElement('hidden', 'Title_prev');
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Kategorie / Grad" );
	unset($buffer);
	$buffer['category'] = &HTML_QuickForm::createElement('select', 'CategoryID', null , $category );
	$buffer[] = &HTML_QuickForm::createElement('select', 'FactorType', null , $factortype );
	$field_ref['CategoryID'] =& $buffer['category'];
	$field_ref['CategoryID']->freeze();
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'FactorType_prev' );
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Deadline" , 'style="width:100%!important;"' );
	unset($buffer);
	$buffer['Deadline'] =& HTML_QuickForm::createElement('static', null , null , '');
	$custom_errors['Deadline'] =& $buffer['Deadline'];
	$grid->addRow($buffer);	
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('text', 'Deadline', 'Deadline' , 'class="input_deadline"' );
	$buffer[] = &insertCalendar('Deadline_Kal','Deadline');
	$buffer['StatusID'] = &HTML_QuickForm::createElement('select', 'StatusID', 'Status' , $productionstatus );
	$field_ref['StatusID'] =& $buffer['StatusID'];
	$buffer[] = new HTML_QuickForm_xbutton_freeze( 'btnFinished', "Produktion abgeschlossen" , 'type=button onClick="markProductionFinished()"');
	$buffer['stcAmpel'] =& HTML_QuickForm::createElement('static', null , null , '');
	$field_ref['stcAmpel'] =&$buffer['stcAmpel'];
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'Deadline_prev');
	$form->addElement('hidden', 'StatusID_prev');
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Nicht starten vor" );
	unset($buffer);
	$buffer['NotBefore'] =& HTML_QuickForm::createElement('static', null , null , '');
	$custom_errors['NotBefore'] =& $buffer['NotBefore'];
	$grid->addRow($buffer);	
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('text', 'NotBefore', 'Nicht starten vor' , 'class="input_date"' );
	$buffer[] = &insertCalendar('NotBefore_Kal','NotBefore');
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'NotBefore_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('select', 'ManagerID', 'Projektleiter' , $staff_sek,  'class="input_dropdown"');
	$field_ref['ManagerID'] =& $buffer;
	$form->addElement($buffer);
	$form->addElement('hidden', 'ManagerID_prev');
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Erstellt von" );
	unset($buffer);
	$buffer['CreatedDate'] =& HTML_QuickForm::createElement('static', null , null , '');
	$custom_errors['CreatedDate'] =& $buffer['CreatedDate'];
	$grid->addRow($buffer);	
	unset($buffer);
	$buffer['CreatedByID'] = &HTML_QuickForm::createElement('select', 'CreatedByID', null , $staff_sek,  'class="input_dropdown"');
	$field_ref['CreatedByID'] =& $buffer['CreatedByID'];
	$buffer[] = &HTML_QuickForm::createElement('text', 'CreatedDate', null , 'class="input_datetime"' );
	$buffer[] = &insertCalendar('Created_Kal','CreatedDate',true);
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'CreatedByID_prev');
	$form->addElement('hidden', 'CreatedDate_prev');

	// Button-Zeile oben erstellen (wird später entsprechend verändert)
	// gleichzeitig untere Zeile vorbereiten
	$grid =& new HTML_QuickForm_ElementGrid( null , "" );
	unset($buffer);
	$buffer['btnVariant'] =  new HTML_QuickForm_button_freeze( 'btnVariant', "Als Kopie speichern", 'onClick=createVariant()');
	$buffer['btnClear'] = &HTML_QuickForm::createElement('reset', 'btnClear', 'Zurücksetzen' , 'class="button"' );
	$buffer['btnSubmit'] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Submit");
	$field_ref['btnVariant'] =&$buffer['btnVariant'];
	$field_ref['btnSubmit'] =&$buffer['btnSubmit'];
	$field_ref['btnClear'] =&$buffer['btnClear'];
	$grid->addRow($buffer);
	$grid->setClass('elementGridRight');
	$form->addElement($grid);
	
	// vorbereiten
	unset($buffer);
	$buffer['btnDelete'] = &HTML_QuickForm::createElement('button', 'btnDelete', 'Produktion löschen', 'onClick=deleteProduction()' );
	$buffer['btnClear'] = &$field_ref['btnClear'];
	$buffer['btnSubmit'] = &$field_ref['btnSubmit'];
	$field_ref['btnDelete'] =&$buffer['btnDelete'];
	$grid =& new HTML_QuickForm_ElementGrid( null , "" );
	$grid->addRow($buffer);
	$grid->setClass('elementGridRight');
	$field_ref['btnGrid'] =&$grid;
		
unset($buffer);
$buffer =& HTML_QuickForm::createElement('header', 'TrackingHeader', "&nbsp;Tracking");
$field_ref['TrackingHeader'] =& $buffer;
$form->addElement($buffer);

	$grid = &HTML_QuickForm::createElement("elementGrid", null , "Gesamtlänge" );
	unset($buffer);
	$buffer['Duration'] =& HTML_QuickForm::createElement('static', null , null , '');
	$custom_errors['Duration'] =& $buffer['Duration'];
	$grid->addRow($buffer);
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('text', "Duration" ,  null , 'class="input_duration"');
	$buffer['DurationUnit'] = &HTML_QuickForm::createElement('static', 'DurationUnit' , null , 'Minuten');
	$field_ref['DurationUnit'] =& $buffer['DurationUnit'];
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'Duration_prev');
	
	$grid = &HTML_QuickForm::createElement("elementGrid", null , "Sprache von / nach" );
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('select', 'LanguageFromID', 'Sprache von', $language,  'class="input_dropdown" onChange="markStaff()"');
	$buffer[] = &HTML_QuickForm::createElement('select', 'LanguageToID', 'Sprache nach', $language,  'class="input_dropdown" onChange="markStaff()"');
	$grid->addRow($buffer);
	$form->addElement($grid);
	$form->addElement('hidden', 'LanguageFromID_prev');
	$form->addElement('hidden', 'LanguageToID_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('textarea', "SpecialNotes", "Besondere Anmerkungen" , 'class="input_textarea"');
	$buffer->setCols(70);
	$buffer->setRows(4);
	$form->addElement($buffer);
	$form->addElement('hidden', 'SpecialNotes_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('textarea', "InternalNotes", "Interne Informationen" , 'class="input_textarea"');
	$field_ref['InternalNotes'] =& $buffer;
	$buffer->setCols(70);
	$buffer->setRows(4);
	$form->addElement($buffer);
	$form->addElement('hidden', 'InternalNotes_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('text', "TechContact", "Kontakt Technik" , 'class="input_extralong"');
	$field_ref['TechContact'] =& $buffer;
	$form->addElement($buffer);
	$form->addElement('hidden', 'TechContact_prev');
	
	//$grid = &HTML_QuickForm::createElement("elementGrid", null , "Art des Projekts" );
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('select', 'ProjectType', 'Art des Projekts', array(''=>''),  'class="input_dropdown"');
	//$buffer[] = &HTML_QuickForm::createElement('text', 'ProjectTypeText', null , 'class="input_short"' );
	$field_ref['ProjectType'] =& $buffer;
	//$grid->addRow($buffer);
	//$form->addElement($grid);
	$form->addElement($buffer);
	$form->addElement('hidden', 'ProjectType_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('select', 'MasterType', 'Master/Film Typ', array(''=>''),  'class="input_dropdown"');
	$field_ref['MasterType'] =& $buffer;
	$form->addElement($buffer);
	$form->addElement('hidden', 'MasterType_prev');
	
// --------------------------------------------------------------------------------------------------------------------

// Regeln für Gültigkeit von Feldern (bitte sparsam!)

function is_unique( $arr ) {
	global $ProductionID;
	$nummer = SQLLQuery( 'SELECT ProductionID , ProductionNo FROM Production WHERE ProductionNo = "'.$arr['ProductionNo1'].$arr['ProductionNo2'].'"' );
	if( count($nummer)==0 || $nummer['ProductionID']==$ProductionID ) return true;
	else return false;
}

$form->registerRule('datum', 'callback' , 'is_a_date' );
$form->registerRule('zeit', 'callback' , 'is_a_time' );
$form->registerRule('unique', 'callback' , 'is_unique' );

$form->applyFilter('Title', 'trim' );
//$form->addRule('ProductionNo', 'Dieses Feld wird zur Identifikation benötigt.', 'required' , null , 'Server' , false , true );
//$form->addRule('ProductionNo', 'Es gibt bereits eine Produktion mit dieser Nummer.', 'unique' , null , 'Server' , false , true );
$form->addGroupRule( "ProductionNoGrp" , array( 'ProductionNo1' => array( array( "Dieses Feld wird zur Identifikation benötigt." , 'required' ) ,
																																					array( "Das ist keine gültige Produktions-Nr." , 'regex' , '/^[1-7]-[0-2][0-9](0?[1-9]|1[0-2])\/[0-9]{4}$|wird angelegt/' ) )));
$form->addRule( "ProductionNoGrp" , "Es gibt bereits eine Produktion mit dieser Nummer." , 'unique' );

$form->addRule('Deadline', 'Bitte ein Datum eingeben oder leer lassen.', 'datum' , null , 'Server' , false , true );
$form->addRule('NotBefore', 'Bitte ein Datum eingeben oder leer lassen.', 'datum' , null , 'Server' , false , true );
$form->addRule('CreatedDate', 'Bitte ein Datum eingeben oder leer lassen.', 'datum' , null , 'Server' , false , true );
$form->addRule('Duration', 'Bitte eine Zahl eingeben oder leer lassen.', 'numeric' , null , 'Server' , false , true );

switch( $action ) {
case 'insert' : // ### Formular in Datenbank schreiben ----------------------------------------------------------------	
	$categoryvalue = $field_ref['CategoryID']->getValue();
		if ( $categoryvalue[0] < 1 ) $categoryvalue[0] = 1;
	
	// $datensatz , $trackingdata , $tasks , $CategoryID , $create
	addLoadedFormElements( $form->getSubmitValues() , null , null , $categoryvalue[0] , true );	
				
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$form->process('insert_data', false);
		// ab hier geht es weiter zu load....!
	} else {
		$form->updateAttributes(array('action'=>"?action=insert"));
		$display_message = "Fehlerhafte Eingaben. Die Produktion wurde noch nicht erstellt.";
		if( $form->getElementError('ProductionNoGrp') == "" ) {
			$field_ref['ProductionNo1']->freeze();
			$field_ref['ProductionNo2']->freeze();
		}
		$field_ref['btnSubmit']->setValue("Produktion erstellen");
		$field_ref['btnDelete']->freeze();
		$field_ref['btnVariant']->freeze();
		$display_form = true;
		break;
	}
case 'update' : // ### Datensatz aktualisieren ------------------------------------------------------------------------
	if( $action == 'update' ) {
	DebugMSG("Update");
	$categoryvalue = $field_ref['CategoryID']->getValue();
	if ( $categoryvalue[0] < 1 ) $categoryvalue[0] = 1;
	$ProductionID = $form->getSubmitValue('ProductionID');
	// $datensatz , $trackingdata , $tasks , $CategoryID , $create
	addLoadedFormElements( $form->getSubmitValues() , null , null , $categoryvalue[0] , true );
	
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$form->process('update_data', false);
		// ab hier geht es weiter zu load....!
	} else {
		$display_message = "Fehlerhafte Eingaben. Die Änderungen wurden noch nicht gespeichert.";
		$field_ref['btnSubmit']->setValue("Änderungen speichern");
		$field_ref['btnDelete']->unfreeze();
		$field_ref['btnVariant']->unfreeze();
		addDeleteScript( $ProductionID );
		$display_form = true;
		break;
	}
	}	
case 'load' : // ### Datensatz laden ----------------------------------------------------------------------------------
	// Werte einsetzen und die fehlenden Formularelemente erstellen
	if( !isset($ProductionID)) $ProductionID = getGet( 'ProductionID' , 'null' );
	DebugMSG("Es wird geladen");
	
	$ampel="gruen";
	
	if( $action == 'load' ) $create=true;
		else $create=false;
	if( !is_numeric($ProductionID) || !load_data( $ProductionID , $create ) ) {
		$display_form = false;
		$display_message ="Konnte die geforderte Produktion nicht finden.";
		$ProductionID = null;
		break;
	}
	$field_ref['btnSubmit']->setValue("Änderungen speichern");
	$field_ref['btnDelete']->unfreeze();
	$field_ref['btnVariant']->unfreeze();
	$display_form = true;
	// Defaults eintragen
	$form->setConstants($autofill);
	addDeleteScript( $ProductionID );
	break;
case 'delete' : // ### Datensatz löschen ------------------------------------------------------------------------------
	DebugMSG("Delete");
	$ProductionID = getGet( 'ProductionID' , 'null' );
	if( $ProductionID > 0 ) {
		SQLLQuery( 'BEGIN' );
		SQLIQuery( "DELETE FROM Task WHERE ProductionID = '$ProductionID'" );
		SQLIQuery( "DELETE FROM Tracking WHERE ProductionID = '$ProductionID' LIMIT 1" );
		SQLIQuery( "DELETE FROM Production WHERE ProductionID = '$ProductionID' LIMIT 1" );
		SQLLQuery( 'COMMIT' );
		$display_form = false;
		$display_message = "Die Produktion wurde gelöscht.";
	} else {
		$display_form = false;
		$display_message ="Konnte die geforderte Produktion nicht finden.";
		$ProductionID = null;
	}
	break;
case 'create': // ### Neues Formular anlegen --------------------------------------------------------------------------
	// komplettes Formular mit Defaultwerten erstellen
	$CategoryID = getGet( 'CategoryID' , '1' );
	DebugMSG("Leeres Formular, Typ $CategoryID");
	$autofill = array( "ManagerID" => $auth->getAuthData("StaffID") ,
				"CreatedDate" => date('d.m.y H:i') ,
				"CreatedByID" => $auth->getAuthData("StaffID") ,
				"ProductionNoGrp" => array( "ProductionNo1" => "wird angelegt" ) ,
				"FactorType" => 1 ,
				"CategoryID" => $CategoryID );
	$form->updateAttributes(array('action'=>"?action=insert"));
	// Unterscheiden, welche Kategorie erstellt werden soll.
	addNewFormElements( $CategoryID );
	$field_ref['ProductionNo1']->freeze();
	$field_ref['ProductionNo2']->freeze();
	$field_ref['btnSubmit']->setValue("Produktion erstellen");
	$field_ref['btnDelete']->freeze();
	$field_ref['btnVariant']->freeze();
	
	// Defaults eintragen
	$form->setDefaults($autofill);
	$display_form = true;
	break;
default: // ### Leere Seite zeigen ------------------------------------------------------------------------------------
	DebugMSG("Keine Aktion");
	$display_form = false;
	break;
} // ------------------------------------------------------------------------------------------------------------------

	// Fehler für Grid-Felder anzeigen (Feature nicht unterstützt in akt. Version)
	foreach( array_keys($custom_errors) as $field ) {
		if( $form->getElementError($field) != "" ) $custom_errors[$field]->setValue('<span style="color:#ff0000;">'.$form->getElementError($field).'</span>');
	}

$buffer =& new HTML_QuickForm_xbutton_freeze( 'btnCalc' , 'berechne Zeitdauer' , 'type=button onClick="duration_reshedule()"' ); //style="margin-left:150px;
$form->insertElementBefore( $buffer  , 'SchedulingTitles');

$form->addElement('static', null , null , '<div class="spaltentitel"><span>█ Muttersprachler/in &nbsp; ▓ passende Sprachkomb. &nbsp; ▁ nicht mögl. </span></div>' ); //style="margin-left:150px;

// Untere Buttonreihe einfügen
$form->addElement($field_ref['btnGrid']);

function insert_data( $values ) {
	global $form, $ProductionID, $category, $auth, $customer, $create_checkboxen, $display_message;
	
	DebugMSG("Verarbeiten...");
	
	if( !isset($values['ProductionNoGrp']['ProductionNo1']) || $values['ProductionNoGrp']['ProductionNo1']=="wird angelegt" ) {
		// Prefix für ProductionNo aus der Datenbank holen
		$prefix = SQLLQuery("SELECT Prefix FROM Category WHERE CategoryID = ".$values['CategoryID']." LIMIT 1" );
		
		// Alle ProductionNo dieses Monats holen
		//$nr = SQLQuery("SELECT ProductionNo FROM Production WHERE ProductionNo regexp '^.*-".date("ym")."/'" );
		
		// Alle ProductionNo holen
		$nr = SQLQuery("SELECT ProductionNo FROM Production WHERE ProductionNo regexp '^.-".date("y")."..\/'" );
		
		// Auf 4-Stellige Suffix-Nummer reduzieren
		foreach( $nr as $index => $val ) {
			$nr[$index] = (int) substr(preg_replace('/^.-....\//','',$val['ProductionNo'],1),0,4);
		}
		
		// Maximum fertig formatiert in nr_neu speichern
		if( !empty($nr) && max($nr) != 0 ) $nr_neu = sprintf("%04d" , (max($nr)+1)); else $nr_neu = "0001";
		$ProductionNo = $prefix['Prefix']."-".date('ym')."/".$nr_neu;
		// workaround zur Sicherheit
		$values['ProductionNo1'] = $ProductionNo;
	} else $ProductionNo = $values['ProductionNoGrp']['ProductionNo1'].$values['ProductionNoGrp']['ProductionNo2'];
	
	// Produktion speichern:
	// Diese Daten werden gespeichert:
	$eintrag_array = array(	"ProductionNo" => $ProductionNo ,
				"CustomerID" => $values['CustomerID'] ,
				"CategoryID" => $values['CategoryID'] ,
				"FactorType" => $values['FactorType'] ,
				"ManagerID" => $values['ManagerID'] ,
				"Title" => $values['Title'] ,
				"Deadline" => input2date($values['Deadline']) ,
				"NotBefore" => input2date($values['NotBefore']) ,
				"CreatedDate" => input2date($values['CreatedDate']) ,
				"CreatedByID" => $values['CreatedByID'] ,
				"StatusID" => $values['StatusID'] );
	
	SQLLQuery( 'BEGIN' );
	$ProductionID=SQLIQuery( "INSERT INTO Production ( ".explode_keys($eintrag_array)." ) VALUES ( ".explode_values($eintrag_array)." )" );
	
	DebugMSG("Neue ID: $ProductionID");

	SQLIQuery( "UPDATE Staff SET LastSavedID = '$ProductionID' WHERE StaffID = '".$auth->getAuthData("StaffID")."'" );

	// Tracking speichern:
	$eintrag_array = array ("ProductionID" => $ProductionID ,
				"Duration" => $values['Duration'] ,
				"LanguageFromID" => $values['LanguageFromID'] ,
				"LanguageToID" => $values['LanguageToID'] );

	// hiermit ist mindestens ein eintrag im Tracking-Array:
	if( isset( $values['TrackingItem'] ) ) $tracking_array = array_merge( $values['TrackingItem'] , array( "SpecialNotes" => $values['SpecialNotes'] )  );
	else $tracking_array = array( "SpecialNotes" => $values['SpecialNotes'] );
	
	if( isset($values['ProjectType']) ) $tracking_array['ProjectType'] = $values['ProjectType'];
	if( isset($values['MasterType']) ) $tracking_array['MasterType'] = $values['MasterType'];
	if( isset($values['TechContact']) ) $tracking_array['TechContact'] = $values['TechContact'];
	if( isset($values['InternalNotes']) ) $tracking_array['InternalNotes'] = $values['InternalNotes'];
	
	if( isset($values['TrackingGroup']) ) {
		while( $group = each($values['TrackingGroup']) ) {
			$tracking_array = array_merge( $tracking_array , $group[value] );
		}
	}
	// Das Tracking-Array hat die Besonderheit, alle Werte als Leerstring statt als null-Wert zu schreiben:
	SQLIQuery( "INSERT INTO Tracking ( ".explode_keys($eintrag_array)." , ".explode_keys($tracking_array)." ) VALUES ( ".explode_values($eintrag_array)." , ".explode_values($tracking_array,"''")." )" );

	// Tasks speichern:
	while( $task = each($values['TaskGroup']) ) {
		$eintrag_array = array ( "ProductionID" => $ProductionID ,
					"Position" => $task[value][Position] ,
					"ParentID" => 0 ,
					"FunctionID" => $task[value][FunctionID] ,
					"StaffID" => $task[value][StaffID] ,
					"Duration" => input2time($task[value][Duration]) ,
					"StartDate" => input2date($task[value][StartDate]) ,
					"EndDate" => input2date($task[value][EndDate]) ,
					"StatusID" => $task[value][StatusID] );					
					
		// DateFix manuell, damit nicht null eingetragen wird
		$TaskID = SQLIQuery( "INSERT INTO Task ( ".explode_keys($eintrag_array)." , DateFix ) VALUES ( ".explode_values($eintrag_array)." , '".$task[value][DateFix]."' )" );
		$form->setConstants( array( "TaskGroup" => array( $task[value][Position] => array("TaskID" => $TaskID) ) ) );
		// Checkboxen für Tasks erstellen
		if( isset( $create_checkboxen[$category[$values['CategoryID']]][$task[value]['FunctionID']] ) ) {
			foreach( $create_checkboxen[$category[$values['CategoryID']]][$task[value]['FunctionID']] as $checkbox ) {
				SQLIQuery( "INSERT INTO Checkbox ( TaskID , Name ) VALUES ( '$TaskID' , '$checkbox' )" );
			}
		}
	}	
	
	SQLLQuery( 'COMMIT' );

	// Fileserver
	saveTrackingSheet( $ProductionID , $values );

}

function load_data( $ProductionID , $create ) {
	global $form, $autofill, $create_tracking, $buttons, $PRODUCTIONSTATUS_LOCK, $ampel, $production_done, $field_ref;

	$datensatz = SQLLQuery("SELECT ProductionID, ProductionNo, CategoryID, FactorType, CustomerID, Title, StatusID , UNIX_TIMESTAMP(Deadline) AS Deadline_Timestamp , DATE_FORMAT(Deadline,'%d.%m.%Y') AS Deadline, Deadline AS Deadline_prev, DATE_FORMAT(NotBefore,'%d.%m.%Y') AS NotBefore, NotBefore AS NotBefore_prev , ManagerID, DATE_FORMAT(CreatedDate,'%d.%m.%y %H:%i') AS CreatedDate, CreatedDate AS CreatedDate_prev , CreatedByID FROM Production WHERE ProductionID=$ProductionID" );
	if( $datensatz['ProductionID'] != $ProductionID || $ProductionID < 1 )
		return false;
	else {
		$autofill = array_merge( $datensatz, map_prev($datensatz) );							
	}
			
	DebugMSG("ID: $ProductionID ");
	// Formularelemente aus Datenbank erstellen
	$tasks = SQLQuery("SELECT TaskID, Position, FunctionID, StaffID, UNIX_TIMESTAMP(StartDate) AS StartDate_Timestamp , UNIX_TIMESTAMP(EndDate) AS EndDate_Timestamp , DATE_FORMAT(Duration,'%H:%i') AS Duration, DATE_FORMAT(StartDate,'%d.%m.%y %H:%i') AS StartDate, StartDate AS StartDate_prev, DATE_FORMAT(EndDate,'%d.%m.%y %H:%i') AS EndDate, EndDate AS EndDate_prev, DateFix, StatusID FROM Task WHERE ProductionID=$ProductionID ORDER BY Position ASC" );
	$trackingfields = reduce_keys( $create_tracking['Alle'] );
	$trackingdata = SQLLQuery("SELECT Duration, LanguageFromID, LanguageToID, SpecialNotes, InternalNotes, ProjectType, MasterType, TechContact, $trackingfields FROM Tracking WHERE ProductionID=$ProductionID LIMIT 1" );
		
	if( $datensatz['StatusID']>=$PRODUCTIONSTATUS_LOCK ) $production_done = true; else $production_done = false;
		
	addLoadedFormElements( $datensatz , $trackingdata , $tasks ,  $datensatz['CategoryID'] , $create );
	
	if( $production_done ) {
		// Fertige Produktionen einfrieren
		$form->freeze();
		// Auswahlfeld reaktivieren
		$field_ref['StatusID']->unfreeze();
	}

	// Ampel anzeigen
	$field_ref['stcAmpel']->setValue('<span id="ampel"><img src="images/ampel-'.$ampel.'.gif" alt=""></span>');

	return true;
}

function update_data( $values ) {
	global $ProductionID,$auth, $category,$customer,$display_message;
	
	$saveTrackingsheet = false;
	
	// prüfe, ob Datensatz bereits existiert
	if( $values['ProductionID'] != "" && sizeof( SQLLQuery("SELECT ProductionID FROM Production WHERE ProductionID=$values[ProductionID]" )) > 0 && $values['ProductionID'] > 0 ) {
		// Datensatz Update
		DebugMSG("Verarbeiten...");

		$values['ProductionNo'] = $values['ProductionNoGrp']['ProductionNo1'].$values['ProductionNoGrp']['ProductionNo2'];

		// Diese Daten werden gespeichert:
		$eintrag_array = make_eintrag_array( $values ,
		array( 	"ProductionNo" => $values['ProductionNo'] ,
			"ManagerID" => $values['ManagerID'] ,
			"Title" => $values['Title'] ,
			"CreatedDate" => input2date($values['CreatedDate']) ,
			"CreatedByID" => $values['CreatedByID'] ,
			"CustomerID" => $values['CustomerID'] ,
			"FactorType" => $values['FactorType'] ,
			"Deadline" => input2date($values['Deadline']) ,
			"StatusID" => $values['StatusID'] ,
			"NotBefore" => input2date($values['NotBefore']) ));	
			
		$ProductionID = $values[ProductionID];
		
		SQLLQuery( 'BEGIN' );
		if( count($eintrag_array)>0 ) {
			SQLIQuery( "UPDATE Production SET ".explode_array($eintrag_array)." WHERE ProductionID = '$ProductionID' LIMIT 1"  );
			$saveTrackingsheet = true;
		}
		
		SQLIQuery( "UPDATE Staff SET LastSavedID = '$ProductionID' WHERE StaffID = '".$auth->getAuthData("StaffID")."'" );
		
		//Tracking speichern:
		$eintrag_array = make_eintrag_array( $values , 
		array(	"Duration" => $values['Duration'] ,
			"LanguageFromID" => $values['LanguageFromID'] ,
			"LanguageToID" => $values['LanguageToID'] ,
			"SpecialNotes" => $values['SpecialNotes'] ,
			"InternalNotes" => $values['InternalNotes'] ,
			"TechContact" => $values['TechContact'] ,
			"ProjectType" => $values['ProjectType'] ,
			"MasterType" => $values['MasterType'] ));
					
		$tracking_array = array();
		if( isset($values['TrackingItem']) ) {
			foreach( $values['TrackingItem'] as $key => $value ) {
				if( $values['TrackingItem']["$key"] != $values['TrackingItem_prev'][$key] ) $tracking_array["$key"] = $value;
			}
		}
		if( isset($values['TrackingGroup']) ) {
			foreach( $values['TrackingGroup'] as $abschnitt => $abschnitt_val ) {
				foreach( $values['TrackingGroup']["$abschnitt"] as $key => $value ) {
					//DebugMSG( $values['TrackingGroup_prev']["$abschnitt"]["$key"] ." nach ".$values['TrackingGroup']["$abschnitt"][$key]);
					if( $value == "" ) $value = 0;
					if( $value != $values['TrackingGroup_prev']["$abschnitt"][$key] ) $tracking_array["$key"] = $value;
				}
			}
		}
		if( count($eintrag_array) > 0 && count($tracking_array) > 0 ) $komma = " , "; else $komma = " ";
		// Das Tracking-Array hat die Besonderheit, alle Werte als Leerstring statt als null-Wert zu schreiben:
		if( count($eintrag_array) > 0 || count($tracking_array) ) {
			SQLIQuery( "UPDATE Tracking SET ".explode_array($eintrag_array)."$komma".explode_array($tracking_array,"''")." WHERE ProductionID = '$ProductionID' LIMIT 1" );
			$saveTrackingsheet = true;
		}
		// Tasks speichern:
		foreach($values['TaskGroup'] as $task => $value ) {
			
			if( $value[DateFix] == "" ) $value[DateFix] = "0";
			
			$eintrag_array = make_eintrag_array( $value , 
			array (	"StaffID" => $value[StaffID] ,
				"Duration" => input2time($value[Duration]) ,
				"StartDate" => input2date($value[StartDate]) ,
				"EndDate" => input2date($value[EndDate]) ,
				"StatusID" => $value[StatusID] ,
				"DateFix" => $value[DateFix] ) );
			if( count($eintrag_array)>0 ) {
				SQLIQuery( "UPDATE Task SET ".explode_array($eintrag_array)." WHERE ProductionID = '$ProductionID' AND TaskID = '".$value[TaskID]."' LIMIT 1" );
				$saveTrackingsheet = true;
			}
		}	
		
		SQLLQuery( 'COMMIT' );
	
		// Hier wird das Trackingsheet aktualisiert:
		if( $saveTrackingsheet ) saveTrackingSheet( $ProductionID , $values );
		
	} else {
		$display_form = false;
		$display_message ="Konnte die geforderte Produktion nicht finden.";
		$ProductionID = null;
	}
}

function addNewFormElements( $CategoryID ) {
	global $autofill, $category, $form, $staff, $staff_sek, $create_tracking, $create_tasks, $create_projecttype, $create_mastertype, $create_techcontact, $field_ref, $STAFFID_DATA, $FUNCTIONID_DATA;

	// ProjectType je nach Category
	if( $create_projecttype[$category[$CategoryID]] ) {
		foreach( $create_projecttype['values'] as $option )
			$field_ref['ProjectType']->addOption( $option , $option );
	} else $form->removeElement('ProjectType');

	// MasterType je nach Category
	if( isset( $create_mastertype[$category[$CategoryID]] ) ) {
		foreach( $create_mastertype[$category[$CategoryID]] as $option )
			$field_ref['MasterType']->addOption( $option , $option );
	} else $form->removeElement('MasterType');

	// Technik-Ansprechpartner entfernen
	if( !$create_techcontact[$category[$CategoryID]] ) {
		$form->removeElement('TechContact');
	}

	// Tracking
	$field_ref['TrackingHeader']->setValue("&nbsp;Eigenschaften : $category[$CategoryID]");
	
	// Erstelle Gruppenweise neue Eingabefelder aus dem $create_tracking-Array
	foreach( $create_tracking[$category[$CategoryID]] as $abschnitt => $trackingline ) {
		// Textfelder
		foreach( $trackingline['text'] as $trackingitem ) {
			$form->addElement('text', "TrackingItem[$trackingitem]", $create_tracking['Alle'][$abschnitt]['text'][$trackingitem] , 'class="input_extralong"');
		}
		// Checkboxen
		$checks = null;
		foreach( $trackingline['bool'] as $trackingitem ) {
			$checks[] = &HTML_QuickForm::createElement('advcheckbox', "$trackingitem", null , $create_tracking['Alle'][$abschnitt]['bool'][$trackingitem] );
		}
		if( isset($checks) ) $form->addGroup($checks, "TrackingGroup[$abschnitt]" , "$abschnitt" , '&nbsp;&nbsp;&nbsp;');
	}
		
	/* Reshedule-JS */
	$java_duration_reshedule = new JavaScriptGenerator( 'duration_reshedule()' );
	$java_duration_reshedule->addLine('process_dates();');
	$java_duration_reshedule->addLine('factor = document.frmProduction.elements["FactorType"];');
	
	// Workaround für Übersetzung
	if( $category[$CategoryID] == "Übersetzung" ) $field_ref['DurationUnit']->setValue("Zeilen");
	
	// Tasks
	addTaskHeader();

	$i=0;
	foreach( $create_tasks[$category[$CategoryID]] as $task ) {
		/* Reshedule-JS */			      
		$java_duration_reshedule->addLine('feld = document.frmProduction.elements["TaskGroup['.$i.'][Duration]"];');
		$java_duration_reshedule->addLine('staff = document.frmProduction.elements["TaskGroup['.$i.'][StaffID]"];');
		$java_duration_reshedule->addLine('feld.value = staffduration(factor.value,'.$task.',staff.value,feld.value);');
		if( $task == $FUNCTIONID_DATA || $task == $FUNCTIONID_DATA+1 ) $autofill['TaskGroup']["$i"]['StaffID'] = $STAFFID_DATA;			
		addFormTask( $task , $i++ );
	}
	/* Reshedule-JS */ $java_duration_reshedule->echoHtml();
}

// erstellt die dynamischen Felder und füllt die Werte aus.
// $create entscheidet, ob die Formularelemente erstellt werden sollen.
// Grund: vor validate() müssen die Felder existieren, aber erst nach dem Laden sollen sie mit
// Datenbankwerten überschrieben werden.
function addLoadedFormElements( $datensatz , $trackingdata , $tasks , $CategoryID , $create = true ) {
	global $autofill, $form, $create_tracking, $category, $allstaff, $customer , $allcustomer, $staff, $staff_sek, $ampel, $production_done, $create_projecttype, $create_mastertype, $create_techcontact, $field_ref;
	
	// Soll autofill gemacht werden?
	$fill = isset($trackingdata);
	
	// ProductionNo aufsplitten
	if( $fill ) {
		$autofill['ProductionNoGrp']=array( 'ProductionNo1' => substr($datensatz['ProductionNo'],0,11) ,
																				'ProductionNo2' => substr($datensatz['ProductionNo'],11) );
	}
	
	// Inaktive Mitarbeiter hinzufügen, wenn diese Ausgewählt sind
	if( $create && $datensatz['ManagerID'] != 0 && !isset( $staff_sek[$datensatz['ManagerID']] ) ) {
		$field_ref['ManagerID']->addOption( $allstaff[$datensatz['ManagerID']]." (inaktiv)" , $datensatz['ManagerID'] );
	}
	if( $create && $datensatz['CreatedByID'] != 0 && !isset( $staff_sek[$datensatz['CreatedByID']] ) ) {
		$field_ref['CreatedByID']->addOption( $allstaff[$datensatz['CreatedByID']]." (inaktiv)" , $datensatz['CreatedByID'] );
	}
	if( $create && $datensatz['CustomerID'] != 0 && !isset( $customer[$datensatz['CustomerID']] ) ) {
		$field_ref['CustomerID']->addOption( $allcustomer[$datensatz['CustomerID']]." (inaktiv)" , $datensatz['CustomerID'] );
	}
	
	// Workaround für Übersetzung
	if( $category[$CategoryID] == "Übersetzung" ) $field_ref['DurationUnit']->setValue("Zeilen");
	
	if( !isset($datensatz['ProjectType']) ) $datensatz['ProjectType'] = $trackingdata['ProjectType'];
	if( !isset($datensatz['MasterType']) ) $datensatz['MasterType'] = $trackingdata['MasterType'];
	if( !isset($datensatz['TechContact']) ) $datensatz['TechContact'] = $trackingdata['TechContact'];
	
	// ProjectType und MasterType je nach Category
	if( $create ) {
		if( isset( $create_projecttype[$category[$CategoryID]] ) ) {
			// Auswahlelemente hinzufügen
			foreach( $create_projecttype['values'] as $option )
				$field_ref['ProjectType']->addOption( $option , $option );
			// Anderes Element steht in der Datenbank: auch noch hinzufügen
			if( $datensatz['ProjectType']!="" && !in_array($datensatz['ProjectType'], $create_projecttype['values'] ) )
				$field_ref['ProjectType']->addOption( $datensatz['ProjectType'] , $datensatz['ProjectType'] );				
		} else $form->removeElement('ProjectType');
	
		if( isset( $create_mastertype[$category[$CategoryID]] ) ) {
			// Auswahlelemente hinzufügen
			foreach( $create_mastertype[$category[$CategoryID]] as $option )
				$field_ref['MasterType']->addOption( $option , $option );
			// Anderes Element steht in der Datenbank: auch noch hinzufügen
			if( $datensatz['MasterType']!="" && !in_array($datensatz['MasterType'], $create_mastertype[$category[$CategoryID]] ) )
				$field_ref['MasterType']->addOption( $datensatz['MasterType'] , $datensatz['MasterType'] );				
		} else $form->removeElement('MasterType');
		
		// Technik-Ansprechpartner entfernen
		if( !$create_techcontact[$category[$CategoryID]] ) {
			$form->removeElement('TechContact');
		}
	}
	
	// Tracking
	if( $create ) $field_ref['TrackingHeader']->setValue("&nbsp;Eigenschaften : $category[$CategoryID]");
	
	if( $fill ) $new_autofill = array( "LanguageFromID" => $trackingdata['LanguageFromID'] ,
				"LanguageToID" => $trackingdata['LanguageToID'] ,
				"SpecialNotes" => $trackingdata['SpecialNotes'] ,
				"InternalNotes" => $trackingdata['InternalNotes'] ,
				"ProjectType" => $trackingdata['ProjectType'] ,
				"MasterType" => $trackingdata['MasterType'] ,
				"TechContact" => $trackingdata['TechContact'] ,
				"Duration" => $trackingdata['Duration'] );
					
	if( $fill ) $autofill = array_merge( $autofill , $new_autofill , map_prev($new_autofill) );
		
	if( $fill ) $autofill_group = array( );
	
	// Erstelle Gruppenweise neue Eingabefelder aus dem $create_tracking-Array
	foreach( $create_tracking['Alle'] as $abschnitt => $trackingline ) {
		// Textfelder
		foreach( $trackingline['text'] as $trackingfield => $trackingitem ) {
			if( ( isset($trackingdata) && !is_null($trackingdata[$trackingfield])) || isset($datensatz['TrackingItem'][$trackingfield]) ) {
				if( $create ) $form->addElement('text', "TrackingItem[$trackingfield]", $trackingitem , 'class="input_extralong"');
				if( $create ) $form->addElement('hidden', "TrackingItem_prev[$trackingfield]");
				if( $fill ) $autofill["TrackingItem[$trackingfield]"] = $trackingdata[$trackingfield]; 
				if( $fill ) $autofill["TrackingItem_prev[$trackingfield]"] = $trackingdata[$trackingfield]; 
			}
		}
		// Checkboxen
		$checks = null;
		$checks_prev = null;
		if( $fill ) $autofill_check = array( );
		foreach( $trackingline['bool'] as $trackingfield => $trackingitem ) {
			if( ( isset($trackingdata) && $trackingdata[$trackingfield] != null ) || isset($datensatz['TrackingGroup'][$abschnitt][$trackingfield]) ) {
				if( $create ) $checks[] = &HTML_QuickForm::createElement('advcheckbox', "$trackingfield", null , $trackingitem );
				if( $create ) $form->addElement('hidden', "TrackingGroup_prev[$abschnitt][$trackingfield]");
				if( $fill ) $autofill_check["$trackingfield"] = $trackingdata[$trackingfield];
			}
		}
		if( isset($checks) ) {
			if( $create ) $form->addGroup($checks, "TrackingGroup[$abschnitt]" , "$abschnitt" , '&nbsp;&nbsp;&nbsp;');
			if( $fill ) $autofill["TrackingGroup"]["$abschnitt"] = $autofill_check;
		}
		if( $fill ) $autofill["TrackingGroup_prev"]["$abschnitt"] = $autofill_check;
	}
	
	// ### Tasks --------------------------------------------------------------------------------------------------
	
	/* Reshedule-JS */
	if( $create ) {
		$java_duration_reshedule = new JavaScriptGenerator( 'duration_reshedule()' );
		$java_duration_reshedule->addLine('process_dates();');
		$java_duration_reshedule->addLine('factor = document.frmProduction.elements["FactorType"];');
	}
	
	if( $create ) addTaskHeader();
	
	if( isset($datensatz['TaskGroup']) && !isset($tasks) ) $tasks = $datensatz['TaskGroup'];

	// notset: keine Änderung, true: deadline übergehen
	if( $fill ) unset($tasks_allfinished);
	if( $fill ) $autofill_group = array();
	
	foreach( $tasks as $task ) {
		$pos = $task['Position'];
		
		if( $create ) addFormTask( $task['FunctionID'] , $pos );
		if( $fill ) {		
			// Hinweise zum Abgabedatum
			if( $task['StaffID'] > 0 && !$production_done ) checkTaskDates( $task , $datensatz['Deadline_Timestamp'] , $pos );
		}
		
		/* Reshedule-JS */			      
		if( $create ) {
			$java_duration_reshedule->addLine('feld = document.frmProduction.elements["TaskGroup['.$pos.'][Duration]"];');
			$java_duration_reshedule->addLine('staff = document.frmProduction.elements["TaskGroup['.$pos.'][StaffID]"];');
			$java_duration_reshedule->addLine('feld.value = staffduration(factor.value,'.$task[FunctionID].',staff.value,feld.value);');
		}

		if( $fill ) $new_autofill = array(
					"TaskID" => $task['TaskID'] ,
					"Position" => $task['Position'] ,
					"FunctionID" => $task['FunctionID'] ,
					"StaffID" => $task['StaffID'] ,
					"Duration" => $task['Duration'] ,
					"StartDate" => $task['StartDate'] ,
					"EndDate" => $task['EndDate'] ,
					"DateFix" => $task['DateFix'] ,
					"StatusID" => $task['StatusID'] );
		if( $fill ) $autofill_group[$pos] = array_merge( $new_autofill , map_prev($new_autofill) );
		// Manuell ersetzen:
		if( $fill ) $autofill_group[$pos]["StartDate_prev"] = $task['StartDate_prev'];
		if( $fill ) $autofill_group[$pos]["EndDate_prev"] = $task['EndDate_prev'];
		
		if( $fill ) {
			if( $task['StaffID'] != null ) {
				if( $task['StatusID'] == 3 && ($tasks_allfinished || !isset($tasks_allfinished)) ) $tasks_allfinished = true;
				else $tasks_allfinished = false;
			}
		}
		
		// Inaktive Mitarbeiter hinzufügen, wenn diese Ausgewählt sind
		if( $create && $task['StaffID'] != 0 && !isset( $staff[$task['StaffID']] ) ) {
			$group =& $form->getElement( "TaskGroup[$pos]" );
			$elements =& $group->getElements();
			$element =& $elements[4];
			$element->addOption( $allstaff[$task['StaffID']]." (inaktiv)" , $task['StaffID'] );
		}
		
	}
	if( $fill ) $autofill["TaskGroup"] = $autofill_group;	
	
	/* Reshedule-JS */ if( $create ) $java_duration_reshedule->echoHtml();
	
	// Hinweise zur Deadline
	if( $fill && !$production_done ) {
		//if( $tasks_allfinished != true )
		checkProductionDates( $datensatz['Deadline_Timestamp'] );
	}
}

function addTaskHeader() {
	global $form;
	$form->addElement('header', 'SchedulingHeader', '&nbsp;Ablauf der Produktion');
	$form->addElement('static', 'SchedulingTitles' , null , '<div class="spaltentitel">
							<span class="input_dropdown" style="width:135px;">Verantwortlich</span>
							<span class="input_duration">Dauer [h]</span>
							<span class="input_datetime" style="width:113px;">Startdatum</span>
							<span class="input_datetime" style="width:113px;">Enddatum</span>
							<span>Fix</span>
							<span>Status</dspan>
						   </div>' );
}

function addFormTask( $FunctionID , $pos , $hide=false ) {
	global $function, $form, $staff, $staff_function, $status, $ampel;
	
	$created[] = &HTML_QuickForm::createElement('hidden', "Position" , $pos );
	$created[] = &HTML_QuickForm::createElement('hidden', "FunctionID" , $FunctionID );
	$created[] = &HTML_QuickForm::createElement('hidden', "TaskID" , null );
	$created[] = &HTML_QuickForm::createElement('static', 'Div1' , null , '<div class="spalten"><span class="input_dropdown" style="width:135px;">');
	$created[] = &HTML_QuickForm::createElement('select', "StaffID", null , $staff_function[$FunctionID] , 'class="input_dropdown" style="width:135px;"');
	$created[] = &HTML_QuickForm::createElement('hidden', "StaffID_prev");
	$created[] = &HTML_QuickForm::createElement('static', 'Div2' , null , '</span><span class="input_duration">');
	$created[] = &HTML_QuickForm::createElement('text', "Duration" ,  null , 'class="input_duration"');
	$created[] = &HTML_QuickForm::createElement('hidden', "Duration_prev");
	$created[] = &HTML_QuickForm::createElement('static', 'Div3' , null , '</span><span class="input_datetime" style="width:116px;">');
	$created[] = &HTML_QuickForm::createElement('text', "StartDate" , null , 'class="input_datetime"');
	$created[] = &insertCalendar('StartDate_Kal','TaskGroup['.$pos.'][StartDate]',true);
	$created[] = &HTML_QuickForm::createElement('hidden', "StartDate_prev");
	$created[] = &HTML_QuickForm::createElement('static', 'Div4' , null , '</span><span class="input_datetime" style="width:116px;">');
	$created[] = &HTML_QuickForm::createElement('text', "EndDate" , null , 'class="input_datetime"');
	$created[] = &insertCalendar('EndDate_Kal','TaskGroup['.$pos.'][EndDate]',true);
	$created[] = &HTML_QuickForm::createElement('hidden', "EndDate_prev");
	$created[] = &HTML_QuickForm::createElement('static', 'Div4' , null , '</span><span style="width:83px;">');
	$created[] = &HTML_QuickForm::createElement('advcheckbox', "DateFix" , null , null );
	$created[] = &HTML_QuickForm::createElement('hidden', "DateFix_prev");
	$created[] = &HTML_QuickForm::createElement('select', "StatusID" , null, $status , 'class="input_statusdrop"');
	$created[] = &HTML_QuickForm::createElement('static', 'Div6' , null , '</span></div>');
	$created[] = &HTML_QuickForm::createElement('hidden', "StatusID_prev");
	$form->addGroup($created, "TaskGroup[$pos]" , $function[$FunctionID] , ' ');
	$form->addGroupRule("TaskGroup[$pos]", array( 	'StartDate' => array( array('Bitte ein Startdatum eingeben oder leer lassen.', 'datum') ) ,
							'EndDate' => array( array('Bitte ein Enddatum eingeben oder leer lassen.', 'datum') ) ,
							'Duration' => array( array('Bitte eine Zeitdauer eingeben oder leer lassen.', 'zeit') ) ) );
}

function addDeleteScript( $ProductionID ) {
$production = SQLLQuery("SELECT ProductionNo, Title FROM Production WHERE ProductionID='$ProductionID'");
$production[Title] = addslashes($production[Title]);
echo <<<END
<script language="JavaScript" type="text/javascript">
<!--
function deleteProduction() {
	if( confirm("ACHTUNG: Wollen Sie die Produktion \"$production[Title]\", Nr. $production[ProductionNo] wirklich löschen?") ) {
		window.location.href = "production.php?action=delete&ProductionID=$ProductionID";
	}
}
-->
</script>
END;
}

$java_process_dates->echoHtml();

?>

<script language="JavaScript" type="text/javascript">
	function markStaff() {
		<?php
		$native_lang = SQLAQuery("SELECT StaffID, NativeLanguageID FROM Staff WHERE StaffID>0");
		echo "		var muttersprache = new Array();\n";
		foreach( $native_lang as $staff_id => $lang_id ) {
			if( $lang_id == "" ) $lang_id = "0";
			echo "		muttersprache[$staff_id] = $lang_id;\n";
		}
		echo "var sprache = new Array();\n";
		$languages = SQLQuery("SELECT StaffID, LanguageID FROM StaffLanguage ORDER BY StaffID ASC");
		$last_staffid = null;
		foreach( $languages as $language ) {
			if( $language['StaffID'] == $last_staffid ) $i++;
			else { 
				echo "		sprache[".$language['StaffID']."] = new Array();\n";
				$i = 0;
			}
			echo "		sprache[".$language['StaffID']."][".$i."] = ".$language['LanguageID'].";\n";
			$last_staffid = $language['StaffID'];
		}
		?>
		if( document.frmProduction && document.frmProduction.elements["LanguageToID"] ) {
			var feld = document.frmProduction.elements["LanguageToID"];
			if( feld.selectedIndex ) { 
				var sprache_to = feld.options[feld.selectedIndex].value;
				feld = document.frmProduction.elements["LanguageFromID"];
				var sprache_from = feld.options[feld.selectedIndex].value;
				
				// Foreach Task
				for (var task = 0; task <= 64; task++) {
					if( document.frmProduction.elements["TaskGroup["+task+"][StaffID]"] ) {
						feld = document.frmProduction.elements["TaskGroup["+task+"][StaffID]"];
						//taskfunction = document.frmProduction.elements["TaskGroup["+task+"][FunctionID]"];
						//alert( taskfunction.value );
						// Foreach Staff in List
						for (var j = 1; j < feld.options.length; j++) {
							var text = feld.options[j].text;
							var staff_id = feld.options[j].value;
							// Bisherige Kennzeichnung entfernen (Stringvergleich)
							var pos1 = text.indexOf("█ ");
							var pos2 = text.indexOf("▓ ");
							var pos3 = text.indexOf("▁ ");
							if( pos1 >= 0 && pos1 < 3 || pos2 >= 0 && pos2 < 3 || pos3 >= 0 && pos3 < 3 ) text = text.substr(2);
							// Neu kennzeichnen
							// TODO: Data Management kennzeichnen
							// Sprache kennzeichnen
							if( sprache_from > 0 && sprache_to > 0 ) {
								// check, ob Zielsprache = Muttersprache
								if( sprache_to == muttersprache[staff_id] )
									var muttersprache_found = 1;
								else
									var muttersprache_found = 0;
								// check, ob beide Sprachen verfügbar sind
								var sprache_to_found = 0;
								var sprache_from_found = 0;
								if( sprache[staff_id] ) {
									for (var k = 0; k < sprache[staff_id].length; k++) {
										if( sprache_from == sprache[staff_id][k] ) sprache_from_found = 1;
										if( sprache_to == sprache[staff_id][k] ) sprache_to_found = 1;
									}
								}
								if( sprache_from_found && muttersprache_found ) text="█ "+text;
								else if( sprache_to_found && sprache_from_found ) text="▓ "+text;
								else  text="▁ "+text;
							}
							feld.options[j].text = text;
						}
					}
				}
			}
		}
	}
	

</script>

<div id="productionlist_frame" style="float:right;">
<iframe frameborder="0" src="production_list.php?selectedID=<?php echo $ProductionID; ?>&scroll=1" width="100%" height="100%" name="ProductionList"></iframe>
<span style="margin:0;">[ <a href="javascript:scrollProductionUp();">nach oben</a> ]</span>
</div>

<div id="formular">

<?php
// ### Formular anzeigen ----------------------------------------------------------------------------------------------
if( isset($display_message) ) echo( "<b style=\"color:red;\">$display_message</b>\n" ); 
if( $display_form ) { 
	$form->display();
	echo '<span class="uplink">[ <a href="javascript:scrollUp();">nach oben</a> ]</span>';
}
// --------------------------------------------------------------------------------------------------------------------
?>
</div>

<?php insertFooter();
$mdb2->disconnect(); ?>

</body>
<script type="text/javascript">
//Adds a button to timesheets attaching projectNumber to search
var proGr1=document.getElementsByName("ProductionNoGrp[ProductionNo1]")[0];var proGr2=document.getElementsByName("ProductionNoGrp[ProductionNo2]")[0];proGr1.parentNode.innerHTML+="<button type='button' id='log_times'>Log Times</button>";document.getElementById("log_times").onclick=function(){var e,t="";if(proGr1!=null){e=proGr1.value}if(proGr2!=null){t=proGr2.value}window.open("http://timesheets.titelbild.de/time_logs/new?utf8=%E2%9C%93&commit=New&project_number="+e+t)}
</script>

</html>
