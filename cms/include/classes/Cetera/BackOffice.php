<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Класс, реализующий логику работы BackOffice части FastsiteCMS
 *
 * @package FastsiteCMS
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
	
	private $events = [];
    
	/** @internal */
    public function __construct($a)
    {
        $this->application = $a;
		
		$t = $this->application->getTranslator();
        
		$this->registerEvent(
			EVENT_CORE_USER_REGISTER,
			$t->_('Зарегистрирован пользователь'),
			[
				'user.id' => $t->_('ID пользователя'),
				'user.login' => $t->_('Логин пользователя'),
				'user.email' => $t->_('E-mail пользователя'),
				'user.name' => $t->_('Имя пользователя'),
				'password' => $t->_('Пароль'),
				'server.fullUrl' => $t->_('Адрес сайта'),
				'server.name'    => $t->_('Имя сайта')					
			]
		);
		
		$this->registerEvent(
			EVENT_CORE_USER_RECOVER,
			$t->_('Сброс пароля пользователя'),
			[
				'user.id' => $t->_('ID пользователя'),
				'user.login' => $t->_('Логин пользователя'),
				'user.email' => $t->_('E-mail пользователя'),
				'user.name' => $t->_('Имя пользователя'),
				'password' => $t->_('Пароль'),
				'server.fullUrl' => $t->_('Адрес сайта'),
				'server.name'    => $t->_('Имя сайта')					
			]
		);

		$this->registerEvent(EVENT_CORE_USER_DELETE, $t->_('Удален пользователь'));        
		$this->registerEvent(EVENT_CORE_BO_LOGIN_OK,   $t->_('Вход в систему'));
		$this->registerEvent(EVENT_CORE_BO_LOGIN_FAIL, $t->_('Попытка входа в систему'));
		$this->registerEvent(EVENT_CORE_LOG_CLEAR,     $t->_('Очищен журнал аудита'));
		$this->registerEvent(EVENT_CORE_DIR_CREATE    , $t->_('Создан раздел'));
		$this->registerEvent(EVENT_CORE_DIR_EDIT      , $t->_('Изменен раздел'));
		$this->registerEvent(EVENT_CORE_DIR_DELETE    , $t->_('Удален раздел'));
		$this->registerEvent(EVENT_CORE_MATH_CREATE   , $t->_('Создан материал'));
		$this->registerEvent(EVENT_CORE_MATH_EDIT     , $t->_('Изменен материал'));
		$this->registerEvent(EVENT_CORE_MATH_DELETE   , $t->_('Удален материал'));
		$this->registerEvent(EVENT_CORE_MATH_PUB      , $t->_('Опубликован материал'));
		$this->registerEvent(EVENT_CORE_MATH_UNPUB    , $t->_('Раcпубликован материал'));
		$this->registerEvent(EVENT_CORE_USER_PROP     , $t->_('Изменен пользователь'));		
			
    }
	
	public function registerEvent($id,$name = null,$parameters = null)
	{
		if (isset($this->events[$id])) throw new \Exception('Событие "'.$id.'" уже зарегистрировано');
		$this->events[$id] = array(
			'id'         => $id,
			'name'       => $name,
			'parameters' => $parameters,
		);
	}
	
	public function getEventById($id)
	{
		return $this->events[$id];
	}	

	public function getRegisteredEvents() {
		return array_values($this->events);
	}

    public function addModule($menu, $parent = null)
    {
        if (!isset($menu['id'])) return;
        if (!isset($menu['path'])) $menu['path'] = $this->application->getCallerPath();
		if ($parent && isset($this->_user_modules[$parent])) {
			$this->_user_modules[$parent]['submenu'][] = $menu;
		}
		else {
			$this->_user_modules[$menu['id']] = $menu;
		}
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
            $this->_modules = [
                'structure' => [
                	  'position'  => MENU_SITE,
                      'name' 	  => $translator->_('Структура и материалы'),
					  'iconCls'   => 'x-fa fa-folder',
                      'class'     => 'Cetera.panel.Structure'
                ],			
                'materials' => [
                	  'position'  => MENU_SITE,
                      'name' 	  => $translator->_('Материалы'),
                	  'icon'      => 'images/math2.gif',
					  'iconCls'   => 'x-fa fa-file-alt',
                      'class'     => 'Cetera.panel.MaterialsByCatalog'
                ]
            ];          
            
            if ($this->application->getUser() && $this->application->getUser()->allowAdmin()) {
        /*
                $this->_modules['widgets'] = array(
                	  'position' => MENU_SITE,
                      'name' 	 => $translator->_('Шаблоны виджетов'),
                	  'icon'     => 'images/widget_icon.png',
					  'iconCls'  => 'x-fa fa-file-code',
                	  'class'    => 'Cetera.widget.templates.Panel',
					  'ext6_compat'=> true
                );
		*/
        /*
                $this->_modules['widget_areas'] = array(
                	  'position' => MENU_SITE,
                      'name' 	 => $translator->_('Области'),
                	  'icon'     => 'images/widget_icon.png',
					  'iconCls'  => 'x-fa fa-cog',
                	  'class'    => 'Cetera.widget.Panel'
                ); 
        */        
                $this->_modules['menus'] = array(
                	  'position' => MENU_SITE,
                      'name' 	   => $translator->_('Меню'),
                	  'icon'     => 'images/icon_menu.png',
					  'iconCls'  => 'x-fa fa-bars',
                	  'class'    => 'Cetera.panel.Menu'
                );   

                $this->_modules['setup'] = array(
                			'position' => MENU_SERVICE,
                            'name' 	   => $translator->_('Настройки'),
                			'icon'     => 'images/setup_small.gif',
							'iconCls'  => 'x-fa fa-cog',
                			'class'    => 'Cetera.panel.Setup'
                );				
            
                $this->_modules['types'] = array(
                			'position' => MENU_SERVICE,
                            'name' 	   => $translator->_('Типы материалов'),
                			'icon'     => 'images/setup_small.gif',
							'iconCls'  => 'x-fa fa-firstdraft',
                			'class'    => 'Cetera.panel.MaterialTypes'
                );
                
                $this->_modules['users'] = array(
                			'position' => MENU_SERVICE,
                            'name' 	   => $translator->_('Пользователи'),
                			'icon'     => 'images/user.gif',
							'iconCls'  => 'x-fa fa-user',
                			'class'    => 'Cetera.users.MainPanel'
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
					'icon'     => 'images/audit1.gif',
					'class'    => 'EventlogPanel',
					'iconCls'  => 'x-fa fa-file',
					'class'    => 'Cetera.eventlog.Panel',
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
					'name' 	   => $translator->_('Установленные модули'),
					'icon'     => 'images/plugin.png',
					'class'    => 'Cetera.plugin.List',
					'iconCls'  => 'x-fa fa-plug'
                );
                
                $this->_modules['themes'] = array(
					'position' => MENU_SERVICE,
					'name' 	   => $translator->_('Темы'),
					'icon'     => 'images/16X16/gallery.gif',
					'iconCls'  => 'x-fa fa-leaf',
					'class'    => 'Cetera.theme.List',
                );                
                 
            }           
        
        }
        
        return $this->_modules + $this->_user_modules;
    }    

}