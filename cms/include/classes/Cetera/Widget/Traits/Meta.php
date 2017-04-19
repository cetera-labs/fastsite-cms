<?php
namespace Cetera\Widget\Traits; 

trait Meta {
		
    public function setMetaTitle($name)
    {
		$a = $this->application;
		$t = $a->getPageProperty('title');
		if ($t) $t = ' &mdash; '.$t;
		$a->setPageProperty('title', $name.$t);
		
		$a->addHeadString('<meta itemprop="name" content="'.htmlspecialchars($name).'"/>');
		$a->addHeadString('<meta name="twitter:title" content="'.htmlspecialchars($name).'">');
		$a->addHeadString('<meta property="og:title" content="'.htmlspecialchars($name).'"/>', 'og:title');
    }
	
	public function setMetaDescription($short)
	{
		if ($short)
		{
			$a = $this->application;
			$a->setPageProperty('description', $short);
			$a->addHeadString('<meta itemprop="description" content="'.htmlspecialchars($short).'"/>');
			$a->addHeadString('<meta name="twitter:description" content="'.htmlspecialchars($short).'">');
			$a->addHeadString('<meta property="og:description" content="'.htmlspecialchars($short).'"/>', 'og:description');		
		}		
	}
	
	public function setMetaPicture($pic,$width = 0, $height = 0)
	{
		if ($pic)
		{
			if ((!$width || !$height) && file_exists(WWWROOT.$pic)) {
				list($width, $height) = getimagesize(WWWROOT.$pic);
			}
			$a = $this->application;
			$a->addHeadString('<meta itemprop="image" content="http://'.$_SERVER['SERVER_NAME'].$pic.'"/>');
			$a->addHeadString('<meta name="twitter:image:src" content="http://'.$_SERVER['SERVER_NAME'].$pic.'">');
			$a->addHeadString('<meta property="og:image" content="http://'.$_SERVER['SERVER_NAME'].$pic.'"/>', 'og:image');		
			if ($width)  $a->addHeadString('<meta property="og:image:width" content="'.$width.'"/>', 'og:image:width');	
			if ($height) $a->addHeadString('<meta property="og:image:height" content="'.$height.'"/>', 'og:image:height');	
		}		
	}

}