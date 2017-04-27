<?php
namespace Cetera\Widget; 

/**
 * Виджет "Календарь"
 * 
 * @package CeteraCMS
 */ 
class Calendar extends Templateable {
			
	use Traits\Catalog;
	
	public static $name = 'Calendar';
	
	protected $month;
		
    protected $_params = array(
		'template'    => 'default.twig',
		'catalog'     => 0,
		'date_param'  => 'date',
		'date'        => null,
		'day_url'     => '?{date_param}={date}',
		'month_url'   => '?{date_param}={date}&month=1',
		'subsections' => true,
		'ajax'        => false,
    );
	
	protected function initParams()
	{
		$this->month = array(
			$this->t->_('Январь'),
			$this->t->_('Февраль'),
			$this->t->_('Март'),
			$this->t->_('Апрель'),
			$this->t->_('Май'),
			$this->t->_('Июнь'),
			$this->t->_('Июль'),
			$this->t->_('Август'),
			$this->t->_('Сентябрь'),
			$this->t->_('Октябрь'),
			$this->t->_('Ноябрь'),
			$this->t->_('Декабрь'),
		);
	}		
	
	public function getDayUrl($date)
	{
		return $this->replaceParams($this->getParam('day_url'), $date);
	}	
	
	public function getMonthUrl($date)
	{
		return $this->replaceParams($this->getParam('month_url'), $date);
	}		
	
	public function getDateParam()
	{
		return $this->getParam('date_param','date');
	}
	
	public function getDate()
	{
		$date = $this->getParam('date');
		if ($date) return $date;
		$d = $this->getDateParam();
		if ($d) return isset($_REQUEST[$d])?$_REQUEST[$d]:date('Y-m-d');
		return date('Y-m-d');
	}
	
	public function getTime()
	{
		return strtotime( $this->getDate() );
	}	
	
	public function getMonth($m)
	{
		return $this->month[$m-1];
	}	
	
	public function getCalendar()
	{	
		list($y,$m,$d) = explode('-', $this->getDate() );
		$m = '"'.mysql_real_escape_string($m.'.'.$y).'"';		
		
		$month = $this->getCatalog()->getMaterials()->select('DATE_FORMAT(dat,"%e") as day')->where('DATE_FORMAT(dat,"%m.%Y") = '.$m);
		if ($this->getParam('subsections')) $month->subFolders();
			
		$calendar = array();
		foreach($month as $l)
		{
			$calendar[(int)$l->fields['day']] = 1;  
		}
		return $calendar;
	}

	protected function replaceParams($str, $date)
	{
		$str = str_replace('{date_param}',$this->getParam('date_param'), $str);
		$str = str_replace('{date}',$date, $str);
		return $str;
	}
      
}