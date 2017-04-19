<?php
namespace Cetera\Widget; 

/**
 * Виджет "Html"
 * 
 * @package CeteraCMS
 */ 
class Html extends Widget {
    protected $_params = array(
        'template' => 'Это виджет HTML'
    );
    
    protected function _getHtml()
    {       
        //ob_start();
        //eval('?'.'>'.$this->_params['template']);
       // $result = ob_get_contents();
        //ob_end_clean();
        //return $result;   
        return $this->_params['template'];      
    }
    
}