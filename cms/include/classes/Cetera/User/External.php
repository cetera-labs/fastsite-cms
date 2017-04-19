<?php
namespace Cetera\User; 

abstract class External extends \Cetera\User {
    
    public function getGroups()
    {
         $g = parent::getGroups();
         $g[] = GROUP_EXTERNAL;
         return $g;
    }
    
    abstract public function getUrl();
    
    abstract public function getSocialCode();
}