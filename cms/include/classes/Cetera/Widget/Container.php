<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera\Widget; 

/**
 * Виджет "Контейнер"
 * 
 * Предназначен для хранения в себе других виджетов в заданном порядке
 *
 * @package FastsiteCMS
 */ 
class Container extends Templateable {

	/**
	 * Виджеты, хранящиеся в контейнере
	 */
    protected $widgets = [];
	
	public static $name = 'Container';
	
    protected $_params = array(
		'template'    => 'default.twig',
    );

	public function enum()
	{
		$res = array();
		$r = self::getDbConnection()->executeQuery( 'SELECT * FROM widgets WHERE widgetName=?', array("Container") );	
		while($f = $r->fetch()) {
			$res[] = new self($f['id'], $f);			
		}
		return $res;
	}

	/**
	 * Конструктор
	 * 
	 * @param int $id ID виджета
	 */	
    public function __construct($id = 0, $params = null)
    {
		if (isset($params['alias'])) {
            $f = self::getDbConnection()->fetchAssoc('SELECT * FROM widgets WHERE widgetAlias=? ORDER BY id DESC', array( $params['alias'] ));
            if ($f) {
                $id = $f['id']; 
				$params = array_merge(unserialize($f['params']), $params);
            } 			
		}		
		
        parent::__construct($id, $params);
		
        if ($this->getId()) {
			$r = self::getDbConnection()->fetchAll('SELECT widget_id FROM widgets_containers WHERE container_id=? ORDER BY position', array( $this->getId() ));
            foreach ($r as $f) try {
				$this->widgets[] = $this->application->getWidget( (int)$f['widget_id'] );
            } catch (\Exception $e) {}				
        }
		
		if ($params['data']) {
			$data = json_decode($params['data'], true);
			foreach ($data as $params) try {
				$this->widgets[] = $this->application->getWidget( $params );
			} catch (\Exception $e) {}	
		}
    }
	
	/**
	 * Устанавливает в контейнер виджеты
	 * 
	 * @param array $widgets массив виджетов
	 */		
    protected function setWidgets($widgets) {
		$this->widgets = [];
		foreach ($widgets as $w)  {
			if (is_subclass_of($w, 'Cetera\Widget\Widget')) $this->widgets[] = $w;
			elseif ($w) $this->widgets[] = $this->application->getWidget( (int)$w );
		}	
    }	

	/**
	 * Добавляет в контейнер виджет
	 * 
	 * @param int $id ID добавляемого виджета
	 */		
    public function addWidget($id)
    {
        if (!(int)$id) return $this;
        $this->widgets[] = $this->application->getWidget( (int)$id );
        return $this;  
    }
    
	/**
	 * Удаляет виджет из контейнера
	 * 
	 * @param int $id ID удаляемого виджета
	 */		
    public function removeWidget($id)
    {
        if (!(int)$id) return $this;
                    
		foreach($this->widgets as $key => $w) {
			if ($w->getId() == $id) {
				unset($this->widgets[$key]);
				return $this;  
			}
		}        
        return $this;  
    }
    
	/**
	 * Сохраняет виджет в БД
	 */    
    public function save()
    {
		$widgets = $this->getParam('widgets');
		if (is_array($widgets)) $this->widgets = $widgets;
		unset( $this->_params['widgets'] );
		
        parent::save();
		
        $this->getDbConnection()->delete('widgets_containers', array( 'container_id' => $this->getId() ));
        foreach($this->widgets as $i => $w)
            $this->getDbConnection()->insert('widgets_containers', array(
                'container_id' => $this->getId(),
                'widget_id'    => $w->getId(),
                'position'     => $i
            ));       
    }
    
	/**
	 * Возвращает содержимое контейнера
	 */	
    public function getChildren()
    {      
        return $this->widgets;
    }
	
	/**
	 * Экспорт виджета в XML
	 */		
	public function getXml()
    {
		$res  = '<widgetcontainer widgetAlias="'.htmlspecialchars($this->widgetAlias).'" widgetTitle="'.htmlspecialchars($this->widgetTitle).'" widgetDisabled="'.(int)$this->widgetDisabled.'" widgetProtected="'.(int)$this->widgetProtected.'">'."\n\n";
		foreach ($this->getChildren() as $w) {
			$res .= $w->getXml()."\n";
		}
		$res .= '</widgetcontainer>'."\n";
		return $res;
	}
    
}