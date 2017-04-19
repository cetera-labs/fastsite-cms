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
     * Конструктор              
     *  
           
     * @return void  
     */ 
    public function __construct()
    {
        
		parent::__construct( new \Cetera\ObjectDefinition( \Cetera\User::TYPE, \Cetera\User::TABLE ) );
        
    } 
    
}
