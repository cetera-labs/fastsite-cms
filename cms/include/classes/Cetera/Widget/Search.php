<?php
namespace Cetera\Widget; 

/**
 * Виджет "Поиск по сайту"
 * 
 * @package FastsiteCMS
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
            'fulltext'             => false,
            'fulltext_boolean'     => false,
			'morphology'           => false,
			'translit'             => false,
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
			'where'                => null,
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

	private function getSubsections($section)	
	{
		$result = [$section->id];
		foreach ($section->children as $c) {
			if ($c->isHidden()) continue;
			$result = array_merge($result, $this->getSubsections($c));
		}
		return $result;
	}
	
	public function getResults()
	{
		if (!$this->results)
		{
			
			$sections = [];
			foreach ($this->getSections() as $c)
			{
				if ($c->isHidden()) continue;
				
				if ($where) $where .= ' or ';
				if ($this->getParam('search_subsections'))
					$sections =  array_merge($sections, $this->getSubsections($c));
					else $sections[] = $c->id;
			}
			$where = '`idcat` IN ('.implode(',', array_unique($sections)) .')';
			
			$where2 = $this->buildWhere();
						
			if ($where2)
			{
				$fields = $this->getSearchFields();
				$fields[] = $this->buildRelevance().' AS relevance';
				
				$this->results = new \Cetera\Iterator\DynamicObjectMultiple( $this->getObjectDefinitions(), $fields );
				
				$this->results
					->orderBy( $this->getSortField(), $this->getSortDirection() )
					->where( $where )
					->where( $where2 )
					->setItemCountPerPage( $this->getItemCountPerPage() )
					->setCurrentPageNumber( $this->getPage() );
					
				if ($this->getParam('where')) {
					$this->results->where( $this->getParam('where') );
				}	
				
				if ($this->getParam('debug')) {
                    print $this->results->getQuery();
                }
			}
		}
		return $this->results;
	}
	
	public function getSections()
	{
		if (!$this->_sections)
		{
			$sections = $this->getParam('sections');

			if (!is_array($sections)) {
				if ($sections) {
					$sections = explode(',', $sections);
					array_walk($sections, 'intval');
				}				
				if (!$sections || !count($sections)) {
					$sections = array( $this->application->getServer()->id );
				}

			}
			$this->_sections = array();
			foreach ($sections as $id) {
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
		
	protected function buildRelevance()
	{
        if ($this->getParam('fulltext')) {
            return $this->buildFullTextQuery();
        }
        else {            
            $fields = $this->getSearchFields();
            $words = $this->splitQueryToWords();
            
            $res = '';
            $i = 0;
            foreach ($fields as $f) {
                $f = '`'.$f.'`';
                if ($res) $res .= ' + ';
                $res .= 'IF('.$f.' LIKE "%'.$this->queryValue().'%",'.(5000-$i*10).',0)';
                
                foreach ($words as $key => $word) {
                    
                    if (is_array($word)) {
                        $weight = 500;
                        foreach ($word as $w) {						
                            if (strlen($w) < $this->getParam('min_length')) continue;
                            $res .= ' + IF ('.$f.' LIKE "%'.$w.'%",'.($weight-$i).',0)';
                            $weight = 100;
                        }
                    } else {
                        if (strlen($key) < $this->getParam('min_length')) continue;
                        $res .= ' + IF ('.$f.' LIKE "%'.$key.'%",'.(500-$i).',0)';
                    }				
                    
                }
                
                $i++;
            }

            return $res;
        }
	}		
	
	protected function buildWhere()
	{
        if ($this->getParam('fulltext')) {
            return $this->buildFullTextQuery();
        }
        else {
		
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
	} 
        
    protected function buildFulltextQuery()
    {
        $words = $this->splitQueryToWords();

        foreach ($words as $key => $word) {
            if (is_array($word)) {
                $s[] = '('.implode(' ',$word).')';
            } else {
                $s[] = $key;
            }
        }

        if ($this->getParam('fulltext_boolean')) {
            $res = "MATCH (".implode(',',$this->getSearchFields()).") AGAINST ('+".implode(' +',$s)."' IN BOOLEAN MODE)";
        }
        else {
            $res = "MATCH (".implode(',',$this->getSearchFields()).") AGAINST ('".implode(' ',$s)."')";
        }

        return $res;
    }
    

	public function getCatalog() 
	{
		return $this->application->server;
	}
	
	public function getChildren() 
	{
		return $this->getResults();
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
			$morphy = new \componavt\phpMorphy\Morphy();
			$res = $morphy->getAllForms($words);
			foreach ($words as $w) {
				if (isset($res[$w])) {
					$words[$w] = $res[$w];
				}
			}
		}	

		if ($this->getParam('translit')) {
			foreach ($words as $w => $v) {
				if (!is_array($v)) $words[$w] = [$w];
				$words[$w][] = translit($w);
			}
		}	
		return $words;
	}  

    public function highlight($text)
    {
        $rpl = '<b>$1</b>';

        $words = $this->getWords();
        foreach ($words as $key => $word){
            $wordsPattern[$key] = mb_strtolower('#(' . $word . ')#iuU');
        }

        foreach ($words as $word) {
            $_word = mb_strtolower($word);
            $_text = mb_strtolower($text);
            $res = mb_strpos($_text, $_word);
            if ($res) {
                $s = mb_strpos($_text, $_word) - 500;
                $e = mb_strrpos($_text, $_word) + 500;
                if ($s < 0) $s = 0;
                $n = '';
                if ($s > 0) $n = '... ';
                $n .= mb_substr($text, $s, $e - $s);

                if ($e < mb_strlen($text)) $n .= ' ...';

                if (mb_strlen($n) > 1000)
                {
                    $n = mb_substr($n, 0, 1000).' ...';
                }


                $n = preg_replace($wordsPattern, $rpl, ' '.$n.' ', -1);


                return $n;

            }
        }

        return mb_substr($text, 0, 1000).' ...';
    }
    
}