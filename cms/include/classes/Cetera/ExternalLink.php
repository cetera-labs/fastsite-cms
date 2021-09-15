<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera; 
 
class ExternalLink implements SiteItem {

	public $name = '';
	public $url = '';
    public $children = [];
	
	public function __construct($params)
	{
		if (!isset($params['name']) || !$params['name']) $params['name'] = $params['url'];
		$this->name = $params['name'];
		$this->url = $params['url'];
        if (isset($params['children'])) {
            $this->children = $params['children'];
        }
	}
	
    public function getUrl()
	{
		return $this->url;
	}
	
    public function getFullUrl()
	{
		return $this->url;
	}	

    public function getChildren()
	{
		$children = [];
        foreach ($this->children as $c) {
            try {
                if (isset($c['id']) && $c['id']) {
                    $children[] = DynamicFieldsObject::getByIdType($c['id'], $c['type']);
                }
                elseif (isset($c['url']) && $c['url']) {
                    $children[] = new ExternalLink($c);
                }
                else {
                    throw new \Exception('Cant parse menu child');
                }
            } catch (\Exception $e) {}            
        }
        return $children;
	}

}