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
 * Базовый класс виджетов.
 * 
 * @package FastsiteCMS
 */ 
class Widget {

    use \Cetera\DbConnection;

 	/**
	 * Параметры виджета
	 */ 	
    protected $_params = array();
	
 	/**
	 * Идентификатор виджета
	 */ 	
    protected $_id = 0;
    
    public $application = NULL;
	protected $t = NULL;
    public $widgetName = NULL;
    public $widgetAlias;
    public $widgetTitle;
    public $widgetDescrib;
    public $widgetDisabled = FALSE;
    public $widgetProtected = FALSE;
	
	private static $count = 1;
	public static $name = null;
	private static $data = array();
	protected static $uniquePrefix = 'x_widget_';
	protected $uniqueId = null;
    
	 /**
	 * Контейнеры, в которых находится виджет
	 */ 
    private $containers = array();
    
    public function __construct($id = 0, $params = null, $uid = null)
    {
        $this->_id = $id;
		$this->uniqueCount = self::$count++;
		
        if ($this->getId()) {
			$r = \Cetera\DbConnection::getDbConnection()->fetchAll('SELECT container_id FROM widgets_containers WHERE widget_id=?', array($this->getId()));
			foreach ($r as $f)
                $this->containers[] = $f['container_id'];
        }
		$this->application = \Cetera\Application::getInstance();
		$this->t = $this->application->getTranslator();
		$this->initParams();
		$this->setParams($params);
		if ($uid)
			$this->setUniqueId($uid);
		if ($this->application->isFrontOffice())
			$this->init();
    }
	
	// заполнение _params дефолтными значениями
	protected function initParams()
	{
	}	
	
	protected function init()
	{
	}
	
    public function getUniqueId()
    {
		if ($this->uniqueId == null) {
			$this->uniqueId = static::$uniquePrefix.$this->uniqueCount;
		}
        return $this->uniqueId;
    }
	
    public function setUniqueId($uid)
    {
        $this->uniqueId = $uid;
    }		
	
    public function getAjaxUrl()
    {
        return '/cms/include/widget.php?ajaxCall=1&locale='.$this->application->getLocale().'&widget='.$this->widgetName.'&unique='.$this->getUniqueId();
    }

	public function isAjax() {
		return isset($_REQUEST['ajaxCall']);
	}
    
    public function getId()
    {
        return (int)$this->_id;
    }

    /*
     * Параметры виджета
     * 
     * @return array          
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    public function setParams($params)
    {
        if (is_array($params)) 
			foreach ($params as $name => $value) 
				if ($value !== null) 
					$this->setParam($name, $value);
		return $this;
    }

    public function getParam($name, $default = null)
    {
		$res = null;
		
        if (property_exists($this, $name)) {
			$res = $this->$name;
		}
		elseif (isset($this->_params[$name])) {
			$res = $this->_params[$name];
		}
		
		if (!$res && $default) $res = $default;
		
		return $res;
    }
    
    public function setParam($name, $value)
    {
        if (property_exists($this, $name)) $this->$name = $value;
		$method = 'set'.ucfirst($name);
		if (method_exists($this, $method)) $this->$method($value);
        $this->_params[$name] = $value;
		return $this;
    }
        
    public function getHtml()
    {
        if ($this->widgetDisabled) return false;
        return $this->_getHtml();
    }
    
    protected function _getHtml()
    {
        return '*** Widget "'.$this->widgetName.'" ***';
    }
    
    public function display() {
         print $this->getHtml();
    }
	
    public function __toString() {
         return $this->getHtml();
    }	
    
    public function save()
    {
        
        $params = array(
            'widgetDisabled' => (int)$this->widgetDisabled,
            'protected' => (int)$this->widgetProtected,
            'widgetTitle' => $this->widgetTitle,
            'widgetAlias' => $this->widgetAlias,
            'widgetName' => $this->widgetName,
            'params' => serialize($this->getParams()),
        );
        
        if ($this->getId()) {
            $this->getDbConnection()->update('widgets', $params, array( 'id' => $this->getId() ));
        } else {
            $this->getDbConnection()->insert('widgets', $params);
            $this->_id = $this->getDbConnection()->lastInsertId();
        }
        
        return $this;
    }
    
    public function delete()
    {
        if (!$this->getId()) return TRUE;
        if ($this->widgetProtected) return FALSE;
        $this->getDbConnection()->executeQuery('DELETE FROM widgets WHERE id=?', array( $this->getId() ));
        $this->getDbConnection()->executeQuery('DELETE FROM widgets_containers WHERE widget_id=? or container_id=?', array( $this->getId(),$this->getId() ));
        return TRUE;
    }
    
    public function addToContainer($id)
    {
        if (!in_array($id, $this->containers)) { 
            $this->containers[] = $id;
            $c = $this->application->getWidget($id);
            $c->addWidget($this->getId())->save();
        }
    }
    
    public function removeFromContainer($id, $cleanup)
    {
    
        $i = array_search($id, $this->containers);
        if ($i !== false) {
            unset($this->containers[$i]);
            $c = $this->application->getWidget($id);
            $c->removeWidget($this->getId())->save();   
        } 
        
        if ($cleanup && !count($this->containers))
            $this->delete();
        
    }
	
	public final static function setData($data)
	{
		self::$data[ get_called_class() ] = $data;
	}
	
	public final static function getData($key = null)
	{
		if ($key) return self::$data[ get_called_class() ][$key];
		return self::$data[ get_called_class() ];
	}	

	public final static function getName()
	{	
		if (isset(self::$data[ get_called_class() ]['name'])) return self::$data[ get_called_class() ]['name'];
		if (static::$name !== null) return static::$name;
		return false;
	}
	
	/**
	 * Экспорт виджета в XML
	 */		
	public function getXml()
    {
		$res  = '<widget widgetTitle="'.htmlspecialchars($this->widgetTitle).'" widgetName="'.htmlspecialchars($this->widgetName).'">'."\n";
		$res .= "<![CDATA[\n".serialize($this->getParams())."\n]]>\n";
		$res .= '</widget>'."\n";
		return $res;
	}	
}