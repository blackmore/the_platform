<html>
<head>
</head>
<body>

	<?php 
	$mail_to="ertugrul.oeztuerk@titelbild.de"; 

	$from_name=$_POST['fromname']; 
	$from_mail=strtolower($_POST['frommail']); 
	$mail_subject=$_POST['mailsubject']; 
	$mail_text=$_POST['mailtext']; 
	$chk_newsletter=$_POST['newsletter']; 
	$send=$_POST['s']; 

	if(trim($from_name)=="") $err_text.="Bitte gib Deinen Namen an.<br>"; 
	if(trim($from_mail)=="") 
	  $err_text.="Bitte gib Deine E-Mail-Adresse an.<br>"; 
	if(trim($mail_subject)=="") $err_text.="Bitte gib einen Betreff ein.<br>"; 
	if(trim($mail_text)=="") $err_text.="Bitte gib einen Nachrichtentext ein.<br>"; 
	if(strlen($mail_text)>1000) { 
	  $mail_text=substr($mail_text,0,1000)."... (Text wurde gekürzt!)"; 
	} 
	$from_name=str_replace(chr(34),"''",$from_name); 
	$mail_subject=str_replace(chr(34),"''",$mail_subject); 
	$from_name=stripslashes($from_name); 
	$from_mail=stripslashes($from_mail); 
	$mail_subject=stripslashes($mail_subject); 
	$mail_text=stripslashes($mail_text); 

	if(($send=="1") && (isset($err_text))) { 
	  echo "<p><b>Fehler:</b><br>"; 
	  echo "$err_text</p>"; 
	} else {

	?>
	
	
	<?php
	}
	
	if(($send!="1") || (isset($err_text))) 
	{ 
	?>
	
	<form action="mailtest.php" method="post"> 
	Name:<br>
	<input type="text" name="fromname" size=50 maxlength=120 value="<?php echo $from_name; ?>" class="feld" style="width:235px;"><br>
	eMail Adresse:<br>
	<input type="text" name="frommail" size=50 maxlength=120 value="<?php echo $from_mail; ?>" class="feld" style="width:235px;"><br>
	Betreff:<br>
	<input type="text" name="mailsubject" size=50 maxlength=120 value="<?php echo $mail_subject; ?>" class="feld" style="width:235px;"><br>
	Text:<br>
	<textarea cols="40" rows="8" name="mailtext" class="feld"><?php echo $mail_text; ?></textarea><br><br>
	<input type="hidden" value="1" name="s"> 
	<input type="submit" value="E-Mail senden" name="submit">
	</form>

	<?php 
	} else { 
		
	  $header="From: $from_name <$from_mail>\n"; 
	  $header.="Reply-To: $from_mail\n"; 
	  $header.="X-Mailer: Titelbild-Formular\n"; 
	  $header.="Content-Type: text/plain"; 
	  $mail_date=gmdate("D, d M Y H:i:s")." GMT"; 
	  $send=0; 
	  if(mail($mail_to,$mail_subject,"> Gesendet über Titelbild-Formular:\n".$mail_text,$header)) 
	  { 
	    echo "<p><b>Deine E-Mail wurde abgesendet.</b></p>"; 
	    echo "<p><a href=\"kontakt.php?from_name=$from_name&from_mail=$from_mail\">Zurück zum Formular</a></p>"; 
	  }else{ 
	    echo "<p><b>Beim Versenden der E-Mail ist ein Fehler aufgetreten!</b></p>"; 
	    echo "<p><a href=\"kontakt.php?from_name=$from_name&from_mail=$from_mail&mail_subject=$mail_subject&mail_text="; 
	    echo urlencode($mail_text)."\">Zurück zum Formular</a></p>"; 
	  } 
	} 
	?>
	
</body>
</html>
