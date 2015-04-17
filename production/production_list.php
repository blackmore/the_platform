<?php require('include/titelbild_functions.php'); 
header('content-type: text/html; charset=utf-8');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>TITELBILD Produktionen</title>
	
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
	$value_sort = getAUTH( 'sort' , 'Deadline_Timestamp' );
	if( !in_array($value_sort,array('ProductionNoSort','Deadline_Timestamp','CustomerName','Title') ) )
		$value_sort = 'Deadline_Timestamp';
	$value_order = getAUTH('order', 'ASC' );
	$value_search = getAUTH('search', null , true );
	
	$value_link = $_SERVER['PHP_SELF'].'?selectedID='.$value_selectedID.'&';
}

if( $auth->checkAuth() == false ) die("\n<b style=\"color:red;\">Bitte einloggen.</b>\n</body></html>");
if( $auth->getAuthData("GroupID") != $GROUPID_OFFICE ) die("\n<b style=\"color:red;\">Zugriff verweigert.</b>\n</body></html>");

// ### Filter-Interface ### -------------------------------------------------------------------------------------------
?>
<div class="productionlogo"><a href="index.php" target="_top"><img src="images/titelbild.gif" alt="" style="border:none;"></a></div>
<table class="productiontable" cellspacing="0">
<tr class="productionfilter"><td colspan="4"><form name="frmSearch" style="float:left;margin-right:1em;" action="<?php echo $value_link; ?>" method="POST"><input type="text" name="search" value="<?php echo $value_search; ?>"><input type="submit" value="suche"><input type="button" value="reset" onClick="resetSearch()"></form>
		<input name="ProductionList_finished" type="checkbox" value="1" <?php if( $value_showListFinished == 1 ) echo "checked"; ?> id="ProductionList_finishedID" onClick="javascript:window.location.href='<?php echo $value_link.'showListFinished='.(1-$value_showListFinished); ?>'"/>
		<label for="ProductionList_finishedID">Nur abgeschlossene Produktionen zeigen</label></td>
</tr>

<tr class="productionheader">
<?php table_header($value_sort,$value_order,$value_link); ?>
</tr>

<?php
// ### Liste der Produktionen anzeigen ### ----------------------------------------------------------------------------
$abfrage_start = "SELECT ProductionID, ProductionNo, SUBSTRING(ProductionNo,2) AS ProductionNoSort, CategoryID, Production.CustomerID, Customer.Name AS CustomerName, Title, StatusID, UNIX_TIMESTAMP(Deadline) AS Deadline_Timestamp, DATE_FORMAT(Deadline,'%d.%m.%Y') AS Deadline FROM Production LEFT JOIN Customer ON Production.CustomerID = Customer.CustomerID";

// Abgeschlossene Produktionen
if( $value_showListFinished == 0 ) $abfrage_filter = $abfrage_start.' WHERE Production.StatusID=1';
else $abfrage_filter = $abfrage_start.' WHERE Production.StatusID > 1';
// Search String
if( $value_search != "") $abfrage_filter .= " HAVING ProductionNo REGEXP '$value_search' OR CustomerName REGEXP '$value_search' OR Title REGEXP '$value_search' OR Deadline REGEXP '$value_search'"; 
// Sortierung
$abfrage_filter .= " ORDER BY $value_sort $value_order";
// Abfrage
$productionlist = SQLAQuery( $abfrage_filter );

// Produktion ausgewählt, die nicht im Filter gefunden wurde
/*if( isset($value_selectedID) && !isset($productionlist[$value_selectedID]) ) {
	$datensatz = SQLAQuery( $abfrage_start." WHERE ProductionID = $value_selectedID" );
	if( isset( $datensatz[$value_selectedID] ) )
		$productionlist = array_nachvorne( array( $value_selectedID => $datensatz[$value_selectedID] ) , $productionlist );
}*/

// Liste aufbauen
// geschätzter Abstand der aktuellen Zeile von oben in Pixeln
$deltaY = 0; //105;
unset( $first_deltaY );
foreach( $productionlist as $production_id => $production ) {
	$current = "";
	if( $production_id==$value_selectedID ) {
		$current =  "productioncurrent";
		if( !isset($first_deltaY) ) $first_deltaY = $deltaY;
	} else $deltaY+=20;
	
	// Ampel neu setzen für Produktionsliste
	setAmpel("gruen",true);
	// Prüfen ob fertig
	if( $production['StatusID']<$PRODUCTIONSTATUS_LOCK ) {
		// Prüfen auf Deadline
		checkProductionDates( $production['Deadline_Timestamp'] , false );
		$tasks = SQLQuery("SELECT StaffID, UNIX_TIMESTAMP(StartDate) AS StartDate_Timestamp, UNIX_TIMESTAMP(EndDate) AS EndDate_Timestamp, StatusID FROM Task WHERE ProductionID=".$production_id );
		unset($alleFertig);
		//$tasksVerwendet = false;
		foreach( $tasks as $task ) {
			if( $task['StaffID'] > 0 ) {
				checkTaskDates( $task , $production['Deadline_Timestamp'] , 0 , false );
				if( $task['StatusID']!=3 ) $alleFertig = false;
				else if( !isset($alleFertig) ) $alleFertig = true;
			} 
		}
		$fertig = "";
	} else {
		if( $production['StatusID']==3 ) $fertig = "&#x2715; ";
		else $fertig = "&#x2714; ";
		$alleFertig = false;
	}
	
	if( $ampel=="rot" ) $marker = "style=\"border-left:5px solid #f70000;\"";
	else if( $ampel=="gelb" ) $marker = "style=\"border-left:5px solid #ffa800;\"";
	else if( $alleFertig === true ) $marker = "style=\"border-left:5px solid #02e622;\"";
	else $marker = "style=\"padding-left:9px;\""; //#02e622
	//$allcustomer[$production['CustomerID']]
	echo "<tr><td class=\"$current\" $marker><a href=\"production.php?action=load&ProductionID=$production_id\" target=\"_parent\">$production[ProductionNo]</a></td><td class=\"$current\" >".trimStr($production['CustomerName'],10)."</td><td class=\"$current\" >".trimStr($production['Deadline'])."</td><td class=\"productiontitle $current\" ><a href=\"production.php?action=load&ProductionID=$production_id\" target=\"_parent\">".trimStr($fertig . $production[Title],43)."</a></td></tr>\n";
}
// --------------------------------------------------------------------------------------------------------------------

function table_header( $sort , $order , $url ) {
	
	$spalten = array(	new ProductionListHeader( 'ProductionNoSort' , 'Prod-No.' , $url , 'width:76px;' ) ,
										new ProductionListHeader( 'CustomerName' , 'Kunde' , $url , 'width:86px;' ) ,
										new ProductionListHeader( 'Deadline_Timestamp' , 'Deadline' , $url , 'width:63px;' ) ,
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
