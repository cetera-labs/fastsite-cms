<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
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
 * @package CeteraCMS
 */ 
class Container extends Templateable {

	/**
	 * Виджеты, хранящиеся в контейнере
	 */
    protected $widgets = array();
	
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
		if ($params['alias'])
		{
            $f = self::getDbConnection()->fetchAssoc('SELECT * FROM widgets WHERE widgetAlias=? ORDER BY id DESC', array( $params['alias'] ));
            if ($f) {
                $id = $f['id']; 
				$params = array_merge(unserialize($f['params']), $params);
            } 			
		}		
		
        parent::__construct($id, $params);
		
        if ($this->getId())
		{
            $r = fssql_query('SELECT widget_id FROM widgets_containers WHERE container_id='.$this->getId().' ORDER BY position');
            while ($f = mysql_fetch_assoc($r))  
                $this->widgets[] = (int)$f['widget_id'];
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
                    
        if (!in_array($id, $this->widgets)) 
            $this->widgets[] = $id;
        
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
                    
        $i = array_search($id, $this->widgets);
        if ($i !== false) unset($this->widgets[$i]);
        
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
        foreach($this->widgets as $i => $wid)
            $this->getDbConnection()->insert('widgets_containers', array(
                'container_id' => $this->getId(),
                'widget_id'    => $wid,
                'position'     => $i
            ));       
    }
    
	/**
	 * Возвращает содержимое контейнера
	 */	
    public function getChildren()
    {
        $res = array();
        if (is_array($this->widgets))
            foreach ($this->widgets as $w) try {
                $res[] = $this->application->getWidget((int)$w);
            } catch (\Exception $e) {}
                
        return $res;
    }
	
	/**
	 * Экспорт виджета в XML
	 */		
	public function getXml()
    {
		$res  = '<widgetcontainer widgetAlias="'.$this->widgetAlias.'" widgetTitle="'.$this->widgetTitle.'" widgetDisabled="'.(int)$this->widgetDisabled.'" widgetProtected="'.(int)$this->widgetProtected.'">'."\n\n";
		foreach ($this->getChildren() as $w) {
			$res .= $w->getXml()."\n";
		}
		$res .= '</widgetcontainer>'."\n";
		return $res;
	}
    
}