<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Класс, реализующий логику работы BackOffice части CeteraCMS
 *
 * @package CeteraCMS
 **/ 
class BackOffice {

	/** @internal */
    private $_modules = false;
	/** @internal */
    private $_user_modules = array();
	/** @internal */
    private $_scripts = array();
	/** @internal */
    private $application = null;
    
	/** @internal */
    public function __construct($a)
    {
        $this->application = $a;
    }

    public function addModule($menu)
    {
        if (!isset($menu['id'])) return;
        if (!isset($menu['path'])) $menu['path'] = $this->application->getCallerPath();  
        $this->_user_modules[$menu['id']] = $menu;
    }
    
    public function addScript($script, $calcPath = true)
    {
        if ($calcPath) $script = $this->application->getCallerPath().'/'.$script;
        $this->_scripts[] = $script;
    } 
    
    public function addEditor($config)
    {
        global $editors, $field_editors, $l_editors;
        
        $editors[$config['id']]   = $config['alias'];
        $l_editors[$config['id']] = $config['name'];
    }   
    
    public function addPseudoField($config)
    {
        global $l_field_types, $field_editors, $pseudo_to_original;
        
        $l_field_types[$config['id']] = $config['name'];
        
        $pseudo_to_original[$config['id']] = array(
            'original' => $config['original']     
        );
        
        if (isset( $config['len'] )) {
             $pseudo_to_original[$config['id']]['len'] = $config['len'];
        }
        
        if (isset($config['editors']) && is_array($config['editors'])) {
            $field_editors[$config['id']] = $config['editors'];
        } else {
            $field_editors[$config['id']] = array();
        }

    }
    
    public function addFieldEditor($field, $editor)
    {
        global $field_editors;
        
        $field_editors[$field][] = $editor;
    }         
        
    public function getScripts()
    {
        return $this->_scripts;
    }
    
    /*
    * Возвращает модули
    * 
    * @return array
    */  
    public function getModules()
    {
        if (!$this->_modules) {
        
            $translator = $this->application->getTranslator();
            $this->_modules = array(
                'materials' => array(
                	  'position'  => MENU_SITE,
                      'name' 	  => $translator->_('Материалы'),
                	  'icon'      => 'images/math2.gif',
					  'iconCls'   => 'x-fa fa-file-text',
                      'class'     => 'Cetera.panel.MaterialsByCatalog'
                )
            );          
            
            if ($this->application->getUser() && $this->application->getUser()->allowAdmin()) {
        
                $this->_modules['widgets'] = array(
                	  'position' => MENU_SITE,
                      'name' 	 => $translator->_('Шаблоны виджетов'),
                	  'icon'     => 'images/widget_icon.png',
					  'iconCls'  => 'x-fa fa-file-code-o',
                	  'class'    => 'Cetera.widget.templates.Panel',
					  'ext6_compat'=> true
                );
		
                $this->_modules['widget_areas'] = array(
                	  'position' => MENU_SITE,
                      'name' 	 => $translator->_('Области'),
                	  'icon'     => 'images/widget_icon.png',
					  'iconCls'  => 'x-fa fa-cog',
                	  'class'    => 'Cetera.widget.Panel'
                ); 
                
                $this->_modules['menus'] = array(
                	  'position' => MENU_SITE,
                      'name' 	   => $translator->_('Меню'),
                	  'icon'     => 'images/icon_menu.png',
					  'iconCls'  => 'x-fa fa-bars',
                	  'class'    => 'Cetera.panel.Menu'
                );            
            
                $this->_modules['types'] = array(
                			'position' => MENU_SERVICE,
                            'name' 	   => $translator->_('Типы материалов'),
                			'icon'     => 'images/setup_small.gif',
							'iconCls'  => 'x-fa fa-cogs',
                			'class'    => 'Cetera.panel.MaterialTypes'
                );
                
                $this->_modules['users'] = array(
                			'position' => MENU_SERVICE,
                      'name' 	   => $translator->_('Пользователи'),
                      'url'      => 'include/ui_users.php',
                			'icon'     => 'images/user.gif',
							'iconCls'  => 'x-fa fa-user',
                			'class'    => 'UsersPanel'
                );
                
                $this->_modules['groups'] = array(
                			'position' => MENU_SERVICE,
                      'name' 	   => $translator->_('Группы пользователей'),
                      'url'      => 'include/ui_groups.php',
                			'icon'     => 'images/users.gif',
							'iconCls'  => 'x-fa fa-users',
                			'class'    => 'GroupsPanel'
                );
                
                $this->_modules['eventlog'] = array(
					'position' => MENU_SERVICE,
					'name' 	   => $translator->_('Журнал'),
					'url'      => 'include/ui_eventlog.php',
					'icon'     => 'images/audit1.gif',
					'class'    => 'EventlogPanel',
					'iconCls'  => 'x-fa fa-file-text-o',
                ); 
                
                $this->_modules['dbrepair'] = array(
					'position' => MENU_SERVICE,
					'name' 	   => $translator->_('Проверка и ремонт БД'),
					'icon'     => 'images/icon_repair.gif',
					'iconCls'  => 'x-fa fa-medkit',
					'class'    => 'Cetera.panel.Repair',
					'ext6_compat'=> true
                );
				
                $this->_modules['mail_templates'] = array(
					'position' => MENU_SERVICE,
					'name' 	   => $translator->_('Почтовые шаблоны'),
					'icon'     => 'images/mail.gif',
					'iconCls'  => 'x-fa fa-envelope',
					'class'    => 'Cetera.grid.MailTemplates',
                );				
                
                $this->_modules['plugins'] = array(
					'position' => MENU_SERVICE,
					'name' 	   => $translator->_('Плагины'),
					'icon'     => 'images/plugin.png',
					'class'    => 'Cetera.plugin.List',
					'iconCls'  => 'x-fa fa-plug',
					'submenu'  => array(
						array(
							'name'  => $translator->_('Добавить плагин'),
							'icon'  => 'images/16X16/pack.gif',
							'class' => 'Cetera.plugin.Add'
						)
					) 
                );
                
                $this->_modules['themes'] = array(
                      		'position' => MENU_SERVICE,
                          	'name' 	   => $translator->_('Темы'),
                          	'icon'     => 'images/16X16/gallery.gif',
							'iconCls'  => 'x-fa fa-picture-o',
                            'class'    => 'Cetera.theme.List',
                            'submenu'  => array(
                                array(
                                    'name'  => $translator->_('Установить тему'),
                                    'icon'  => 'images/image_add.png',
                                    'class' => 'Cetera.theme.Add'
                                )
                            ) 
                );                
                 
            }           
        
        }
        
        return $this->_modules + $this->_user_modules;
    }    

}