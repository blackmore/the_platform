<?php
require_once 'HTML/QuickForm/input.php';
require_once 'HTML/QuickForm/button.php';

class HTML_QuickForm_button_freeze extends HTML_QuickForm_button
{
     function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />';
        }
    } //end func toHtml

    
    function getFrozenHtml()
    {
        return '';
    }
    
    function freeze()
    {
        $this->_flagFrozen = true;
        return true;
    } //end func freeze

    // }}}
 
} //end class HTML_QuickForm_button
?>
