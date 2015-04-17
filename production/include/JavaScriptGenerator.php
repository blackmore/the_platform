<?php
class JavaScriptGenerator {
	
	var $_lines = array();
	var $_endlines = array(); // werden am Ende rückwärts ausgegeben
	var $_tabs = 0;
	
	function JavaScriptGenerator( $function_header = null ) {
		if( isset($function_header) ) {
			$this->addLine('function '.$function_header.' {');
			$this->addEndLine('}');
			$this->_tabs++;
		}
	}
	
	function addLine( $text ) {
		$this->_lines[] = str_repeat( "\t" , $this->_tabs ).$text;
	}
	
	function addLineTab( $text ) {
		$this->addLine($text);
		$this->_tabs++;
	}
	
	function addLineTabEnd( $text ) {
		$this->_tabs--;
		$this->addLine($text);
	}
	
	function addEndLine( $text ) {
		$this->_endlines[] = $text;
	}
	
	function echoHtml() {
		echo '<script language="JavaScript" type="text/javascript">'."\n<!--\n";
		foreach( array_merge($this->_lines,array_reverse($this->_endlines)) as $line ) {
			echo $line."\n";
		}
		echo "-->\n</script>";
	}
	
}

?>
