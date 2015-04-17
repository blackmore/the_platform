<?php
require_once 'HTML/QuickForm/element.php';
require_once 'HTML/QuickForm/static.php';

class HTML_QuickForm_static_freeze extends HTML_QuickForm_static {
    
    function toHtml()
    {
        if( !$this->_flagFrozen ) return $this->_getTabs() . $this->_text;
        else return '';
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        return "";
    } //end func getFrozenHtml


} //end class HTML_QuickForm_static
?>
