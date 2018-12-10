<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Iterator;
 
/**
 * Итератор Пользователей
 *
 * @package CeteraCMS
 **/
class User extends DynamicObject {
	
    /**
     * Включать пользователей из групп
     *         
     * @var array   
     */   	
    protected $groups = [];
     
    /**
     * Конструктор              
     *             
     * @return void  
     */ 
    public function __construct()
    {        
		parent::__construct( new \Cetera\ObjectDefinition( \Cetera\User::TYPE, \Cetera\User::TABLE ) );        
    } 
	
    /**
     * Включать пользователей из группы
     *         
     * @return Cetera\Iterator\User  
     */		
	public function inGroup($gid)
	{
		$this->groups = [$gid];
		return $this;
	}
	
    protected function fixQuery($query)
    {   
		if (count($this->groups)) {
			$query->leftJoin('main', 'users_groups_membership', 'UGM', 'main.id = UGM.user_id');
			$query->where('UGM.group_id IN ('.implode(',',$this->groups).')');
		}
		return parent::fixQuery($query);
    }	
    
}
