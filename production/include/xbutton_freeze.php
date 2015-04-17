<?php
/**
 * Base class for form elements
 */ 
require_once 'HTML/QuickForm/element.php';
require_once 'HTML/QuickForm/xbutton.php';

class HTML_QuickForm_xbutton_freeze extends HTML_QuickForm_xbutton
{
    function toHtml()
    {
        if( !$this->_flagFrozen ) return '<button' . $this->getAttributes(true) . '>' . $this->_content . '</button>';
        else return '';
    }
    
    function getFrozenHtml()
    {
        return '';
    }
    
    /**
     * Freeze the element so that only its value is returned
     *
     * @access    public
     * @return    void
     */
    function freeze()
    {
        $this->_flagFrozen = true;
        return true;
    } //end func freeze

}
?>
