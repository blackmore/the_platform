<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Tracking</title>
	
	<link rel="stylesheet" href="productions.css" type="text/css" media="all">

	<style type="text/css"> 
	body {
		padding:0 !important;
		margin:0 !important;
		width:494px !important;
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
<body onLoad="onLoadStart()">

<?php

if( $auth->checkAuth() ) {
	// Hole Liste der Produktionen
	// Einstellungen für die Produktionsliste holen
	$value_selectedID = getGET('selectedID', null );
	$value_showListFinished = getAUTH( 'showListFinished' , 0 );
	$value_sort = getAUTH( 'sort' , 'StartDate_Timestamp' );
	if( !in_array($value_sort,array('ProductionNoSort','StartDate_Timestamp','EndDate_Timestamp','Title') ) )
		$value_sort = 'StartDate_Timestamp';
	$value_order = getAUTH('order', 'ASC' );
	$value_showAll = getAUTH('showAll', 1 );
	$value_search = getAUTH('search', null , true );
	$value_link = $_SERVER['PHP_SELF'].'?selectedID='.$value_selectedID.'&';
}

if( $auth->checkAuth() == false ) die("\n<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");

// ### Filter-Interface ### -------------------------------------------------------------------------------------------
?>
<div class="productionlogo"><a href="index.php" target="_top"><img src="images/titelbild.gif" alt="" style="border:none;"></a></div>
<table class="productiontable" cellspacing="0">
<tr class="productionfilter"><td colspan="4"><form name="frmSearch" style="float:left;margin-right:1em;" action="<?php echo $value_link; ?>" method="POST"><input type="text" name="search" value="<?php echo $value_search; ?>"><input type="submit" value="suche"><input type="button" value="reset" onClick="resetSearch()"></form>

<?php
	// ## Hier werden die Rechte und anzeigeoptionen unterschieden:
	if( $auth->getAuthData("GroupID") == $GROUPID_OFFICE || $auth->getAuthData("GroupID") == $GROUPID_DATA ) {
		// ## Spezialfilter Project Office / Data Management
		echo '<div style="float:right;margin-right:1em;"><input name="ProductionList_finished" type="checkbox" value="1"';
			if( $value_showListFinished == 1 ) echo " checked";
			echo ' id="ProductionList_finishedID" onClick="javascript:window.location.href=\''.$value_link.'showListFinished='.(1-$value_showListFinished).';\'"/><label for="ProductionList_finishedID">Nur abgeschlossene Produktionen zeigen</label><br>';
			echo '<input name="ProductionList_all" type="checkbox" value="1"';
			if( $value_showAll == 0 ) echo " checked";
			echo ' id="ProductionList_allID" onClick="javascript:window.location.href=\''.$value_link.'showAll='.(1-$value_showAll).';\'"/><label for="ProductionList_allID">Nur eigene Aufgaben zeigen</label></div></td>';
			echo "</tr>\n".'<tr class="productionheader"><td colspan="5" style="border-bottom:none;">';
		if( $value_showAll == 1 ) {
			echo "Alle anstehenden Aufgaben</td></tr>";
			$time_format='%d.%m.%y';
		}
	} else $value_showAll = 0;
	if( $value_showAll == 0 ) {
		// ## Mitarbeiter oder Spezialfilter aus
		echo '<span style="font-size:12px;font-weight:bold;">Anstehende Aufgaben für '.$allstaff[$auth->getAuthData("StaffID")]."</span></td>";
		$time_format='%d.%m.%y %h:%i';
	}
?>

<tr class="productionheader">
<?php table_header($value_sort,$value_order,$value_link); ?>
</tr>

<?php
// ### Liste der Produktionen anzeigen ### ----------------------------------------------------------------------------
$abfrage_start = "
		SELECT Production.ProductionID, ProductionNo, SUBSTRING(ProductionNo,3) AS ProductionNoSort, CategoryID, CustomerID, Title, Production.StatusID, FunctionID, UNIX_TIMESTAMP(Task.StartDate) AS StartDate_Timestamp, UNIX_TIMESTAMP(Task.EndDate) AS EndDate_Timestamp, DATE_FORMAT(Task.StartDate,'$time_format') AS StartDate, DATE_FORMAT(Task.EndDate,'$time_format') AS EndDate, Task.StatusID AS TaskStatusID, Task.StaffID, Staff.GroupID, TaskID
		FROM Task INNER JOIN Production ON Production.ProductionID = Task.ProductionID INNER JOIN Staff ON Task.StaffID = Staff.StaffID";
if( $auth->getAuthData("GroupID") == $GROUPID_OFFICE || $auth->getAuthData("GroupID") == $GROUPID_DATA ) {
	// Alle Produktionen zeigen
	if( $value_showListFinished == 0 ) $abfrage_filter = $abfrage_start.' WHERE Production.StatusID=1 AND Task.StaffID IS NOT NULL';
	else $abfrage_filter = $abfrage_start.' WHERE Production.StatusID > 1 AND Task.StaffID IS NOT NULL';
	if( $value_showAll == 0 ) $abfrage_filter .= ' AND Task.StaffID = '.$auth->getAuthData("StaffID");
	if( $auth->getAuthData("GroupID") == $GROUPID_DATA ) $abfrage_filter .= ' AND Staff.GroupID='.$GROUPID_DATA;
} else { 
	// Nur sichtbare Produktionen zeigen
	$value_showListFinished = 0;
	$abfrage_filter = $abfrage_start.' WHERE Task.StaffID = '.$auth->getAuthData("StaffID")." AND Production.StatusID < $PRODUCTIONSTATUS_LOCK";
}
// Search String
// TODO: Deadline und Kunden hinzufügen (erst Problem in production_list lösen)
if( $value_search != "") $abfrage_filter .= " HAVING ProductionNo REGEXP '$value_search' OR Title REGEXP '$value_search'"; 

// Sortierung
$abfrage_filter .= " ORDER BY $value_sort $value_order, StartDate_Timestamp $value_order, EndDate_Timestamp $value_order, Task.Position $value_order";
// Abfrage
$productionlist = SQLQuery( $abfrage_filter );

// Liste aufbauen
// geschätzter Abstand der aktuellen Zeile von oben in Pixeln
$deltaY = 0; //105;
unset( $first_deltaY );
foreach( $productionlist as  $production ) {
	$current = "";
	if( $production['ProductionID']==$value_selectedID ) {
		$current =  "productioncurrent";
		if( !isset($first_deltaY) ) $first_deltaY = $deltaY;
	} else $deltaY+=20;
	
	$marker_start = "";
	$marker_end = "";
	
	// Prüfen ob fertig
	if( $production['StatusID']<$PRODUCTIONSTATUS_LOCK ) {
		// Task fertig
		if( $production['TaskStatusID']==3 ) {
			$marker_start = 'style="background:#02e622 !important;color:white !important;"'; // Startdatum Grün
			$marker_end = $marker_start; // Startdatum Grün
		} else {
			// Startdatum überschritten und nicht in Arbeit
			if( $production['TaskStatusID']<2 && $production['StartDate_Timestamp'] != null && $production['StartDate_Timestamp']-mktime() < 0 ) {
				$marker_start = 'style="background:#ffa800 !important;color:white !important;"'; // Startdatum Gelb
			} 
			// Enddatum überschritten und nicht fertig
			if( $production['TaskStatusID']!=3 && $production['EndDate_Timestamp'] != null && $production['EndDate_Timestamp']-mktime() < 0 ) {
				$marker_end = 'style="background:#f70000 !important;color:white !important;"'; // Enddatum Rot
				$marker_start = $marker_end; // Startdatum Rot
			}
		}
		$fertig = "";
	} else {
		if( $production['StatusID']==3 ) $fertig = "&#x2715; ";
		else $fertig = "&#x2714; ";
	}
	if( $value_showAll == 1 )
		$klein = $allstaff[$production['StaffID']];
	else
		$klein = $function_short[$production['FunctionID']];
	
		// Kunde : <td $current >".trimStr($allcustomer[$production['CustomerID']],10)."</td>
	echo "<tr><td class=\"$current\" ><a href=\"tracking.php?action=load&ProductionID=$production[ProductionID]\" target=\"_parent\">$production[ProductionNo]</a> <small>".$klein."</small></td><td class=\"trackingdate $current\" $marker_start>".trimStr($production['StartDate'])."</td><td class=\"trackingdate $current\" $marker_end>".trimStr($production['EndDate'])."</td><td class=\"productiontitle $current\"  ><a href=\"tracking.php?action=load&ProductionID=$production[ProductionID]\" target=\"_parent\">".trimStr($fertig . $production['Title'],25)."</a></td></tr>\n";}
// --------------------------------------------------------------------------------------------------------------------

function table_header( $sort , $order , $url ) {
	
	$spalten = array(	new ProductionListHeader( 'ProductionNoSort' , 'Prod-No.' , $url ) ,
										new ProductionListHeader( 'StartDate_Timestamp' , 'Beginn' , $url ) ,
										new ProductionListHeader( 'EndDate_Timestamp' , 'Abgabe' , $url ) ,
										new ProductionListHeader( 'Title' , 'Produktionstitel' , $url ) );
	
	foreach( $spalten as $spalte ) {
		$spalte->setOrder( $sort , $order );
		echo $spalte->output;
	}

	echo "</tr>\n";
}


?>
</table>

<script language="JavaScript" type="text/javascript">
<!--
	function onLoadStart() {
		<?php $value_scroll = getGET('scroll', 0 );
			echo "		var scroll=$value_scroll;"; ?>
		if( scroll == 0 ) {
			// Focus auf Suchfeld, falls vorhanden
			document.frmSearch.search.focus();
		} else {
			// Scrollen zum aktuellen Eintrag
			window.scrollTo(0,<?php echo $first_deltaY > 350 ? $first_deltaY : 0; ?>);
		}
	}
	function resetSearch() {
		if( document.frmSearch ) {
			feld = document.frmSearch.search;
			feld.value="";
			document.frmSearch.submit();
		}	
	}
-->
</script>

<?php $mdb2->disconnect(); ?>

</body>
</html>
