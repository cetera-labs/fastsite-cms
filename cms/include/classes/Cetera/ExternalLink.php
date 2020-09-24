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
	
	public function __construct($name, $url)
	{
		if (!$name) $name = $url;
		$this->name = $name;
		$this->url = $url;
	}
	
    public function getUrl()
	{
		return $this->url;
	}
	
    public function getFullUrl()
	{
		return $this->url;
	}	

}