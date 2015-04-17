<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Tracking</title>
	
	<link rel="stylesheet" href="productions.css?date=0801" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language='JavaScript' src='include/production.js'></script>
	
	<style type="text/css"> 
	body {
		width:1200px !important;
	}
	#createbuttons {
		background-image:url('images/face-blue.png') !important;
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
	.spalten .input_datetime {
		margin-right:8px !important;
	}
	</style> 
	<![endif]-->
		
</head>
<body onLoad="onLoadStart()">

<?php
// Was soll getan werden? action=[create]|load|insert|update
$action = getGet('action','');
// Produktion abgeschlossen
$production_done = false;

if( $action == 'load' && getGet('ProductionID',null) == "" ) $action = 'create';

// ### Login-Leiste ---------------------------------------------------------------------------------------------------
echo "<div id=\"createbuttons\">";

if( $action == 'logout' ) {
	$auth->logout();
	$auth->start();
}

if( $auth->checkAuth() ) {
	echo "<div id=\"login\">Eingeloggt als: ".$auth->getUsername()." [ <a href=\"$PHP_SELF?action=logout\">ausloggen</a> ]</div><b>Tracking Sheet</b>";
	
	// Hole Liste der Produktionen
	// TODO: verkleinern, da alles in tracking_list gemacht wird!
	if( $auth->getAuthData("GroupID") == $GROUPID_OFFICE ) {
	echo "&nbsp; <button type=button value=\"Druckausgabe\" onClick=\"gotoPrint()\">Druckausgabe</button>";
	echo "&nbsp; Ansicht:";
	echo "&nbsp; <button type=button value=\"Zur Mitarbeiterliste\" onClick=\"gotoStaff()\">Zur Mitarbeiterliste</button>";
	echo "&nbsp; <button type=button value=\"Zur Produktionsliste\" onClick=\"gotoProduction()\">Zur Produktionsliste</button>";
	}
}

echo $login_box;

echo "</div>\n";
// --------------------------------------------------------------------------------------------------------------------

if( $auth->checkAuth() == false ) die("<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");

// neues Formular
$form = new HTML_QuickForm('frmTracking', 'post' , '?action=update' , null , "class=\"formtable\"" , true );

$display_form = false;

// ### Formular-Generierung ### ---------------------------------------------------------------------------------------
// this is just my bit to test 
//echo "<p>this is just a test</p>";

// Gruppe Detail Information
$form->addElement('header', 'ProductionHeader', '&nbsp;Informationen zur Produktion');

	$form->addElement('hidden', 'ProductionID');
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('text', 'ProductionNo', 'Produktion Nr.' );
	$buffer[] = &HTML_QuickForm::createElement('select', 'CustomerID', 'Kunde', $allcustomer );
	$buffer[] = &HTML_QuickForm::createElement('text', 'Title', 'Produktionstitel' );
	$buffer[] = &HTML_QuickForm::createElement('select', 'CategoryID', 'Kategorie' , $category );
	$buffer[] = &HTML_QuickForm::createElement('select', 'ManagerID', 'Projektleiter' , $allstaff );
	foreach( $buffer as $element ) {
		$element->freeze();
		$form->addElement($element);
	}
	
	$grid =& new HTML_QuickForm_ElementGrid( null , "Erstellt von / Datum" );
	unset($buffer);
	$buffer[] = &HTML_QuickForm::createElement('select', 'CreatedByID', null , $allstaff );
	$buffer[] = &HTML_QuickForm::createElement('text', 'CreatedDate', null );
	foreach( $buffer as $element ) {
		$element->freeze();
	}
	$grid->addRow($buffer);
	$form->addElement($grid);
	
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
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('select', 'ProjectType', 'Art des Projekts', array(''=>''),  'class="input_dropdown"');
	$field_ref['ProjectType'] =& $buffer;
	$form->addElement($buffer);
	$form->addElement('hidden', 'ProjectType_prev');
	
	unset($buffer);
	$buffer = &HTML_QuickForm::createElement('select', 'MasterType', 'Master/Film Typ', array(''=>''),  'class="input_dropdown"');
	$field_ref['MasterType'] =& $buffer;
	$form->addElement($buffer);
	$form->addElement('hidden', 'MasterType_prev');

// --------------------------------------------------------------------------------------------------------------------

// Regeln für Gültigkeit von Feldern (bitte sparsam!)
$form->registerRule('datum', 'callback' , 'is_a_date' );
$form->registerRule('zeit', 'callback' , 'is_a_time' );
$form->registerRule('timetracking', 'callback' , 'check_timetracking' );

$form->addRule('Duration', 'Bitte eine Zahl eingeben oder leer lassen.', 'numeric' , null , 'Server' , false , true );

	// Button-Zeile vorbereiten (wird später entsprechend verändert)
	$grid =& new HTML_QuickForm_ElementGrid( null , "" );
	unset($buffer);
	$buffer['btnClear'] = &HTML_QuickForm::createElement('reset', 'btnClear', 'Zurücksetzen' , 'class="button"' );
	$buffer['btnSubmit'] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Submit");
	$field_ref['btnClear'] =&$buffer['btnClear'];
	$field_ref['btnSubmit'] =&$buffer['btnSubmit'];
	$grid->addRow($buffer);
	$grid->setClass('elementGridRight');
	$field_ref['btnGrid'] =&$grid;
	// wird erst später eingefügt
	//$form->addElement($grid);

switch( $action ) {
case 'update' : // ### Datensatz aktualisieren ------------------------------------------------------------------------
	if( $action == 'update' ) {
		DebugMSG("Update");

		$ProductionID = $form->getSubmitValue('ProductionID');
		$CategoryID = $form->getSubmitValue('CategoryID');
		addLoadedFormElements( $form->getSubmitValues() , null , null , null ,$CategoryID , true );
		
		if ($form->validate()) { // Sind alle Daten speicherbar?
			$form->process('update_data', false);
			// ab hier geht es weiter zu load....!
		} else {
			$display_message = "Fehlerhafte Eingaben. Die Änderungen wurden noch nicht gespeichert.";
			$field_ref['btnSubmit']->setValue("Änderungen speichern");
			$display_form = true;
			break;
		}
	}
case 'load' : // ### Datensatz laden ----------------------------------------------------------------------------------
	// Werte einsetzen und die fehlenden Formularelemente erstellen
	if( !isset($ProductionID)) $ProductionID = getGet( 'ProductionID' , 'null' );
	DebugMSG("Es wird geladen");
	
	// Rechte zum Anzeigen prüfen
	if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE && $auth->getAuthData("GroupID") != $GROUPID_DATA ) {
			$datensatz = SQLQuery("
			SELECT Production.ProductionID, Task.TaskID
			FROM Production LEFT JOIN Task ON Production.ProductionID = Task.ProductionID
			WHERE Production.ProductionID = $ProductionID AND Production.StatusID < $PRODUCTIONSTATUS_LOCK AND
				( Task.StaffID = ".$auth->getAuthData("StaffID")."
				OR Production.ManagerID = ".$auth->getAuthData("StaffID")."
				OR Production.CreatedByID = ".$auth->getAuthData("StaffID").")" );
		if( count($datensatz)==0 ) {
			$display_form = false;
			$display_message = "Sie haben nicht die erforderlichen Rechte, diese Produktion anzuzeigen.";
			$ProductionID = null;
			break;
		}
	}
	
	// Interne Informationen nur im Project Office sichtbar
	if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE ) $form->removeElement("InternalNotes");
	
	if( $action == 'load' ) $create=true;
		else $create=false;
	if( !load_data( $ProductionID , $create ) ) {
		$display_form = false;
		$display_message ="Konnte die geforderte Produktion nicht finden.";
		$ProductionID = null;
		break;
	}
	
	$field_ref['btnSubmit']->setValue("Änderungen speichern");
	$display_form = true;
	// Defaults eintragen
	$form->setConstants($autofill);
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

// Buttonreihen einfügen
if( !$production_done ) $form->addElement($field_ref['btnGrid']);

//$group =& $form->getElement( 'CategoryGroup' );
//unset($buttons['break']);
//$buttons['break'] = &HTML_QuickForm::createElement('static', 'PageBreak' , null ,  '<div style="page-break-after:always"></div>' );


function load_data( $ProductionID , $create ) {
	global $autofill, $create_tracking, $form, $buttons, $production_done, $auth, $GROUPID_OFFICE, $field_ref;
	$datensatz = SQLLQuery("SELECT ProductionID, ProductionNo, CategoryID, CustomerID, Title, ManagerID, DATE_FORMAT(CreatedDate,'%d.%m.%y %H:%i') AS CreatedDate, CreatedByID , StatusID FROM Production WHERE ProductionID=$ProductionID" );
			
	if( $datensatz['ProductionID'] != $ProductionID || $ProductionID < 1 )
		return false;
	else {
		$autofill = $datensatz;			
	}
	
	DebugMSG("ID: $ProductionID ");
	// Formularelemente aus Datenbank erstellen
	$tasks = SQLQuery("SELECT TaskID, Position, FunctionID, StaffID, DATE_FORMAT(EndDate,'%d.%m.%y %H:%i') AS EndDate, EndDate AS EndDate_prev, Notes, StatusID FROM Task WHERE ProductionID=$ProductionID ORDER BY Position ASC" );
	$trackingfields = reduce_keys( $create_tracking['Alle'] );
	$trackingdata = SQLLQuery("SELECT Duration, LanguageFromID, LanguageToID, SpecialNotes, InternalNotes, ProjectType, MasterType, TechContact, $trackingfields FROM Tracking WHERE ProductionID=$ProductionID LIMIT 1" );
	$timetracking = SQLQuery("SELECT TimeTrackingID , StaffID , DATE_FORMAT(Date,'%d.%m.%Y') AS Date , Date AS Date_prev ,  DATE_FORMAT(Duration,'%H:%i') AS Duration , Wert, TypeID, Comment FROM TimeTracking WHERE ProductionID=$ProductionID ORDER BY Date_prev ASC" );	
			
	// Fertige Produktionen einfrieren
	if( $datensatz['StatusID'] == "2" || $datensatz['StatusID'] == "3" ) $production_done = true; else $production_done = false;
			
	addLoadedFormElements( $datensatz , $trackingdata , $tasks , $timetracking , $datensatz['CategoryID'] , $create );
	
	if( $production_done ) {
		// Fertige Produktionen einfrieren
		$form->freeze();
		// TODO: Buttons bei den Tasks entfernen
	}
	
	// TODO: Fertige Tasks einfrieren
	
	return true;
}

function update_data( $values ) {
	global $ProductionID, $auth, $display_form, $display_message, $form, $autofill, $field_ref, $STAFFID_DATA, $GROUPID_DATA, $GROUPID_OFFICE;
	// prüfe, ob Datensatz bereits existiert
	$datensatz = SQLLQuery("SELECT ProductionID, ManagerID FROM Production WHERE ProductionID=$values[ProductionID]" );
	if( $values['ProductionID'] != "" && sizeof( $datensatz ) > 0 && $values['ProductionID'] > 0 ) {
		// Datensatz Update
		DebugMSG("Verarbeiten...");

		$ProductionID = $values[ProductionID];
		$isManager = $auth->getAuthData("GroupID") == $GROUPID_OFFICE;
		
		SQLLQuery( 'BEGIN' );

		SQLIQuery( "UPDATE Staff SET LastSavedID = '$ProductionID' WHERE StaffID = '".$auth->getAuthData("StaffID")."'" );

		//Tracking speichern:
		$eintrag_array = make_eintrag_array( $values , 
		array( "Duration" => $values['Duration'] ,
			"LanguageFromID" => $values['LanguageFromID'] ,
			"LanguageToID" => $values['LanguageToID'] ,
			"SpecialNotes" => $values['SpecialNotes'] ,
			"InternalNotes" => $values['InternalNotes'] ,
			"TechContact" => $values['TechContact'] ,
			"ProjectType" => $values['ProjectType'] ,
			"MasterType" => $values['MasterType'] ));
		
		$tracking_array = array();
		if( isset($values['TrackingItem']) )
			foreach( $values['TrackingItem'] as $key => $value ) {
				if( $values['TrackingItem']["$key"] != $values['TrackingItem_prev'][$key] ) $tracking_array["$key"] = $value;
			}
		if( isset($values['TrackingGroup']) )
		foreach( $values['TrackingGroup'] as $abschnitt => $abschnitt_val ) {
			foreach( $values['TrackingGroup']["$abschnitt"] as $key => $value ) {
				if( $value == "" ) $value = 0;
				if( $value != $values['TrackingGroup_prev']["$abschnitt"][$key] ) $tracking_array["$key"] = $value;
			}
		}
		if( count($eintrag_array) > 0 && count($tracking_array) > 0 ) $komma = " , "; else $komma = " ";
		// Das Tracking-Array hat die Besonderheit, alle Werte als Leerstring statt als null-Wert zu schreiben:
		if( count($eintrag_array) > 0 || count($tracking_array) ) {
			SQLIQuery( "UPDATE Tracking SET ".explode_array($eintrag_array)."$komma".explode_array($tracking_array,"''")." WHERE ProductionID = '$ProductionID' LIMIT 1" );
		}
		// Tasks speichern:
		if( isset($values['TaskGroup']) ) {
			foreach($values['TaskGroup'] as $task => $value ) {
				// Rechte Prüfen
				if( $value['StaffID'] == $auth->getAuthData("StaffID") || $isManager || ($value['StaffID'] == $STAFFID_DATA && $auth->getAuthData("GroupID") == $GROUPID_DATA) ) {
					$eintrag_array = make_eintrag_array( $value , 
					array (	"Notes" => $value[Notes] ,
						"StatusID" => $value[StatusID] ));
					if( count($eintrag_array)>0 ) {
						SQLIQuery( "UPDATE Task SET ".explode_array($eintrag_array)." WHERE ProductionID = '$ProductionID' AND TaskID = '".$value['TaskID']."' LIMIT 1" );
					}
					if( isset($values['TaskGroup_check'][$value['Position']]) ) {
						foreach($values['TaskGroup_check'][$value['Position']] as $checkbox ) {
							$eintrag_array = make_eintrag_array( $checkbox , 
								array (	"Value" => $checkbox['Value'] ,
									"Notes" => $checkbox['Notes'] ));
							if( count($eintrag_array) > 0 )	
								SQLIQuery( "UPDATE Checkbox SET ".explode_array($eintrag_array)." WHERE CheckboxID = '".$checkbox['CheckboxID']."' LIMIT 1" );
						}
					}
				}
			}
		}
		
		// TimeTracking speichern:
		foreach($values['TimeTrackingGroup'] as $timetracking ) {
			if( $timetracking['TimeTrackingID'] != null ) {
				// bereits vorhanden
				if( $timetracking['TypeID'] != 0 ) {
					// update
					$eintrag_array = make_eintrag_array( $timetracking , array (
					"StaffID" => $timetracking['StaffID'] ,
						"Date" => input2date($timetracking['Date']) ,
						"Duration" => input2time($timetracking['Duration']) ,
						"Wert" => $timetracking['Wert'] ,
						"TypeID" => $timetracking['TypeID'] ,
						"Comment" => $timetracking['Comment'] ));
						if( count($eintrag_array) > 0 )
							SQLIQuery( "UPDATE TimeTracking SET ".explode_array($eintrag_array)." WHERE TimeTrackingID = $timetracking[TimeTrackingID] AND ProductionID = '$ProductionID'" );
				} else {
					// delete
					SQLIQuery( "DELETE FROM TimeTracking WHERE TimeTrackingID=$timetracking[TimeTrackingID] LIMIT 1" );
				}
			} elseif( $timetracking['TypeID'] != 0 ) {
				// nicht vorhanden, aber gesetzt
				// insert				
				$eintrag_array = array (
					"ProductionID" => $ProductionID ,
					"StaffID" => $timetracking['StaffID'] ,
					"Date" => input2date($timetracking['Date']) ,
					"Duration" => input2time($timetracking['Duration']) ,
					"Wert" => $timetracking['Wert'] ,
					"TypeID" => $timetracking['TypeID'] ,
					"Comment" => $timetracking['Comment'] );
				$TimeTrackingID = SQLIQuery( "INSERT INTO TimeTracking ( ".explode_keys($eintrag_array)." ) VALUES ( ".explode_values($eintrag_array)." )" );
			}
		
		}
		SQLLQuery( 'COMMIT' );
		
	} else {
		$display_form = false;
		$display_message ="Konnte die geforderte Produktion nicht finden.";
		$ProductionID = null;
	}
}

function addLoadedFormElements( $datensatz , $trackingdata , $tasks , $timetracking , $CategoryID , $create = true ) {
	global $autofill, $form, $create_tracking, $category, $allstaff, $customer , $allcustomer, $staff, $allstaff, $auth, $STAFFID_DATA, $GROUPID_DATA, $GROUPID_OFFICE, $production_done, $create_projecttype, $create_mastertype, $create_techcontact, $field_ref;
	
	// Soll autofill gemacht werden?
	$fill = isset($trackingdata);
	
	// Workaround für Übersetzung
	if( $category[$CategoryID] == "Übersetzung" ) $field_ref['DurationUnit']->setValue("Zeilen");
		
	if( !isset($datensatz['ProjectType']) ) $datensatz['ProjectType'] = $trackingdata['ProjectType'];
	if( !isset($datensatz['MasterType']) ) $datensatz['MasterType'] = $trackingdata['MasterType'];
	if( !isset($datensatz['TechContact']) ) $datensatz['TechContact'] = $trackingdata['TechContact'];

	// ProjectType und MasterType je nach Category
	if( $create ) {
		if( $create_projecttype[$category[$CategoryID]] ) {
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
	
	// ### Tracking -----------------------------------------------------------------------------------------------
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
				
	if( $create ) addTaskHeader();
				
	if( isset($datensatz['TaskGroup']) && !isset($tasks) ) $tasks = $datensatz['TaskGroup'];

	$isManager = $auth->getAuthData("GroupID") == $GROUPID_OFFICE;

	if( $fill ) $autofill_group = array();
	if( $fill ) $autofill_checkgroup = array();
	if( isset($tasks) ) {
		foreach( $tasks as $task ) {
			// Nur Tasks anzeigen, die einem Mitarbeiter zugeordnet sind
			if( isset($task['StaffID']) ) {
				$pos = $task['Position'];
				// Prüfe, ob der Task dem eingeloggten Mitarbeiter zugeordnet ist
				$isStaff = ($task['StaffID'] == $auth->getAuthData("StaffID")) || ($task['StaffID'] == $STAFFID_DATA && $auth->getAuthData("GroupID") == $GROUPID_DATA) ;
				
				if( $create ) addFormTask( $task['FunctionID'] , $pos , !$isManager  , $isStaff );
				
				// Hier werden die Checkboxen eingefüget	
				$checkboxen = SQLQuery("SELECT CheckboxID , Name , Value , Notes FROM Checkbox WHERE TaskID=".$task['TaskID']." ORDER BY CheckboxID ASC" );
				//if( isset($datensatz['TaskGroup_check']) ) $checkboxen = $datensatz['TaskGroup_check'][$pos];
				if( count($checkboxen) > 0 ) {
					foreach( $checkboxen as $checkbox ) {
						if( $create ) addFormCheckbox( $checkbox , $pos , !$isManager , $isStaff );
						if( $fill ) $new_autofill = array( 
							"Value" => $checkbox['Value'] ,
							"Notes" => $checkbox['Notes'] );
						if( $fill ) $autofill_checkgroup[$pos][$checkbox['CheckboxID']] = array_merge( $new_autofill , map_prev($new_autofill) );
					}
					
				}
				
				if( $fill ) {
					
					$new_autofill = array(
					"TaskID" => $task['TaskID'] ,
					"Position" => $task['Position'] ,
					"FunctionID" => $task['FunctionID'] ,
					"StaffID" => $task['StaffID'] ,
					"EndDate" => $task['EndDate'] ,
					"Notes" => $task['Notes'] ,
					"StatusID" => $task['StatusID'] );
					$autofill_group[$pos] = array_merge( $new_autofill , map_prev($new_autofill) );
					// Manuell ersetzen:
					$autofill_group[$pos]["EndDate_prev"] = $task['EndDate_prev'];
				}
				if( $task['StatusID'] == 3 ) $color = "green"; else $color = "#d73131";
				$form->setConstants( array( 'TaskGroup' => array( "$pos" => array( "Staff" => "<span style=\"color:".$color.";\">".$allstaff[$task['StaffID']]."</span>"))));
			}
					
		}
	}
	if( $fill ) $autofill["TaskGroup"] = $autofill_group;	
	if( $fill ) $autofill["TaskGroup_check"] = $autofill_checkgroup;
	
	// ### Time Tracking ------------------------------------------------------------------------------------------
	
	if( $create ) addTimeTrackingHeader();
	if( $fill ) $autofill_group = array();
	
	if( isset($datensatz['TimeTrackingGroup']) && !isset($timetracking) ) $timetracking = $datensatz['TimeTrackingGroup'];

	// Hiermit werden alle nicht übermittelten Werte überschrieben, damit die Zeitnachweistabelle stimmt
	$empty=array(	'StaffID'=>"",
			'TimeTrackingID'=>"",
			'Date' => "" ,
			'Duration'=>"",
			'Wert'=>"",
			'TypeID'=>"0",
			'Comment'=>"");

	$pos = 1;

	foreach( $timetracking as $timetrack ) {
		if( $timetrack['TypeID']!=0 ) {
				if( $create ) addTimeTracking( $pos );
				
				foreach( $timetrack as $key => $val ) {
					if( !isset($val) ) $timetrack[$key]=$empty[$key];
				}
				
				$form->setConstants( array( "TimeTrackingGroup" => array( $pos => array_merge( $timetrack , map_prev($timetrack) ) ) ) );
				//$autofill_group[$pos] = array_merge( $timetrack , map_prev($timetrack) );
				
				// Inaktive Mitarbeiter hinzufügen, wenn diese Ausgewählt sind
				if( $timetrack['StaffID'] != 0 && !isset( $staff[$timetrack['StaffID']] ) ) {
					$group =& $form->getElement( "TimeTrackingGroup[$pos]" );
					$elements =& $group->getElements();
					$element =& $elements[3];
					$element->addOption( $allstaff[$timetrack['StaffID']]." (i.)" , $timetrack['StaffID'] );
				}
				
				$pos++;
		}
	}
	
	// leere Eingabefelder
	if( !$production_done ) {
		for( $i = $pos ; $i<$pos+4 ; $i++ ) {
			if( $create ) addTimeTracking( $i );
			if( $fill ) $form->setConstants( array( "TimeTrackingGroup" => array( $i => array_merge($empty,map_prev($empty)) ) ) );
		}
	}
}

function addTimeTrackingHeader() {
	global $form, $field_ref, $production_done;
	if( !$production_done ) $form->addElement($field_ref['btnGrid']);
	$form->addElement('header', 'TimeTrackingHeader', '&nbsp;Zeitnachweis');
	$titletext = '	<div class="spaltentitel">';
	$titletext .= '	<span class="input_dropdown" style="width:120px;">Mitarbeiter</span>';
	$titletext .= '	<span class="input_date">Datum</span>
			<span class="input_duration">Stunden /</span>';
	$titletext .= '	<span class="input_duration">Auslagen</span>';
	$titletext .= '	<span class="input_dropdown" style="width:75px;">Tätigkeit</span>
			<span class="input_short" style="width:auto;">Bemerkungen</span></div>';
	$form->addElement('static', null , null , $titletext );
}

function addTimeTracking( $pos ) {
	global $form, $timetrackingtype, $staff_sek, $staff;
	$created[] = &HTML_QuickForm::createElement('hidden', "TimeTrackingID" , null );
	
	$created[] = &HTML_QuickForm::createElement('static', 'Div1' , null , '<div class="spalten">');	
	$created[] = &HTML_QuickForm::createElement('static', 'Div2' , null , '<span class="input_dropdown" style="width:120px;">');
	$created[] = &HTML_QuickForm::createElement('select', 'StaffID', null , $staff , 'class="input_dropdown" style="width:120px;"');
	$created[] = &HTML_QuickForm::createElement('hidden', "StaffID_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div3' , null , '</span>');
	$created[] = &HTML_QuickForm::createElement('static', 'Div4' , null , '<span class="input_date">');
	$created[] = &HTML_QuickForm::createElement('text', "Date" , null , 'class="input_date"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Date_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div5' , null , '</span><span class="input_duration">');
	$created[] = &HTML_QuickForm::createElement('text', "Duration" , null , 'class="input_duration"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Duration_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div6' , null , '</span><span class="input_duration">');
	$created[] = &HTML_QuickForm::createElement('text', "Wert" , null , 'class="input_duration"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Wert_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div7' , null , '</span><span class="input_dropdown" style="width:80px;">');
	$created['status'] = &HTML_QuickForm::createElement('select', "TypeID" , null, $timetrackingtype , 'class="input_dropdown" style="width:80px;" onChange="markStaff('.$pos.')"');
	$created[] = &HTML_QuickForm::createElement('hidden', "TypeID_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div8' , null , '</span><span class="input_short" style="width:120px;">');
	$created[] = &HTML_QuickForm::createElement('text', "Comment" , null , 'class="input_short" style="width:120px;"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Comment_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div9' , null , '</span></div>');
	
	$form->addGroup( $created, "TimeTrackingGroup[$pos]" , null , ' ' );
	$form->addRule( "TimeTrackingGroup[$pos]" , "Bitte Mitarbeiter auswählen und Datum und Zeitdauer eintragen." , 'timetracking' );
	$form->addGroupRule( "TimeTrackingGroup[$pos]", array( 	'Date' => array( array('Bitte ein Datum eingeben.', 'datum' ) ) ,
							'Duration' => array( array('Bitte eine Zeitdauer eingeben.', 'zeit') ) ) );
}

function addTaskHeader() {
	global $form, $field_ref, $production_done;
	if( !$production_done ) $form->addElement($field_ref['btnGrid']);
	$form->addElement('header', 'SchedulingHeader', '&nbsp;Ablauf der Produktion');
	$form->addElement('static', null , null , '<div class="spaltentitel">
							<span class="input_dropdown" style="width:99px;">Verantwortlich</span>
							<span class="input_datetime" style="width:82px;">Abgabedatum</span>
							<span class="input_medium">Bemerkungen</span>
							<span>Status</span>
						   </div>' );
}

function addFormTask( $FunctionID , $pos , $hide=true , $highlight ) {
	global $function, $form, $allstaff, $status;
	
	if( $highlight ) $hide=false;
	if( $highlight ) $highlight_tag = "background:white;font-weight:bold;border:1px solid #0095cd;width:91px;"; else $hilight_tag = "";
	
	$created[] = &HTML_QuickForm::createElement('hidden', "Position" , $pos );
	$created[] = &HTML_QuickForm::createElement('hidden', "FunctionID" , $FunctionID );
	$created[] = &HTML_QuickForm::createElement('hidden', "TaskID" , null );
	$created[] = &HTML_QuickForm::createElement('hidden', "StaffID" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div1' , null , '<div class="spalten"><span style="width:93px;padding-left:6px;'.$highlight_tag.'">');
	$created[] = &HTML_QuickForm::createElement('static', "Staff" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div2' , null , '</span><span class="input_datetime" style="margin-right:0px;">');
	$created[] = &HTML_QuickForm::createElement('text', "EndDate" , null );
	$created[] = &HTML_QuickForm::createElement('static', 'Div3' , null , '</span><span class="input_medium">');
	$created['notes'] = &HTML_QuickForm::createElement('text', "Notes" , null, 'class="input_medium"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Notes_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div4' , null , '</span><span>');	
	$created['status'] = &HTML_QuickForm::createElement('select', "StatusID" , null, $status );
	$created[] = &HTML_QuickForm::createElement('hidden', "StatusID_prev" );
	$created[] = &HTML_QuickForm::createElement('static', 'Div6' , null , '</span></div>');
	if( !$hide ) $created['submit'] = new HTML_QuickForm_button_freeze( 'btnFinished', "fertig" , 'onClick="markTaskFinished( '.$pos.' )"');
	$form->addElement('hidden', "TaskGroup[$pos][StatusID_prev]");
		
	foreach( array_keys($created) as $key ) {	
		$created[$key]->freeze();
	}
	
	if( !$hide ) {
		$created['status']->unfreeze();
		$created['notes']->unfreeze();
		$created['submit']->unfreeze();
	}
		
	$form->addGroup($created, "TaskGroup[$pos]" , $function[$FunctionID] , ' ');
}

function addFormCheckbox( $checkbox , $pos , $hide=true , $highlight ) {
	global $function, $form, $allstaff, $status;
	
	if( $highlight ) $hide=false;
	
	$created[] = &HTML_QuickForm::createElement('hidden', "CheckboxID" , $checkbox['CheckboxID'] );
	$created[] = &HTML_QuickForm::createElement('static', 'Div1' , null , '<div class="taskchecks">' );
	$created['box'] = &HTML_QuickForm::createElement('advcheckbox', "Value" , null , $checkbox['Name'] );
	$created[] = &HTML_QuickForm::createElement('static', 'Div2' , null , '</div>' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Value_prev" );
	$created['notes'] = &HTML_QuickForm::createElement('text', "Notes" , null, 'class="input_medium"' );
	$created[] = &HTML_QuickForm::createElement('hidden', "Notes_prev" );
		
	if( $hide ) {
		foreach( array_keys($created) as $key ) {	
			$created[$key]->freeze();
		}
	}
		
	$form->addGroup($created, "TaskGroup_check[".$pos."][".$checkbox['CheckboxID']."]" , "" , ' ');
}

?>

<script language="JavaScript" type="text/javascript">
<!--
function markStaff( pos ) {
	if( document.frmTracking ) {
		var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][TypeID]"];
		if( feld.value > 0 ) {
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][StaffID]"];
			if( feld.value == 0 ) {
				feld.value = <?php echo $auth->getAuthData("StaffID"); ?>;
				var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Date]"];
				feld.value = "<?php echo date('d.m.Y'); ?>";
				var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Duration]"];
				feld.focus();
			}
		} else {
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][StaffID]"];
			feld.value = 0;
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Date]"];
			feld.value = "";
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Duration]"];
			feld.value = "";
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Wert]"];
			feld.value = "";
			var feld = document.frmTracking.elements["TimeTrackingGroup["+pos+"][Comment]"];
			feld.value = "";
		}
	}
}
-->
</script>

<div id="productionlist_frame" style="float:right;">
<iframe frameborder="0" src="tracking_list.php?selectedID=<?php echo $ProductionID; ?>&scroll=1" width="100%" height="100%" name="ProductionList"></iframe>
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
<script type="text/javascript">
//Adds a button to timesheets attaching projectNumber to search
var proGr1=document.getElementsByName("ProductionNo")[0]; proGr1.parentNode.innerHTML+="<button type='button' id='log_times' style='margin-left:10px;'>Log Times</button>";document.getElementById("log_times").onclick=function(){var e,t="";if(proGr1!=null){e=proGr1.value};window.open("http://timesheets.titelbild.de/time_logs/new?utf8=%E2%9C%93&commit=New&project_number="+e)}
</script>
</body>
</html>
