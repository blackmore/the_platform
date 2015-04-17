<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Mitarbeiter</title>
	
	<link rel="stylesheet" href="productions.css" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language='JavaScript' src='include/production.js'></script>
	
	<style type="text/css"> 
	body {
		width:1130px !important;
	}
	#createbuttons {
		background-image:url('images/face-yellow.png') !important;
	}
	</style> 
	<!--[if gte IE 6]>
	<style type="text/css"> 
	button {
		font: 10px "Lucida Grande",Arial, Geneva, Verdana, Helvetica, sans-serif black !important;

	}
	</style> 
	<![endif]-->
		
</head>
<body>

<?php
// Was soll getan werden? action=[create]|load|insert|update
$action = getGet('action','');

// ### Leiste ---------------------------------------------------------------------------------------------------------
echo "<div id=\"createbuttons\">\n";

if( $action == 'logout' ) {
	$auth->logout();
	$auth->start();
}

echo $login_box;
if( $auth->checkAuth() ) echo "<div id=\"login\">Eingeloggt als: ".$auth->getUsername()." [ <a href=\"$PHP_SELF?action=logout\">ausloggen</a> ]</div>";
if( $auth->getAuthData("GroupID") == $GROUPID_OFFICE ) {
	echo "<button type=button value=\"Mitarbeiter erstellen\" onClick=\"createStaff()\">Mitarbeiter erstellen</button>";
	echo "&nbsp; Ansicht:";
	echo "&nbsp; <button type=button value=\"Zur Produktionsliste\" onClick=\"gotoProduction()\">Zur Produktionsliste</button>";
}
echo "&nbsp; <button type=button value=\"Zum Trackingsheet\" onClick=\"gotoTracking()\">Zum Trackingsheet</button> ";
echo "</div>\n";
// --------------------------------------------------------------------------------------------------------------------

if( $auth->checkAuth() == false ) die("\n<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");
if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE ) die("\n<b style=\"color:red;\">Zugriff verweigert.</b>\n</body></html>");

// neues Formular
$form = new HTML_QuickForm('frmStaff', 'post' , '?action=update' , null , "class=\"stafftable\"" , true );

$buttons[] = &HTML_QuickForm::createElement('static', 'BtnDiv' , null , '<span style="float:right;">' );
$button_delete = null;

// Defaultwert, der aber angepasst wird
$save_button_string = "Speichern";
$display_form = false;

// ### Formular-Generierung ### ---------------------------------------------------------------------------------------

$form->addElement('header', 'StaffHeader', '&nbsp;Mitarbeiter');

	$form->addElement('hidden', 'StaffID');
	
	$form->addElement('text', 'Name', 'Name', 'class="input_long"');
	$form->addElement('hidden', 'Name_prev');
	$form->addElement('select', 'GroupID', 'Gruppe' , $staffgroup, 'class="input_dropdown"');
	$form->addElement('hidden', 'GroupID_prev');
	$form->addElement('select', 'NativeLanguageID', 'Muttersprache' , $language , 'class="input_dropdown"');
	$form->addElement('hidden', 'NativeLanguageID_prev');
	$select = &HTML_QuickForm::createElement('select', 'Languages', 'Sprachen<br></b>(Auswahl mit Strg-Taste)<b>', $languagesel ,  'class="input_select"');
	$select->setMultiple(true);
	$form->addElement($select);
	$select = &HTML_QuickForm::createElement('hiddenselect', 'Languages_prev' , null , $languagesel );
	$select->setMultiple(true);
	$form->addElement($select);
	$form->addElement('text', 'Login', 'Login-Name', 'class="input_long"');
	$form->addElement('hidden', 'Login_prev');
	$form->addElement('password', 'Password', 'Neues Passwort', 'class="input_long"');
	$form->addElement('password', 'Password2', 'Passwort Wiederholung', 'class="input_long"');
	$form->addElement('text', 'EMail', 'E-Mail Adresse', 'class="input_long"');
	$form->addElement('hidden', 'EMail_prev');
	$form->addElement('advcheckbox', 'Active', 'Status' , 'Aktiv' );
	$form->addElement('hidden', 'Active_prev');

$form->addElement('header', 'StaffHeader2', '&nbsp;Mitarbeiterstärken');


	
	$strengths = SQLQuery("SELECT FunctionID, Function FROM Function WHERE Function IS NOT NULL" );
	foreach( $strengths as $strength ) {
		$form->addElement('select', "Strength[$strength[FunctionID]]", "$strength[Function]" , $strength_skala , 'style="width:50px;"');
		$form->addElement('hidden', "Strength_prev[$strength[FunctionID]]" );
	}
	
	$buttons[] = &HTML_QuickForm::createElement('reset', 'btnClear', 'Zurücksetzen' , 'class="button"' );

// --------------------------------------------------------------------------------------------------------------------

// Regeln für Gültigkeit von Feldern (bitte sparsam!)

function is_unique( $str ) {
	global $allstaff, $StaffID;
	$erg = array_search( $str , $allstaff );
	if( $erg==false || $erg==null || $erg==$StaffID ) return true;
	else return false;
}

function is_unique_login( $str ) {
	global $alllogins, $StaffID;
	$erg = array_search( strtolower($str) , $alllogins );
	if( $erg==false || $erg==null || $erg==$StaffID ) return true;
	else return false;
}
$form->applyFilter('Name', 'trim' );
$form->applyFilter('Login', 'trim' );
$form->applyFilter('Login', 'strtolower' );
$form->registerRule('unique', 'callback' , 'is_unique' );
$form->registerRule('unique_login', 'callback' , 'is_unique_login' );
$form->addRule('Name', 'Dieses Feld wird zur Identifikation benötigt.', 'required');
$form->addRule('Login', 'Dieses Feld wird zur Identifikation benötigt.', 'required');
$form->addRule('Name', 'Es gibt bereits einen Mitarbeiter mit diesem Namen.', 'unique' );
$form->addRule('Login', 'Es gibt bereits einen Mitarbeiter mit diesem Login.', 'unique_login' );
$form->addRule(array('Password','Password2'), 'Die Passwörter stimmen nicht überein.', 'compare');

$autofill = array( 'Active' => "1" );

switch( $action ) {
case 'delete' : // ### Datensatz löschen ------------------------------------------------------------------------------
	DebugMSG("Delete");
	$StaffID = getGet( 'StaffID' , 'null' );
	if( $StaffID > 0 ) {
		// TODO: Auch im TimeTracking schauen!
		$datensatz = SQLQuery( "SELECT ProductionID FROM Production WHERE ManagerID = '$StaffID' OR CreatedByID = '$StaffID'" );
		$datensatz_tasks = SQLQuery( "SELECT ProductionID FROM Task WHERE StaffID = '$StaffID'" );
		if( count($datensatz) > 0 || count($datensatz_tasks) > 0 ) {
			$display_message = "Der Mitarbeiter konnte aufgrund von bestehenden Verknüpfungen nicht gelöscht werden.
			Er erscheint jedoch nicht mehr in der Auswahlliste, wenn Sie ihn deaktivieren.";
			// ab hier geht es weiter zu load....!
		} else {
			SQLLQuery( 'BEGIN' );
			SQLIQuery( "DELETE FROM StaffLanguage WHERE StaffID = '$StaffID'" );
			SQLIQuery( "DELETE FROM Staff WHERE StaffID = '$StaffID' LIMIT 1" );
			SQLLQuery( 'COMMIT' );
			$display_message = "Der Mitarbeiter wurde gelöscht.";
			$form->updateAttributes(array('action'=>"?action=insert"));
			$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Mitarbeiter erstellen" );
			// Defaults eintragen
			$StaffID = null;
			$form->setDefaults($autofill);
			$display_form = true;
			break;
		}
	} else {
		$display_form = false;
		$display_message ="Konnte den geforderten Mitarbeiter nicht finden.";
		$StaffID = null;
	}
case 'insert' : // ### Formular in Datenbank schreiben ----------------------------------------------------------------	
	if( $action == 'insert' ) {
	DebugMSG("Insert");
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$form->process('insert_data', false);
		// ab hier geht es weiter zu load....!
	} else {
		$form->updateAttributes(array('action'=>"?action=insert"));
		$display_message = "Fehlerhafte Eingaben. Der Mitarbeiter wurde nicht erstellt.";
		$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Mitarbeiter erstellen");
		$display_form = true;
		break;
	}
	}
case 'update' : // ### Datensatz aktualisieren ------------------------------------------------------------------------
	if( $action == 'update' ) {
	DebugMSG("Update");
	$StaffID = $form->getSubmitValue('StaffID');
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$form->process('update_data', false);
		// ab hier geht es weiter zu load....!
	} else {
		$display_message = "Fehlerhafte Eingaben. Die Änderungen wurden noch nicht gespeichert.";
		$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Änderungen Speichern");
		$button_delete = &HTML_QuickForm::createElement('button', 'btnDelete', 'löschen', 'onClick=deleteStaff()' );
		addDeleteScript( $StaffID );
		$display_form = true;
		break;
	}
	}
	loadStaff();
case 'load' : // ### Datensatz laden ----------------------------------------------------------------------------------
	// Werte einsetzen und die fehlenden Formularelemente erstellen
	DebugMSG("Es wird geladen");
	if( !isset($StaffID)) $StaffID = getGet( 'StaffID' , 'null' );
	
	if( !is_numeric($StaffID) || !load_data( $StaffID ) ) {
		$display_form = false;
		$display_message ="Konnte den geforderten Mitarbeiter nicht finden.";
		$StaffID = null;
		break;
	}
	
	$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Änderungen Speichern");
	$button_delete = &HTML_QuickForm::createElement('button', 'btnDelete', 'löschen', 'onClick=deleteStaff()' );
	$display_form = true;
	// Defaults eintragen
	$form->setConstants($autofill);
	addDeleteScript( $StaffID );
	break;
default: // ### Neues Formular anlegen --------------------------------------------------------------------------
	// komplettes Formular mit Defaultwerten erstellen
	DebugMSG("Leeres Formular");
	$form->updateAttributes(array('action'=>"?action=insert"));
	$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Mitarbeiter erstellen" );
	// Defaults eintragen
	$form->setDefaults($autofill);
	$display_form = true;
	break;
} // ------------------------------------------------------------------------------------------------------------------

$buttons[] = &HTML_QuickForm::createElement('static', 'BtnEndDiv' , null , '</span>' );
$form->addElement($button_delete);
$form->addGroup($buttons, null, null, '&nbsp;');

function load_data( $StaffID ) {
	global $autofill;
	$datensatz = SQLLQuery("SELECT StaffID, Name, Login, GroupID, EMail, Active, NativeLanguageID FROM Staff WHERE StaffID=$StaffID" );
	if( $datensatz['StaffID'] != $StaffID || $StaffID < 1 )
		return false;
	else {
		$datensatz_lang = SQLQuery("SELECT StaffID, LanguageID FROM StaffLanguage WHERE StaffID=$StaffID" );
		$autofill_lang = array();
		if( isset($datensatz_lang) ) {
			foreach( $datensatz_lang as $lang ) {
				$autofill_lang[] = $lang['LanguageID'];
			}
		}
		
		$strengths = SQLQuery("SELECT FunctionID, Value FROM Strength WHERE StaffID=$StaffID" );
		$autofill_strength = array();
		foreach( $strengths as $strength ) {
			$autofill_strength[$strength['FunctionID']] = "$strength[Value]";
		}
		
		$autofill = array_merge( $datensatz , map_prev($datensatz) , array("Languages" => $autofill_lang) , array("Languages_prev" => $autofill_lang) );
		$autofill['Strength'] = $autofill_strength;
		$autofill['Strength_prev'] = $autofill_strength;					
				
	}
	DebugMSG("ID: $StaffID ");
	
	return true;
}

function insert_data( $values ) {
	global $form, $StaffID;
	DebugMSG("Verarbeiten...");
	
	// Produktion speichern:
	// Diese Daten werden gespeichert:
	
	if( $values['Active'] == "" ) $values['Active'] = "0";
	
	$eintrag_array = array( "GroupID" => $values['GroupID'] ,
				"Login" => $values['Login'] ,
				"Name" => $values['Name'] ,
				"EMail" => $values['EMail'] ,
				"Active" => $values['Active'] ,
				"NativeLanguageID" => $values['NativeLanguageID'] );
	if( $values['Password']!="" ) $eintrag_array['Password'] = md5( $values['Password'] );

	SQLLQuery( 'BEGIN' );
	$StaffID=SQLIQuery( "INSERT INTO Staff ( ".explode_keys($eintrag_array)." ) VALUES ( ".explode_values($eintrag_array)." )" );
	
	// Sprachen
	if( count( $values['Languages'] )>0 ) {
		foreach( $values['Languages'] as $lang ) {
			SQLIQuery( "INSERT INTO StaffLanguage ( StaffID , LanguageID ) VALUES ( $StaffID , $lang )" );
		}
	}
	
	// Stärken
	foreach( $values['Strength'] as $function_id => $value ) {
		SQLIQuery( "INSERT INTO Strength ( StaffID , FunctionID , Value ) VALUES ( $StaffID , $function_id , $value )" );
	}
	
	DebugMSG("Neue ID: $StaffID");
	SQLLQuery( 'COMMIT' );
}

function update_data( $values ) {
	global $StaffID;
	// prüfe, ob Datensatz bereits existiert
	if( $values['StaffID'] != "" && sizeof( SQLLQuery("SELECT StaffID FROM Staff WHERE StaffID=$values[StaffID]" )) > 0 && $values['StaffID'] > 0 ) {
		// Datensatz Update
		DebugMSG("Verarbeiten...");
		
		if( $values['Active'] == "" ) $values['Active'] = "0";
		
		// Diese Daten werden gespeichert:
		$eintrag_array = make_eintrag_array( $values , array(
				"GroupID" => $values['GroupID'] ,
				"Login" => $values['Login'] ,
				"Name" => $values['Name'] ,
				"EMail" => $values['EMail'] ,
				"Active" => $values['Active'] ,
				"NativeLanguageID" => $values['NativeLanguageID'] ));
		
		if( $values['Password']!="" ) $eintrag_array['Password'] = md5( $values['Password'] );
		$StaffID = $values[StaffID];
		
		SQLLQuery( 'BEGIN' );
		if( count($eintrag_array)>0 ) {
			SQLIQuery( "UPDATE Staff SET ".explode_array($eintrag_array)." WHERE StaffID = '$StaffID' LIMIT 1"  );
		}
		if( $values['Languages'] != $values['Languages_prev'] ) {
			SQLIQuery( "DELETE FROM StaffLanguage WHERE StaffID = '$StaffID'" );
			foreach( $values['Languages'] as $lang ) {
				SQLIQuery( "INSERT INTO StaffLanguage ( StaffID , LanguageID ) VALUES ( $StaffID , $lang )" );
			}
		}
		// Stärken
		foreach( $values['Strength'] as $function_id => $value ) {
			if( $value != $values['Strength_prev'][$function_id] ) {
				// Für den Fall, dass neue Definitionen dazukommen:
				if( $values['Strength_prev'][$function_id] == null ) {
					SQLIQuery( "INSERT INTO Strength ( StaffID , FunctionID , Value ) VALUES ( $StaffID , $function_id , $value )" );	
				} else {
					SQLIQuery( "UPDATE Strength SET Value = $value WHERE StaffID = '$StaffID' AND FunctionID = '$function_id' LIMIT 1" );
				}
			}
			
		}
		SQLLQuery( 'COMMIT' );
		// TODO: Passwort nach dem Schreiben aus dem Formular entfernen
	} else {
		$display_form = false;
		$display_message ="Konnte den geforderten Mitarbeiter nicht finden.";
		$StaffID = null;
	}
}

function addDeleteScript( $StaffID ) {
global $allstaff;
// TODO: Fehler: Nach Insert stimmt der Name noch nicht!
echo <<<END
<script language="JavaScript" type="text/javascript">
<!--
function deleteStaff() {
	if( confirm("ACHTUNG: Wollen Sie den Mitarbeiter \"$allstaff[$StaffID]\" wirklich löschen?") ) {
		window.location.href = "staff.php?action=delete&StaffID=$StaffID";
	}
}
-->
</script>
END;
}

?>

<div id="productionlist" style="float:right;">
<div class="productionlogo"><a href="index.php"><img src="images/titelbild.gif" alt="" style="border:none;"></a></div>
<table class="productiontable" cellspacing="0">
<tr class="productionheader"><td>Login</td><td>Mitarbeiter</td><td>Gruppe</td><td>Aktiv</td></tr>
<?php
// ### Liste der Mitarbeiter anzeigen ### -----------------------------------------------------------------------------

// Hole Liste der Mitarbeiter
$stafflist = SQLQuery("SELECT StaffID, Login, Name, GroupID, Active FROM Staff WHERE StaffID > 0 ORDER BY Login ASC");

foreach( $stafflist as $curstaff ) {
	$current = ($curstaff['StaffID']==$StaffID) ? "class=\"productioncurrent\"" : "";
	echo "<tr><td $current ><a href=\"$PHP_SELF?action=load&StaffID=$curstaff[StaffID]\">$curstaff[Login]</a></td><td $current >".$curstaff['Name']."</td><td $current >".$staffgroup[$curstaff['GroupID']]."</td><td $current >".$curstaff['Active']."</td></tr>\n";
}
// --------------------------------------------------------------------------------------------------------------------
?>
</table>
</div>

<div id="formular" style="width:500px;">
<?php
// ### Formular anzeigen ----------------------------------------------------------------------------------------------
if( isset($display_message) ) echo( "<b style=\"color:red;\">$display_message</b>\n" ); 
if( $display_form ) $form->display(); 
// --------------------------------------------------------------------------------------------------------------------
?>
</div>

<?php insertFooter();
$mdb2->disconnect(); ?>

</body>
</html>
