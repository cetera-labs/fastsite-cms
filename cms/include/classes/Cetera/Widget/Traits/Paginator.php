<?php
namespace Cetera\Widget\Traits; 

trait Paginator {
	
    public function getPage()
    {
		$p = null;
		if ($this->getParam('page')) $p = (int)$this->getParam('page');
		if ($this->getParam('paginator') && isset($_REQUEST[ $this->getParam('page_param') ])) {
			$p = (int)$_REQUEST[ $this->getParam('page_param') ];
		}
		if (!$p) $p = 1;
		return $p;
	}
	
    public function getPaginator()
    {
		$query = $_GET;
		$query[$this->getParam('page_param')] = '{page}';

		$trans = array(
			'{catalog}'     => $this->getCatalogPath(),
			'{page_param}'  => $this->getParam('page_param'),
			'{query_string}'=> urldecode(http_build_query($query)),
		);
		
		$url = strtr($this->getParam('paginator_url'), $trans);
		
		return $this->application->getWidget('Paginator',array(
			'iterator' => $this->getChildren(),
			'url'      => $url,
			'template' => $this->getParam('paginator_template'),		
		))->getHtml();
	}	

	private function getCatalogPath() {
		$dir = explode("/", $_SERVER['REQUEST_URI']);

		return ($dir[1] != 'search') ? $this->getCatalog()->url : '';
	}
}