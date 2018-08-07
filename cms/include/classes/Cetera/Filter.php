<?php
namespace Cetera;

class Filter {
	
	const TYPE_NUMERIC        = 1;
	const TYPE_NUMERIC_SLIDER = 2;
	const TYPE_CHECKBOX       = 3;
	const TYPE_RADIO          = 4;
	const TYPE_DROPDOWN       = 5;
	const TYPE_TEXT           = 6;
	const TYPE_DATE           = 7;
	const TYPE_DATE_INTERVAL  = 8;

	protected $iterator;
	protected $active = false;
	protected $info = null;
	protected $data = null;
	protected $a;
	public $name = false;	
	
    public function __construct($name, Iterator\DynamicObject $iterator)
    {
		$this->iterator = $iterator;
		$this->name = $name;	
		$this->a = \Cetera\Application::getInstance();
		$this->data = array();
    } 	
	
	public function submittedValue($name)
	{
		return (isset($_REQUEST[ $this->name ][$name]) && $_REQUEST[ $this->name ][$name])?$_REQUEST[ $this->name ][$name]:null;
	}
	
	public function getQueryString()
	{
		return http_build_query(array($this->name => $_REQUEST[ $this->name ]));
	}

	public function isActive()
	{
		$this->getInfo();
		return $this->active;
	}	
	
	public function addField($name, $type = self::TYPE_CHECKBOX)
	{
		$this->data[] = array(
			'field' => $this->iterator->getObjectDefinition()->getField($name),
			'filter_type' => $type,
		);
	}
	
	public function getIterator()
	{
		return $this->iterator;
	}

	public function getInfo()
	{		
		if (!$this->info)
		{		
			$this->active = false;
			
			$this->info = [];
			foreach ($this->data as $d) {		
								
				$d['describ'] = $this->a->decodeLocaleString( $d['field']['describ'] );							
				$d['iterator'] = array();
				$d['submitted'] = false;
				$d['name'] = $d['field']['name'];
				$d['field_id'] = $d['field']['field_id'];
				
				if (is_subclass_of($d['field'], '\\Cetera\\ObjectFieldLinkAbstract')) {					
					$d['iterator'] = $d['field']->getIterator();
					$d['value'] = $this->submittedValue($d['name']);
					if ($d['value']) {
						$this->active = true;
						$d['submitted'] = true;
					}					
				}
				elseif ($d['field'] instanceof \Cetera\ObjectField) {
					if (
							($d['field']['type'] == FIELD_INTEGER || $d['field']['type'] == FIELD_DOUBLE)
							&&
							($d['filter_type'] == self::TYPE_NUMERIC_SLIDER || $d['filter_type'] == self::TYPE_NUMERIC)
					   )
					{
						$list = clone $this->iterator;
						
						$f = $this->generateField($d['field']);
						$list->select(['MIN('.$f.') as _MIN_','MAX('.$f.') as _MAX_']);
						$m = $list->current();
						if (!$m) continue;
						$d['min'] = $m->fields['_MIN_'];
						$d['max'] = $m->fields['_MIN_'];
						if ($d['min'] === NULL && $d['max'] === NULL) continue;
						if ($d['max'] == $d['min']) $d['max'] += 10;
						
						$d['value_min'] = $this->submittedValue($d['name'].'_min');
						$d['value_max'] = $this->submittedValue($d['name'].'_max');
						if($d['value_min'] === null) $d['value_min'] = $d['min'];
						if($d['value_max'] === null) $d['value_max'] = $d['max'];
						if ($d['value_min'] != $d['min'] || $d['value_max'] != $d['max'])
						{
							$this->active = true;
							$d['submitted'] = true;
						}
					}
					elseif ($d['field']['type'] == FIELD_BOOLEAN) {
						if ($d['filter_type'] == self::TYPE_RADIO) {
							$d['iterator'] = array(
								array('id' => 0, 'name' => self::t()->_('да') ),
								array('id' => 0, 'name' => self::t()->_('нет') )
							);
						}
						else {
							$d['iterator'] = false;
							$d['filter_type'] = self::TYPE_CHECKBOX;
						}
						$d['value'] = $this->submittedValue($d['name']);
						if ($d['value']) {
							$this->active = true;
							$d['submitted'] = true;
						}
					}
					else {	
					
						if ($d['filter_type'] == self::TYPE_DATE_INTERVAL) {
							
							$d['value_min'] = $this->submittedValue($d['name'].'_min');
							$d['value_max'] = $this->submittedValue($d['name'].'_max');		
							if ($d['value_min'] != $d['min'] || $d['value_max'] != $d['max']) {
								$this->active = true;
								$d['submitted'] = true;
							}							
							
						}
						else {
					
							$d['iterator'] = [];
							
							$f = $this->generateField($d['field']);
							$list = clone $this->iterator;
							$list->select($f.' AS '.$d['name'])->orderBy($f)->groupBy($f, false)->setItemCountPerPage(0);

							if (!$list->getCountAll()) continue;
							foreach($list as $m) {
								if (!$m->fields[$d['name']]) continue;
								$d['iterator'][] = array(
									'id'   => $m->fields[$d['name']],
									'name' => $m->fields[$d['name']],
								);
							}
							$d['value'] = $this->submittedValue($d['name']);
							if ($d['value']) {
								$this->active = true;
								$d['submitted'] = true;
							}
							
							if (!count($d['iterator'])) continue;
						
						}
					}					
				}
				$this->info[ $d['field_id'] ] = $d;
			}
		}
		return $this->info;
	}	
	
	/*
	 * применить фильтр к итератору
	 */
	public function apply()
	{
		if (!$this->isActive()) return;
		
		foreach ($this->getInfo() as $f)
		{
			switch($f['filter_type']) 
			{
				case self::TYPE_NUMERIC:
				case self::TYPE_NUMERIC_SLIDER:
					if ($this->submittedValue($f['name'].'_min') !== null)
					{
						$this->iterator->where( $this->generateField($f['field']).' >= '.(float)$this->submittedValue($f['name'].'_min') );
					}
					if ($this->submittedValue($f['name'].'_max') !== null && $this->submittedValue($f['name'].'_max'))
					{
						$this->iterator->where( $this->generateField($f['field']).' <= '.(float)$this->submittedValue($f['name'].'_max') );
					}	
					break;
				case self::TYPE_RADIO:
				case self::TYPE_DROPDOWN:
					if ($this->submittedValue($f['name']) !== null) {
						$this->iterator->where( $this->generateField($f['field']).' = :'.$f['name'] )
									   ->setParameter($f['name'], $this->submittedValue($f['name']));
					}				
					break;
				case self::TYPE_CHECKBOX:
					if ($this->submittedValue($f['name']) !== null) {
						if (is_array($this->submittedValue($f['name']))) {
							$a = array();
							foreach ($this->submittedValue($f['name']) as $value => $dummy) {
								$a[] = '"'.$value.'"';
							}
							$this->iterator->where( $this->generateField($f['field']).' IN ('.implode(',',$a).')' );
						}
						else {
							if ($this->submittedValue($f['name'])) {
								$this->iterator->where( $this->generateField($f['field']).' > 0' );
							}
						}
					}
					break;
				case self::TYPE_DATE_INTERVAL:
					if ($f['value_min']) {
						$this->iterator->where( $this->generateField($f['field']).' >= STR_TO_DATE(:'.$f['name'].'_min,"%Y-%m-%d")' )
									   ->setParameter($f['name'].'_min', $f['value_min']);						
					}
					if ($f['value_max']) {
						$this->iterator->where( $this->generateField($f['field']).' <= DATE_ADD(STR_TO_DATE(:'.$f['name'].'_max,"%Y-%m-%d"), INTERVAL 1 DAY)' )
									   ->setParameter($f['name'].'_max', $f['value_max']);						
					}
					break;
				case self::TYPE_DATE:
					if ($f['value']) {
						$this->iterator->where( 'DATE_FORMAT('.$this->generateField($f['field']).',"%Y-%m-%d") = :'.$f['name'] )
									   ->setParameter($f['name'], $f['value']);						
					}				
					break;
				case self::TYPE_TEXT:
					if ($f['value']) {
						$this->iterator->where( $this->generateField($f['field']).' LIKE :'.$f['name'] )
									   ->setParameter($f['name'], '%'.$f['value'].'%');						
					}					
					break;
			}
		}
		$this->iterator->groupBy('main.id');
	}

	protected function generateField($field)
	{
		if (is_subclass_of($field, '\\Cetera\\ObjectFieldLinkSetAbstract')) {					
			return '`'.$field['name'].'`';
		}	
		else {
			return 'main.'.$field['name'];
		}
		
	}	

}