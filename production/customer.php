<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Kunden</title>
	
	<link rel="stylesheet" href="productions.css" type="text/css" media="all">
	<link rel="stylesheet" href="productions-print.css" type="text/css" media="print">
	
	<script language="JavaScript" type="text/javascript">
	<!--
	// TODO: onClose
	function createCustomer() {
		window.location.href = "customer.php?action=create";
	}
	function loadCustomer() {
		feld = document.frmCustomer.elements["LoadGroup[Customers]"];
		index = feld.options[feld.selectedIndex].value;
		if( index > 0 ) {
			window.location.href = "customer.php?action=load&CustomerID="+index;
		}
	}
	function onLoadStart() {
		// Focus holen
		window.focus();
	}
	-->
	</script>
	
	<!--[if gte IE 6]>
	<style type="text/css"> 
	button {
		font: 10px "Lucida Grande",Arial, Geneva, Verdana, Helvetica, sans-serif black !important;

	}
	</style> 
	<![endif]-->
		
</head>
<body onLoad="onLoadStart()">

<?php
// Was soll getan werden? action=[create]|load|insert|update
$action = getGet('action','');
if( $action == "load" && getGet( 'CustomerID' , 0 ) == 0 )
	$action = create;

if( $auth->checkAuth() == false ) die("\n<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");
if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE ) die("\n<b style=\"color:red;\">Zugriff verweigert.</b>\n</body></html>");

// neues Formular
$form = new HTML_QuickForm('frmCustomer', 'post' , '?action=update' , null , "class=\"formtable\"" , true );

$buttons[] = &HTML_QuickForm::createElement('static', 'BtnDiv' , null , '<span style="float:right;">' );
$buttons[] = &HTML_QuickForm::createElement('reset', 'btnClear', 'Zurücksetzen' , 'class="button"' );

$buttons2[] = &HTML_QuickForm::createElement('static', 'BtnDiv' , null , '<span style="float:right;">' );

// Defaultwert, der aber angepasst wird
$save_button_string = "Speichern";
$display_form = false;
$customerlist = array_nachvorne( array( "0" => "- Kunde -" ) , $allcustomer );


// ### Formular-Generierung ### ---------------------------------------------------------------------------------------

// Gruppe Detail Information
$form->addElement('header', 'CustomerHeader1', '&nbsp;Kundenauswahl');
	$laden[] = &HTML_QuickForm::createElement('select', 'Customers', null , $customerlist, 'class="input_dropdown" onchange="loadCustomer()"');
	// $laden[] = &HTML_QuickForm::createElement('button', 'btnSubmit', "Laden" , 'onClick="loadCustomer()"' );
	$laden[] = &HTML_QuickForm::createElement('button', 'btnSubmit', "Neu" , 'onClick="createCustomer()"' );
	$form->addGroup($laden, 'LoadGroup' , 'Vorhandene Kunden' , '&nbsp;');
$form->addElement('header', 'CustomerHeader2', '&nbsp;Kundendaten');

	$form->addElement('hidden', 'CustomerID');
	
	$form->addElement('text', 'Name', 'Name', 'class="input_long"');
	$form->addElement('hidden', 'Name_prev');
	$form->addElement('text', 'Anschrift', 'Anschrift', 'class="input_long"');
	$form->addElement('hidden', 'Anschrift_prev');
	$form->addElement('text', 'Strasse', 'Straße', 'class="input_long"');
	$form->addElement('hidden', 'Strasse_prev');
	$created[] = &HTML_QuickForm::createElement('text', 'PLZ', 'PLZ', 'class="input_date"');
	$created[] = &HTML_QuickForm::createElement('hidden', 'PLZ_prev');
	$created[] = &HTML_QuickForm::createElement('text', 'Ort', 'Ort', 'class="input_datetime"');
	$created[] = &HTML_QuickForm::createElement('hidden', 'Ort_prev');
	$form->addGroup($created, 'PLZOrt' , 'PLZ / Ort' , '&nbsp;');
	$form->addElement('text', 'Land', 'Land', 'class="input_long"');
	$form->addElement('hidden', 'Land_prev');
	$form->addElement('text', 'Telefon', 'Telefon', 'class="input_long"');
	$form->addElement('hidden', 'Telefon_prev');
	$form->addElement('text', 'Fax', 'Fax', 'class="input_long"');
	$form->addElement('hidden', 'Fax_prev');
	$form->addElement('text', 'EMail', 'EMail', 'class="input_long"');
	$form->addElement('hidden', 'EMail_prev');
	$form->addElement('text', 'Website', 'Website', 'class="input_long"');
	$form->addElement('hidden', 'Website_prev');
	$form->addElement('text', 'Ansprechpartner', 'Ansprechpartner', 'class="input_long"');
	$form->addElement('hidden', 'Ansprechpartner_prev');
	$form->addElement('text', 'Kommentar', 'Kommentar', 'class="input_long"');
	$form->addElement('hidden', 'Kommentar_prev');
	$form->addElement('advcheckbox', 'Active', 'Status' , 'Als Kunden anzeigen' );
	$form->addElement('hidden', 'Active_prev');

// --------------------------------------------------------------------------------------------------------------------

// Regeln für Gültigkeit von Feldern (bitte sparsam!)
function is_unique( $str ) {
	global $allcustomer, $CustomerID;
	$erg = array_search( $str , $allcustomer );
	if( $erg==false || $erg==null || $erg==$CustomerID ) return true;
	else return false;
}
$form->registerRule('unique', 'callback' , 'is_unique' );

$form->applyFilter('Name', 'trim' );
$form->addRule('Name', 'Dieses Feld wird zur Identifikation benötigt.', 'required');
$form->addRule('Name', 'Dieser Name ist bereits in Verwendung.', 'unique');
$autofill = array( 'Active' => "1" );

switch( $action ) {
case 'delete' : // ### Datensatz löschen ------------------------------------------------------------------------------
	DebugMSG("Delete");
	$CustomerID = getGet( 'CustomerID' , 'null' );
	if( $CustomerID > 0 ) {
		$datensatz = SQLQuery( "SELECT ProductionID , ProductionNo , Title FROM Production WHERE CustomerID = '$CustomerID'" );
		if( count($datensatz) > 0 ) {
			$display_message = "Der Kunde konnte nicht gelöscht werden.</b><br>
			<p>Die folgenden Produktionen sind ihm zugeordnet:<br>\n";
			foreach( $datensatz as $prod ) {
				$display_message .= "No. ".$prod['ProductionNo'].", \"".$prod['Title']."\"<br>\n";
			}
			$display_message .= "</p><b>\n";
			// ab hier geht es weiter zu load....!
		} else {
			SQLIQuery( "DELETE FROM Customer WHERE CustomerID = '$CustomerID' LIMIT 1" );
			
			$display_message = "Der Kunde wurde gelöscht.";
			$form->updateAttributes(array('action'=>"?action=insert"));
			$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Kunden erstellen" );
			// Defaults eintragen
			$CustomerID = null;
			loadCustomers();
			// TODO: Kunde ist jetz noch im Auswahlfeld...
			$form->setDefaults($autofill);
			$display_form = true;
			break;
		}
	} else {
		$display_form = false;
		$display_message ="Konnte den geforderten Kunden nicht finden.";
		$CustomerID = null;
	}
case 'insert' : // ### Formular in Datenbank schreiben ----------------------------------------------------------------	
	if( $action == 'insert' ) {
	DebugMSG("Insert");
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$name = $form->process('insert_data', false);
		// Neuen Eintrag hinzufügen

				//$element->addOption( $name , $CustomerID );
		// ab hier geht es weiter zu load....!
	} else {
		$form->updateAttributes(array('action'=>"?action=insert"));
		$display_message = "Fehlerhafte Eingaben. Der Kunde wurde nicht angelegt.";
		$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Kunden erstellen");
		$display_form = true;
		break;
	}
	}
case 'update' : // ### Datensatz aktualisieren ------------------------------------------------------------------------
	if( $action == 'update' ) {
	DebugMSG("Update");
	$CustomerID = $form->getSubmitValue('CustomerID');
	DebugMSG("ID: $CustomerID ");
	if ($form->validate()) { // Sind alle Daten speicherbar?
		$form->process('update_data', false);
		// ab hier geht es weiter zu load....!
	} else {
		$display_message = "Fehlerhafte Eingaben. Die Änderungen wurden noch nicht gespeichert.";
		$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Änderungen Speichern");
		$buttons2[] = &HTML_QuickForm::createElement('button', 'btnDelete', 'Kunden löschen', 'onClick=deleteCustomer()' );
		addDeleteScript( $CustomerID );
		$display_form = true;
		break;
	}
	}
	$group =& $form->getElement( "LoadGroup" );
	$elements =& $group->getElements();
	$element =& $elements[0];
	loadCustomers();
	$customerlist = array_nachvorne( array( "0" => "- Kunde -" ) , $allcustomer );
	$elements[0] = &HTML_QuickForm::createElement('select', 'Customers', null , $customerlist, 'class="input_dropdown" onchange="loadCustomer()"');
	$group->setElements( $elements );

case 'load' : // ### Datensatz laden ----------------------------------------------------------------------------------
	// Werte einsetzen und die fehlenden Formularelemente erstellen
	DebugMSG("Es wird geladen");
	if( !isset($CustomerID)) $CustomerID = getGet( 'CustomerID' , 'null' );
	
	if( !is_numeric($CustomerID) || !load_data( $CustomerID ) ) {
		$display_form = false;
		$display_message ="Konnte den geforderten Kunden nicht finden.";
		$CustomerID = null;
		break;
	}
	
	DebugMSG("ID: $CustomerID ");
	$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Änderungen Speichern");
	$buttons2[] = &HTML_QuickForm::createElement('button', 'btnDelete', 'Kunden löschen', 'onClick=deleteCustomer()' );
	$display_form = true;
	// Defaults eintragen
	$form->setConstants($autofill);
	addDeleteScript( $CustomerID );
	break;
default: // ### Neues Formular anlegen --------------------------------------------------------------------------
	// komplettes Formular mit Defaultwerten erstellen
	DebugMSG("Leeres Formular");
	$form->updateAttributes(array('action'=>"?action=insert"));
	$buttons[] = &HTML_QuickForm::createElement('submit', 'btnSubmit', "Kunden erstellen" );
	// Defaults eintragen
	$form->setDefaults($autofill);
	$display_form = true;
	break;
} // ------------------------------------------------------------------------------------------------------------------

$buttons[] = &HTML_QuickForm::createElement('static', 'BtnEndDiv' , null , '</span>' );
$form->addGroup($buttons, null, null, '&nbsp;');

// Buttons für Reset / Absenden

$buttons2[] = &HTML_QuickForm::createElement('button', 'btnClose', "Fenster Schließen" , "onClick=updateAndClose();" );
$buttons2[] = &HTML_QuickForm::createElement('static', 'BtnEndDiv' , null , '</span>' );
$form->addGroup($buttons2, null, null, '&nbsp;');

function load_data( $CustomerID ) {
	global $autofill;
	$datensatz = SQLLQuery("SELECT * FROM Customer WHERE CustomerID=$CustomerID" );
	if( $datensatz['CustomerID'] != $CustomerID || $CustomerID < 1 )
		return false;
	else {
		$autofill = array_merge( $datensatz , array( 'LoadGroup' => array( 'Customers' => $datensatz['CustomerID'] ),
								'PLZOrt' => array( 'PLZ' => $datensatz['PLZ'] , 'Ort' => $datensatz['Ort'] ,
										'PLZ_prev' => $datensatz['PLZ'] , 'Ort_prev' => $datensatz['Ort'] ) )
								, map_prev($datensatz) );							
	}
	return true;
}

function insert_data( $values ) {
	global $form, $CustomerID;
	DebugMSG("Verarbeiten...");
	
	// Kunden speichern:
	// Diese Daten werden gespeichert:
	
	if( $values['Active'] == "" ) $values['Active'] = "0";
	
	$eintrag_array = array( "Name" => $values['Name'] ,
				"Anschrift" => $values['Anschrift'] ,
				"Strasse" => $values['Strasse'] ,
				"PLZ" => $values['PLZOrt']['PLZ'] ,
				"Ort" => $values['PLZOrt']['Ort'] ,
				"Land" => $values['Land'] ,
				"Telefon" => $values['Telefon'] ,
				"Fax" => $values['Fax'] ,
				"EMail" => $values['EMail'] ,
				"Website" => $values['Website'] ,
				"Ansprechpartner" => $values['Ansprechpartner'] ,
				"Kommentar" => $values['Kommentar'] ,
				"Active" => $values['Active'] );

	// TODO: Prüfen, ob Name bereits existiert.
	SQLLQuery( 'BEGIN' );
	$CustomerID=SQLIQuery( "INSERT INTO Customer ( ".explode_keys($eintrag_array)." ) VALUES ( ".explode_values($eintrag_array)." )" );
	DebugMSG("Neue ID: $CustomerID");
	SQLLQuery( 'COMMIT' );
	return $values[Name];
}

function update_data( $values ) {
	global $CustomerID;
	// prüfe, ob Datensatz bereits existiert
	if( $values['CustomerID'] != "" && sizeof( SQLLQuery("SELECT CustomerID FROM Customer WHERE CustomerID=$values[CustomerID]" )) > 0 && $values['CustomerID'] > 0 ) {
		// Datensatz Update
		DebugMSG("Verarbeiten...");
		
		if( $values['Active'] == "" ) $values['Active'] = "0";
		
		// Diese Daten werden gespeichert:
		$eintrag_array = make_eintrag_array( $values , array( "Name" => $values['Name'] ,
				"Anschrift" => $values['Anschrift'] ,
				"Strasse" => $values['Strasse'] ,
				"Land" => $values['Land'] ,
				"Telefon" => $values['Telefon'] ,
				"Fax" => $values['Fax'] ,
				"EMail" => $values['EMail'] ,
				"Website" => $values['Website'] ,
				"Ansprechpartner" => $values['Ansprechpartner'] ,
				"Kommentar" => $values['Kommentar'] ,
				"Active" => $values['Active'] ));
		
		$eintrag_array = array_merge( $eintrag_array , make_eintrag_array( $values['PLZOrt'] , array(
				"PLZ" => $values['PLZOrt']['PLZ'] ,
				"Ort" => $values['PLZOrt']['Ort'] ) ));
		
		$CustomerID = $values[CustomerID];
		
		SQLLQuery( 'BEGIN' );
		if( count($eintrag_array)>0 ) {
			SQLIQuery( "UPDATE Customer SET ".explode_array($eintrag_array)." WHERE CustomerID = '$CustomerID' LIMIT 1"  );
		}
		SQLLQuery( 'COMMIT' );
		
	} else {
		$display_form = false;
		$display_message ="Konnte den geforderten Kunden nicht finden.";
		$CustomerID = null;
	}
}

function addDeleteScript( $CustomerID ) {
global $allcustomer;
echo <<<END
<script language="JavaScript" type="text/javascript">
<!--
function deleteCustomer() {
	if( confirm("ACHTUNG: Wollen Sie den Kunden \"$allcustomer[$CustomerID]\" wirklich löschen?") ) {
		window.location.href = "customer.php?action=delete&CustomerID=$CustomerID";
	}
}
-->
</script>
END;
}

?>

<div id="formular" style="width:410px;">
<?php
// ### Formular anzeigen ----------------------------------------------------------------------------------------------
if( isset($display_message) ) echo( "<b style=\"color:red;\">$display_message</b>\n" ); 
if( $display_form ) $form->display(); 
// --------------------------------------------------------------------------------------------------------------------
?>
</div>

	<script language="JavaScript" type="text/javascript">
	<!--
	function updateAndClose() {
		if( opener.document.frmProduction ) {
			feld = opener.document.frmProduction.elements["CustomerID"];
			// Auswahlfeld leeren
			while( feld.length > 1 ) {
				feld.options[1] = null;
			}
			// Neu füllen
			<?php 
			foreach( $customer as $key => $val ) {
				if( $key <> 0 ) {
					if( $key == $CustomerID ) $sel = "true"; else $sel = "false";
					echo "		self.opener.fillCustomer( \"$val\", \"$key\", $sel );\n";
				}
			}
			
			if( $CustomerID > 0 && !isset( $customer[$CustomerID] ) ) {
				echo "		self.opener.fillCustomer( \"$allcustomer[$CustomerID] (inaktiv)\", \"$CustomerID\", true );\n";
			}
			?>
		}
		// Fenster schließen
		window.close();
	}
	-->
	</script>

<?php insertFooter();
$mdb2->disconnect(); ?>

</body>
</html>
