<?php
namespace Cetera\User; 

abstract class External extends \Cetera\User {
    
	protected $external_id = null;
	
    public function getGroups()
    {
         $g = parent::getGroups();
         $g[] = GROUP_EXTERNAL;
         return $g;
    }
    
    abstract public function getUrl();
    
    abstract public function getSocialCode();
	
    public static function fetch($data, $type = 0, $table = null)
    {
		$u = parent::fetch($data, $type, $table);
		if (isset($data['external_id'])) {
			$u->external_id = $data['external_id'];
		}		
		return $u;
	}	
}