// ## NAVIGATIONS-BUTTONS ## ------------------------------------------------------------------------------------------
	// neue Produktion erstellen
	function createProduction( category ) {
		window.location.href = "production.php?action=create&CategoryID="+category;
	}
	// Mitarbeiter
	function gotoStaff() {
		window.location.href = "staff.php";
	}
	// Kunden
	function openCustomers() {
		// Fenster öffnen
		feld = document.frmProduction.elements["CustomerID"];
		index = feld.options[feld.selectedIndex].value;
		window.open( "customer.php?action=load&CustomerID="+index , "customer_window" , "height=590, width=460, top=70,left="+((screen.width/2)-350)+",location=no,menubar=no,resizable=no,status=no,toolbar=no,scrollbars=yes" );
	}
	function gotoProduction() {
		if( document.frmTracking ) {
			feld = document.frmTracking.elements["ProductionID"];
			if( feld.value > 0 ) {
				var id = "?action=load&ProductionID="+feld.value;
			} else {
				var id = "";
			}
		} else {
			var id = "";
		}	
		window.location.href = "production.php"+id;
	}
	function gotoPrint() {
		if( document.frmTracking ) {
			feld = document.frmTracking.elements["ProductionID"];
			if( feld.value > 0 ) {
				var id = "?ProductionID="+feld.value;
			} else {
				var id = "";
			}
		} else {
			var id = "";
		}	
		window.location.href = "tracking_print.php"+id;
	}
	// Tracking Sheet
	function gotoTracking() {
		if( document.frmProduction ) {
			feld = document.frmProduction.elements["ProductionID"];
			if( feld.value > 0 ) {
				var id = "?action=load&ProductionID="+feld.value;
			} else {
				var id = "";
			}
		} else {
			var id = "";
		}	
		window.location.href = "tracking.php"+id;
	}
// ## FORMULAR-FUNKTIONEN ## ------------------------------------------------------------------------------------------
	// Nachdem die Seite geladen wurde
	function onLoadStart() {
		// Focus auf Loginfeld, falls vorhanden
		if(document.frmLogin) {
			if(document.frmLogin.username) {
				document.frmLogin.username.focus();
			}
		}
		// Mitarbeiterstärken ausrechnen
		if(document.frmProduction) {
			markStaff();
		}
	}
	
	function scrollUp() {
		window.scrollTo(0,0);
	}
	
	function scrollProductionUp() {	
		ProductionList.scrollTo(0,0);
	}
	
	function askSave() {
		//return confirm("Das Formular wurde verändert.\nKlicken Sie auf OK, um Ihre Änderungen jetzt zu speichern.");
	}
	
	function createVariant() {
		if( document.frmProduction ) {
			feld1a = document.frmProduction.elements["ProductionNoGrp[ProductionNo1]"];
			feld1b = document.frmProduction.elements["ProductionNoGrp[ProductionNo2]"];
			feld2 = document.frmProduction.elements["ProductionNo_prev"];
			feld_date = document.frmProduction.elements["CreatedDate"];
			feld_by = document.frmProduction.elements["CreatedByID"];
			feld_staff = document.frmProduction.elements["CurrentStaffID"];
			while( feld1a.value+feld1b.value == feld2.value ) {
				// Werte verändern vor dem Absenden		
				neuenr = prompt( "Bitte neue Produktions-Nr. angeben:\nleeres Feld: automatisch" , feld1a.value+feld1b.value );
				if( neuenr == undefined ) return 0;
				if( neuenr == "" ) feld1a.value = "wird angelegt";
				else {
					feld1a.value = neuenr.substring(0,11);
					feld1b.value = neuenr.substring(11);
				}
			}
			heute = new Date();
			feld_date.value = formatDate(heute,"dd.MM.yy HH:mm");
			feld_by.value = feld_staff.value;
			document.frmProduction.action = "?action=insert";
			document.frmProduction.submit();
			return 1;
		}
	}
	
	
	// Kundenfeld neu laden
	function fillCustomer( nam , nummer , sel ) {
		feld = document.frmProduction.elements["CustomerID"];
		// Neu füllen
		neu = new Option( nam , nummer , false , sel );
		feld.options[feld.length] = neu;
	}
	// Produktion als Abgeschlossen markieren
	function markProductionFinished() {
		feld = document.frmProduction.elements["StatusID"];
		feld.selectedIndex = 1;
		document.forms['frmProduction'].submit();
	}
	function markTaskFinished( pos ) {
		feld = document.frmTracking.elements["TaskGroup["+pos+"][StatusID]"];
		feld.selectedIndex = 2;
		document.forms['frmTracking'].submit();
	}
	// Berechne Zeitdauer
	function staffduration( factorType , functionID , staffID , oldValue ) {
		if( oldValue!="" ) return oldValue;
		if( staffID==0 ) return "";
		
		feld = document.frmProduction.elements["Duration"];
		if( feld.value=="" ) return "";
		// 8-Stunden-Tag angenommen
		if( stafftimefactor[staffID] && stafftimefactor[staffID][functionID] ) {
			dauer = (8*feld.value) / ( functiontimefactor[functionID][factorType][stafftimefactor[staffID][functionID]] );
		} else {
			dauer = (8*feld.value) / ( functiontimefactor[functionID][factorType][0] );
		}
		if( isNaN(dauer) ) return "";
		
		var std = parseInt(dauer);
		var min = round((dauer-std)*60);

		// Auf 30 Minuten runden
		if( min <= 5 ) min = "00";
		else if( min > 5 && min <= 40 ) min = "30";
		else if( min > 40 ) {
			min = "00";
			std++;
		}
		if( std < 10 ) std = "0"+std;
		return std+":"+min;
	}
	// Hilfsfunktion
	function round( zahl ) {
		var kurz = parseInt(zahl);
		if( (zahl-kurz) >= 0.5 ) return kurz+1;
		else return kurz;
	}
	
	//var_win = window.open( 	"calendar.php" , "calendar_win" , "height=250, width=200, top=50, left="+((screen.width/2)-180)+",location=no,menubar=no,resizable=auto,status=no,toolbar=no,scrollbars=no" );
	// Kalender öffnen
	function openCalendar( field , return_time ) {
    dateField = document.frmProduction.elements[field];
    curDate = parseDate(dateField.value);
    if( curDate ) curDate = Date.parse(curDate)/1000;
    else curDate = "";
    window.open("calendar.php?time="+return_time+"&date="+curDate, "calendar_win", "width=200,height=250,status=yes");
    
    //dateType = type;
	}
	
	function changeDate( text , time ) {
		parsed_date = parseDate(text);
		if( parsed_date && time==1 ) return formatDate(parsed_date,"dd.MM.yy HH:mm");
		else if( parsed_date && time==0 ) return formatDate(parsed_date,"dd.MM.yyyy");
		else return text;
	}
	


	