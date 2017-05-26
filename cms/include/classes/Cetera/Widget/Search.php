<?php
namespace Cetera\Widget; 

require_once( 'phpmorphy/src/common.php');

/**
 * Виджет "Поиск по сайту"
 * 
 * @package CeteraCMS
 */ 
class Search extends Templateable {
	
	use Traits\Paginator;
	
	public static $name = 'Search';
	
	protected $results = null;
	protected $query = null;
	protected $_sections = null;
		
	protected function initParams()
	{
		$this->_params = array(
			'type'                 => 1,
			'min_length'           => 3,
			'sections'             => null,
			'search_subsections'   => true,
			'morphology'           => false,
			'fields'               => 'name, text, short',
			'query_param'          => 'query',
			'query_placeholder'    => $this->t->_('Введите строку для поиска'),
			'button_text'          => $this->t->_('Искать'),
			'page_param'           => 'page',
			'page'                 => null,
			'items_per_page'       => 20,
			'sort_field'           => 'dat',
			'sort_direction'       => 'DESC',
			'paginator'            => true,
			'paginator_template'   => false,
			'paginator_url'        => '?{query_string}',
			'path_template'        => 'path.twig',
			'date_format'          => 'd.m.Y',
			'show_date'            => true,
			'show_path'            => true,
			'template'			   => 'default.twig',
		);  		
	}		

	public function querySubmitted()
	{
		
		return isset( $_REQUEST[$this->getParam('query_param')] );
	}
	
	public function queryValue()
	{	
		if ($this->query === null)
		{
			$this->query = $_REQUEST[$this->getParam('query_param')];
			$this->query = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $this->query);
		}
		return $this->query;
	}	
	
	public function getResults()
	{
		if (!$this->results)
		{
			
			$sections = array();
			foreach ($this->getSections() as $c)
			{
				if ($where) $where .= ' or ';
				if ($this->getParam('search_subsections'))
					$sections =  array_merge($sections, $c->getSubs());
					else $sections[] = $c->id;
			}
			$where = '`idcat` IN ('.implode(',', array_unique($sections)) .')';
			
			$where2 = $this->buildWhere();
						
			if ($where2)
			{
				$this->results = new \Cetera\Iterator\DynamicObjectMultiple( $this->getObjectDefinitions(), $this->getSearchFields() );
				$this->results
					->orderBy( $this->getSortField(), $this->getSortDirection() )
					->where( $where )
					->where( $where2 )
					->setItemCountPerPage( $this->getItemCountPerPage() )
					->setCurrentPageNumber( $this->getPage() );
			}
		}
		return $this->results;
	}
	
	public function getSections()
	{
		if (!$this->_sections)
		{
			$sections = $this->getParam('sections');

			if (!is_array($sections))
			{
				if ($sections)
				{
					$sections = explode(',', $sections);
					array_walk($sections, 'intval');
				}				
				if (!$sections || !count($sections))
				{
					$sections = array( $this->application->getServer()->id );
				}

			}
			$this->_sections = array();
			foreach ($sections as $id)
			{
				$this->_sections[] = \Cetera\Catalog::getById($id);
			}
		}

		return $this->_sections;
	}
	
	protected function getSortField()
	{
		return $this->getParam('sort_field','dat');		
	}	

	protected function getSortDirection()
	{
		return $this->getParam('sort_direction','DESC');			
	}	
	
	protected function getItemCountPerPage()
	{
		$value = (int)$this->getParam('items_per_page');
		if (!$value) $value = 20;
		return $value;
	}			
	
	protected function getObjectDefinitions()
	{
		$types = $this->getParam('type');
		if (!is_array($types))
		{
			$type_id = (int)$this->getParam('type');
			if (!$type_id) $type_id = 1;
			$types = array($type_id);
		}	
		$res = array();
		
		foreach ($types as $t)
		{
			$res[] = \Cetera\ObjectDefinition::findById($t);
		}
		
		return $res;
	}	
	
	protected function splitQueryToWords()
	{
		$q = $this->queryValue();
		$q = mb_strtoupper($q);
		$www = explode(" ", $q);
		$words = array();
		foreach ($www as $w) {
			if (strlen(trim($w)) >= $this->getParam('min_length')) $words[$w] = $w;
		}	
		if ($this->getParam('morphology')) {
			$morphy = new \phpMorphy(DOCROOT.LIBRARY_PATH.'/phpmorphy/dicts/', 'ru_RU', array('storage' => PHPMORPHY_STORAGE_FILE));
			$res = $morphy->getAllForms($words, \phpMorphy::IGNORE_PREDICT);
			if (count($res)) $words = $res;
		}
		
		return $words;
	}

	protected function getSearchFields()
	{
		$f = trim($this->getParam('fields'));
		if (!$f) $f = 'name';
		$fields = array();
		foreach (explode(',', $f) as $field) {
			$fields[] = trim($field);
		}
		return $fields;
	}		
	
	protected function buildWhere()
	{
		
		$fields = $this->getSearchFields();
		$words = $this->splitQueryToWords();
		
		if (!count($words)) return false;
		
		$res = array();
		foreach ($fields as $f) {
			$f = '`'.$f.'`';
			$res2 = array();
			foreach ($words as $key => $word) {
				$res3 = array();
				if (is_array($word)) {
					foreach ($word as $w) {						
						if (strlen($w) < $this->getParam('min_length')) continue;
						$res3[] = $f.' LIKE "%'.$w.'%"';
					}
					$res2[] = '('.implode(' or ',$res3).')';
				} else {
					if (strlen($key) < $this->getParam('min_length')) continue;
					$res2[] = '('.$f.' LIKE "%'.$key.'%")';
				}
			}
			$res[] = '('.implode(' and ',$res2).')';
		}
		return '('.implode(' or ',$res).')';
	} 

	public function getCatalog() 
	{
		return $this->application->server;
	}
	
	public function getChildren() 
	{
		return $this->getResults();
	}	
      
}