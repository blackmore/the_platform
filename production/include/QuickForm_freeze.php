<?php
require_once 'HTML/QuickForm/input.php';
require_once 'HTML/QuickForm/button.php';
require_once 'HTML/QuickForm/static.php';
require_once 'HTML/QuickForm/xbutton.php';

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
