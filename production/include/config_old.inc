<?php

/*	Titelbild Interface
 *	Jess-Arnold & Partner Consulting
 *
 *	KONFIGURATION
 */

// Ausgabe von Aktionen zur Fehleranalyse
$FORM_DEBUG_ON = false;

// Zugang zur MySQL Datenbank
$MYSQL_DSN = "mysql://titelbild:trackingpw@localhost/titelbild";

$SMB_CONNECT = 'smbclient //10.1.1.240/SMPM \'allegro\' --user=production -R host';

// Achtung: Escape-Backslashes
$SMB_PATH = "Production\\\\Tracking_Sheets\\\\";

// Adresse, unter der das Skript erreichbar ist
$SCRIPT_LOCATION = "http://10.1.1.246/production/";

// GroupID für Project Office-Rechte
//$PRODUCTION_GRP = 4;
$GROUPID_OFFICE = 4;
// GroupID für Data Management
$GROUPID_DATA = 5;
// StaffID für Data Management
$STAFFID_DATA = 59;
// FunctionID für Daten-Tasks (+0 +1)
$FUNCTIONID_DATA = 18;

// StatusID für eine Produktion. >= wird für Eingabe gesperrt (z.B. 2: Abgebrochen / 3: Fertig )
$PRODUCTIONSTATUS_LOCK = 2;

// FunctionID für Hauptaufgabe (aus Datenbank)
$FUNCTIONID_UB = array( 1 , 4 , 14 );

// FunctionID für QA (aus Datenbank)
$FUNCTIONID_QA = array( 2 , 5 );

// ### Hier muss angepasst werden, wenn sich Kategorien ändern! -----------------------------------------------
// ### Aus diesen Elementen werden die Datenbankeinträge generiert!

// Dies sind die FunctionIDs in der Reihenfolge der Abarbeitung
$create_tasks['DVD'] = array( 18, 15, 1, 1, 2, 3, 7, 8, 19, 20 );
$create_tasks['Video'] = array( 18, 15, 1, 1, 2, 3, 7, 8, 19, 20 );
$create_tasks['TV'] = array( 18, 15, 1, 1, 2, 3, 7, 8, 19, 20 );
$create_tasks['Film'] = array( 18, 15, 9, 1, 1, 2, 3, 7, 8, 19, 20 );
$create_tasks['Übersetzung'] = array( 4, 4, 5, 6, 6, 20 );
$create_tasks['Live-UT'] = array( 14, 14, 14, 14, 20 );

// CategoryID -> FunctionID -> array( Checkboxnamen )
$create_checkboxen = array();

$create_checkboxen['DVD'][1] = array( 'Horrors' , 'Bauchbinden' );
$create_checkboxen['DVD'][2] = array( 'Horrors Geprüft' , 'Bauchbinden geprüft' , 'TimeCode Geprüft' , 'TimeCode Abweichungen' );
$create_checkboxen['DVD'][19] = array( 'Ansichts-DVD erstellen' );
$create_checkboxen['Film'][9] = array( 'Eingangskontrolle' );
$create_checkboxen['Film'][1] = array( 'Schnittlisten' , 'Horrors' , 'Bauchbinden' );
$create_checkboxen['Film'][2] = array( 'Horrors Geprüft' , 'Bauchbinden geprüft' , 'TimeCode Geprüft' , 'TimeCode Abweichungen' );
$create_checkboxen['Übersetzung'][5] = array( 'Evaluierungsbogen ausgefüllt' );
$create_checkboxen['Video'][1] = array( 'Horrors' , 'Bauchbinden' );
$create_checkboxen['Video'][2] = array( 'Horrors Geprüft' , 'Bauchbinden geprüft' , 'TimeCode Geprüft' , 'TimeCode Abweichungen' );
$create_checkboxen['Video'][19] = array( 'Ansichts-DVD erstellen' );
// leer: $create_checkboxen['Live-UT']

// Auswahlelemente für die Master/Film Typ Checkbox
$create_mastertype['DVD'] = array( 'MPEG1' , 'MPEG4' , 'VHS' , 'CD-ROM' , 'DVD' , 'BlueRay' , 'HD-DVD' , 'Sonstiges' );
$create_mastertype['Video'] = array( 'DigiBeta' , 'BetaCam SP' , 'HD-CAM' , 'Mini-DV' , 'DV-CAM' , 'Sonstiges' );
$create_mastertype['Film'] = array( '35mm' , 'Sonstiges' );
$create_mastertype['TV'] = array( 'MPEG1' , 'VHS' , 'IPTV' , 'DigiBeta' , 'Sonstiges' );

// Art des Projektes
$create_projecttype['DVD'] = true;
$create_projecttype['Video'] = true;
$create_projecttype['Film'] = true;
$create_projecttype['TV'] = true;
$create_projecttype['values'] = array( 'Spielfilm' , 'Dokumentation' , 'Bonus' , 'Serie' , 'Musik' , 'Kurzfilm' , 'Sonstiges' );

$create_techcontact['DVD'] = true;
$create_techcontact['Video'] = true;
$create_techcontact['Film'] = true;
$create_techcontact['TV'] = true;
$create_techcontact['Live-UT'] = true;

// ### --------------------------------------------------------------------------------------------------------

// Liste aller Möglichen Eigenschaften
// Je zwei Abschnitte Text und Häkchen für das Tracking
// - es werden nur die angezeigt, die festgelegt werden
// bzw. in der DB nicht 'null' sind.
$create_tracking['Alle']['Master']['text'] = array( );
$create_tracking['Alle']['Master']['bool'] = array( 	'VITC' => "VITC" ,
						'MasterTimeCode' => "TC im Bild" );
												
$create_tracking['Alle']['Importieren']['text'] = array( );
$create_tracking['Alle']['Importieren']['bool'] = array( 	'ImportEZTitles' => "EZTitles" ,
							'ImportFAB' => "FAB" ,
							'ImportCopy' => "Arbeitskopie" ,
							'ImportVHS' => "Kunden Ansichts VHS / DVD" );
$create_tracking['Alle']['Material']['text'] = array( 	'AuthoringHouse' => "Authoring House" ,
						'Studio' => "Überspielung Studio" ,
						'Broadcast' => "Sendetermine" ,
						'Filename' => "Dateiname" ,
						'MemPath' => "Best. Memory Pfad" ,
						'NewMemPath' => "Neuer Memory Pfad" ,
						'OrigPath' => "Pfad Original" ,
						'Path' => "Pfad Übersetzung" );
$create_tracking['Alle']['Material']['bool'] = array( 	'MatDialogList' => "Dialogliste" ,
						'MatTranslation' => "Übersetzung der Dialogliste" ,
						'MatGlossary' => "Glossar" ,
						'MatGuidelines' => "Richtlinien" ,
						'MatMisc' => "andere Ressourcen" ,
						'MatFeedback' => "Kunden-Feedback" ,
						'MatWishes' => "Änderungswünsche" );

// Diese Trackingeigenschaften werden für eine DVD angelegt (vergleiche Liste aller mgl. Eigenschaften!)
$create_tracking['DVD']['Master']['text'] = array( );
$create_tracking['DVD']['Master']['bool'] = array( 'VITC' , 'MasterTimeCode' );
$create_tracking['DVD']['Importieren']['text'] = array( );
$create_tracking['DVD']['Importieren']['bool'] = array( 'ImportEZTitles' , 'ImportFAB' , 'ImportCopy' , 'ImportVHS' );
$create_tracking['DVD']['Material']['text'] = array( 'AuthoringHouse' , 'Filename' , 'Path' );
$create_tracking['DVD']['Material']['bool'] = array( 'MatDialogList' , 'MatTranslation' , 'MatGlossary' , 'MatGuidelines' );

// Video
$create_tracking['Video']['Master']['text'] = array( );
$create_tracking['Video']['Master']['bool'] = array( 'VITC' , 'MasterTimeCode' );
$create_tracking['Video']['Importieren']['text'] = array( );
$create_tracking['Video']['Importieren']['bool'] = array( 'ImportEZTitles' , 'ImportFAB' , 'ImportCopy' , 'ImportVHS' );
$create_tracking['Video']['Material']['text'] = array( 'Studio' , 'Filename' , 'Path' );
$create_tracking['Video']['Material']['bool'] = array( 'MatDialogList' , 'MatTranslation' , 'MatGlossary' , 'MatGuidelines');

// TV
$create_tracking['TV']['Master']['text'] = array( );
$create_tracking['TV']['Master']['bool'] = array( 'VITC' , 'MasterTimeCode' );
$create_tracking['TV']['Importieren']['text'] = array( );
$create_tracking['TV']['Importieren']['bool'] = array( 'ImportEZTitles' , 'ImportFAB' , 'ImportCopy' , 'ImportVHS' );
$create_tracking['TV']['Material']['text'] = array( 'Broadcast' , 'Filename' , 'Path' );
$create_tracking['TV']['Material']['bool'] = array( 'MatDialogList' , 'MatTranslation' , 'MatGlossary' , 'MatGuidelines');

// Film
$create_tracking['Film']['Master']['text'] = array( );
$create_tracking['Film']['Master']['bool'] = array( );
$create_tracking['Film']['Importieren']['text'] = array( );
$create_tracking['Film']['Importieren']['bool'] = array( 'ImportEZTitles' , 'ImportFAB' , 'ImportCopy' , 'ImportVHS' );
$create_tracking['Film']['Material']['text'] = array( 'Filename' , 'Path' );
$create_tracking['Film']['Material']['bool'] = array( 'MatDialogList' , 'MatTranslation' , 'MatGlossary' , 'MatGuidelines' );

// Übersetzung
$create_tracking['Übersetzung']['Material']['text'] = array( 'MemPath' , 'NewMemPath' , 'OrigPath' , 'Path' );
$create_tracking['Übersetzung']['Material']['bool'] = array( 'MatGlossary' , 'MatMisc' , 'MatFeedback' , 'MatWishes' );

// Live-UT
$create_tracking['Live-UT']['Material']['text'] = array( );
$create_tracking['Live-UT']['Material']['bool'] = array( );

// ------------------------------------------------------------------------------------------------------------

?>
