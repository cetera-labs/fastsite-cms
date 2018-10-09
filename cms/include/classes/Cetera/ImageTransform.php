<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera;
 
/**
 * Класс обработки картинок
 * 
 * @package CeteraCMS
 */ 
class ImageTransform {
	
	const PREFIX = '/imagetransform';
	
	const FIT_NONE = 0;
	const FIT_CROP = 1;
	const FIT_CROP_CENTER = 2;
	const FIT_CONTAIN_CENTER = 3;
	
	const WM_EXACT = 0;	
	const WM_STRETCH = 1;
	const WM_FIT = 2;
	const WM_CONTAIN = 3;
	const WM_TILE = 4;
	
	
	protected $src = null;
	protected $src_info = null;
	protected $src_exists = false;
	protected $default;
	protected $isDirty = true;
	protected $functionout = null;
	protected $dst_img = null;
	protected $bgcolor = '#ffffff';
	
	protected $width = null;
	protected $height = null;
	protected $quality = 75;
	protected $enlarge = true;
	protected $aspect = true;
	protected $fit = 0;
	protected $face = false;
	
	protected $text = array();
	protected $watermark = array();
	
    public function __construct($src = null) 
    {
		$this->src = $src;
		$this->default = WWWROOT.'/cms/images/cmslogo_bw.png';
    }	
	
    public static function create($src = null) 
    {
		return new self($src);
	}

	public function setBgcolor($c)
	{
		$this->bgcolor = $c;
		$this->isDirty = true;
		return $this;
	}
	
	public function setWidth($width)
	{
		$this->width = (int)$width;
		$this->isDirty = true;
		return $this;
	}
	
	public function setHeight($height)
	{
		$this->height = (int)$height;
		$this->isDirty = true;
		return $this;
	}
	
	public function setQuality($quality)
	{
		$this->quality = (int)$quality;
		if ($this->quality > 100) $this->quality = 100;
		if ($this->quality < 0) $this->quality = 0;
		$this->isDirty = true;
		return $this;
	}
	
	public function setEnlarge($value)
	{
		$this->enlarge = (boolean)$value;
		$this->isDirty = true;
		return $this;
	}
	
	public function setAspect($value)
	{
		$this->aspect = (boolean)$value;
		$this->isDirty = true;
		return $this;
	}
	
	public function setFit($value)
	{
		$this->fit = (int)$value;
		$this->isDirty = true;
		return $this;
	}
	
	public function setDefault($value)
	{
		$this->default = $value;
		$this->isDirty = true;
		return $this;
	}	
	
	public function setText($text, $params = null)
	{
		$this->text = array();
		$this->addText($text, $params);
	}
	
	public function addText($text, $params = null)
	{
		$defaults = array(
			'left' => 0,
			'top'  => 0,
			'font'   => 5,
			'size'   => 14,
			'angle'  => 0,
			'color'  => '#FFFFFF',
			'alpha'  => 0, 
		);		
		if (!is_array($params)) $params = array();
		foreach ($defaults as $key => $value) {
			if (!isset($params[$key])) $params[$key] = $value;
		}
		
		$params['text'] = $text;
		
		$this->text[] = $params;
		$this->isDirty = true;
		return $this;
	}	
	
	public function setWatermark($src, $params = null)
	{
		$this->watermark = array();
		$this->addWatermark($src, $params);
	}		

	public function addWatermark($src, $params = null)
	{
		$defaults = array(
			'left'  => 0,
			'top'   => 0,
			'alpha' => 100, 
			'width' => 0,
			'height'=> 0,
			'size'  => self::WM_EXACT
		);		
		if (!is_array($params)) $params = array();
		foreach ($defaults as $key => $value) {
			if (!isset($params[$key])) $params[$key] = $value;
		}
		
		$params['src'] = $src;
		
		$this->watermark[] = $params;
		$this->isDirty = true;
		return $this;
	}	

	protected function transform()
	{
		if (!$this->isDirty) return $this;
		
		if (!file_exists($this->src) || !is_file($this->src)) {
			$this->src = $this->default;
		}
		
		$this->src_exists = $this->src && file_exists($this->src) && is_file($this->src);
				
		if ($this->src_exists) {
			$this->src_info = getimagesize($this->src);
			if (!$this->src_info) throw new \Exception('Invalid image '.$this->src);
			
			if (!$this->width && !$this->height) {
				$this->width = $this->src_info[0];
				$this->height = $this->src_info[1];
			} else { 
				if (!$this->width)  if ($this->aspect) $this->width = 10000; else $this->width = $this->src_info[0];
				if (!$this->height) if ($this->aspect) $this->height = 10000; else $this->height = $this->src_info[1];
			}
				
		} else {
					
			if (!$this->width && !$this->height) {
				$this->width = 256;
				$this->height = 256;
			} else { 
				if (!$this->width)  $this->width = $this->height;
				if (!$this->height) $this->height = $this->width;
			}
			$this->face = false;
		}	

		if ($this->src_exists) {
		
			if ($this->src_info[2] >= 4) throw new \Exception('No support for '.$info['mime']);
			
			switch ($this->src_info[2]) {
				case 1: $suf = 'gif'; break;
				case 2: $suf = 'jpeg'; break;
				case 3: $suf = 'png'; break;
			}
			
			// -------------------------------------------------------
			// корректировка размеров результата, если опция enlarge=0	
			if  (!$this->enlarge && ($this->width > $this->src_info[0] && $this->height > $this->src_info[1])) {

				$w = $this->src_info[0]/$this->width;
				$h = $this->src_info[1]/$this->height;
				$wh = $this->width/$this->height;	
			
				if ($this->aspect) {
					if ($this->fit == 3) {
						if ($w > $h) {
							$this->width =  $this->src_info[0];
							$this->height = $this->width/$wh;
						} else {
							$this->height = $this->src_info[1];
							$this->width = $this->height*$wh;
						}						
					}
					else {
						
						if ($this->width < $this->src_info[0] || $this->height < $this->src_info[1]) {
						
							if ($w < $h) {
								$this->width =  $this->src_info[0];
								$this->height = $this->width/$wh;
							} else {
								$this->height = $this->src_info[1];
								$this->width = $this->height*$wh;
							}
						
						}
						else {
							$this->width =  $this->src_info[0];
							$this->height = $this->src_info[1];
						}
					}					
				}
				else {
					$this->width = $this->src_info[0];
					$this->height = $this->src_info[1];
				}
			}
			// -------------------------------------------------------
			
			$ox = 0;
			$oy = 0;
			$dx = 0;
			$dy	= 0;			

			$w = $this->src_info[0]/$this->width;
			$h = $this->src_info[1]/$this->height;			
						
			if ($this->aspect && !$this->fit) {     
				if ($w > $h) {
					$new_w = $this->width;
					$new_h = intval($this->src_info[1]*$new_w/$this->src_info[0]);
				} else {
					$new_h = $this->height;
					$new_w = intval($this->src_info[0]*$new_h/$this->src_info[1]);
				}
			} else {
				$new_w = $this->width;
				$new_h = $this->height;
			}
			
			$res_w = $new_w;
			$res_h = $new_h;
			
			if ($this->fit && $this->aspect)
			{
				if ($this->fit == 3)
				{
					if ($w > $h) {			
						$dy = round(($this->height - $this->src_info[1]/$w)/2);
						$new_h = round ($this->src_info[1] / ($this->src_info[0] / $this->width) );
					} 
					else {	  
						$dx = round(($this->width - $this->src_info[0]/$h)/2);
						$new_w = round ($this->src_info[0] / ($this->src_info[1] / $this->height) );
					}
				}
				else {
					if ($w > $h) {			  
					  $new0 = intval($this->src_info[1]*$this->width/$this->height);
					  if ($this->fit == 2) $ox = round( ($this->src_info[0] - $new0)/2 );			  
					  $this->src_info[0] = $new0;
					} 
					else {	  
					  $new1 = intval($this->src_info[0]*$this->height/$this->width);
					  if ($this->fit == 2) $oy = round( ($this->src_info[1] - $new1)/2 );
					  $this->src_info[1] = $new1;
					}				
				}
			}
		} else {
			
			$suf = 'png';
			$new_w = $this->width;
			$new_h = $this->height;
			$res_w = $this->width;
			$res_h = $this->height;			
			
		}
		
		$function = 'imagecreatefrom'.$suf;
	   
		$this->functionout = 'image'.$suf;
		
		if (!function_exists($function)) throw new \Exception('Undefined function: '.$function);
		
		if ($new_w<1) $new_w = 1;
		if ($new_h<1) $new_h = 1;
		
		$this->width = intval($res_w);
		$this->height= intval($res_h);				
		
		$this->dst_img = imagecreatetruecolor($this->width,$this->height);
		if (!$this->dst_img) throw new \Exception('Error creating image');
		
		if ($this->bgcolor) {
			list($r,$g,$b) = sscanf($this->bgcolor, "#%2x%2x%2x");			
			$bg = imagecolorallocate($this->dst_img , $r,$g,$b);
			imagefilledrectangle($this->dst_img, 0, 0, $this->width , $this->height, $bg);
		}
		else {
			imagesavealpha($this->dst_img, true);
			imagealphablending($this->dst_img, false);
			$bg = imagecolorallocatealpha($this->dst_img , 255,255,255,127);	
			imagefilledrectangle($this->dst_img, 0, 0, $this->width , $this->height, $bg);
			imagealphablending($this->dst_img, true);
		}
		
		if ($this->src_exists) {
			
			$src_img = $function($this->src);
			if (!$src_img) throw new \Exception('Error reading source image');
			ImageCopyResampled($this->dst_img,$src_img,$dx,$dy,$ox,$oy,$new_w,$new_h,$this->src_info[0],$this->src_info[1]);
		  
		} else {
					  
		   $this->src_info['mime'] = 'image/png';
		   
		}
		
		// ------------------------------------------
		// наложение текста
		foreach ($this->text as $text) {
			
			list($r,$g,$b) = sscanf($text['color'], "#%2x%2x%2x");
			
			$textcolor = imagecolorallocatealpha($this->dst_img, $r, $g, $b, $text['alpha']);
						
			if ($text['left'] == 0) {
				$x = intval($this->width/2 - $this->textWidth($text)/2);
			}
			elseif ($text['left'] < 0) {
				$x = $this->width - $this->textWidth($text) + $text['left'];;
			}
			else {
				$x = $text['left'];
			}

			if ($text['top'] == 0) {
				$y = intval($this->height/2 - $this->textHeight($text)/2);
				if (!is_int($text['font'])) $y = $y + intval($this->textHeight($text)/2);
			}
			elseif ($text['top'] < 0) {
				$y = $this->height + $text['top'];
				if (is_int($text['font'])) $y = $y - $this->textHeight($text);
			}
			else {
				$y = $text['top'];
				if (!is_int($text['font'])) $y = $y + $this->textHeight($text);
			}		
			
			if (is_int($text['font'])) {
				imagestring($this->dst_img, $text['font'], $x, $y, $text['text'], $textcolor);
			} 
			else {
				imagefttext($this->dst_img, $text['size'], $text['angle'], $x, $y, $textcolor, $text['font'], $text['text']);
			}
		}
		// ------------------------------------------
		
		// ------------------------------------------
		// наложение водяных знаков
		foreach ($this->watermark as $wm) {
			if (!file_exists($wm['src']) || !is_file($wm['src'])) continue;
			
			$img = self::create($wm['src'])->setBgcolor(false);
			
			//$img->display();
			//die();
			
			if ($wm['size'] == self::WM_TILE) {
				
				if ($wm['width'])  $img->setWidth($this->checkPercent($wm['width'],$this->width));
				if ($wm['height']) $img->setHeight($this->checkPercent($wm['height'],$this->height));				
				
				$y = 0;
				while ($y < $this->height)
				{		
					$x = 0;
					while($x < $this->width)
					{
						self::imagecopymerge_alpha($this->dst_img, $img->getImage(), $x, $y, 0, 0, $img->getWidth(), $img->getHeight(), $wm['alpha']);
						$x += $img->getWidth();
					}
					$y += $img->getHeight();
				}
				
			}
			else {
				
				if ($wm['size'] == self::WM_STRETCH) {
					$x = 0; $y = 0;
					$img->setWidth($this->width)->setHeight($this->height)->setAspect(0);
				}
				elseif ($wm['size'] == self::WM_FIT) {
					$x = 0; $y = 0;
					$img->setWidth($this->width)->setHeight($this->height)->setFit(self::FIT_CROP_CENTER);
				}		
				elseif ($wm['size'] == self::WM_CONTAIN) {
					$x = 0; $y = 0;
					$img->setWidth($this->width)->setHeight($this->height)->setFit(self::FIT_CONTAIN_CENTER);
				}				
				else {	

					if ($wm['width'])  $img->setWidth($this->checkPercent($wm['width'],$this->width));
					if ($wm['height']) $img->setHeight($this->checkPercent($wm['height'],$this->height));
				
					$wm['left'] = $this->checkPercent($wm['left'],$this->width);
					$wm['top']  = $this->checkPercent($wm['top'],$this->height);
				
					if ($wm['left'] == 0) {
						$x = intval($this->width/2 - $img->getWidth()/2);
					}
					elseif ($wm['left'] < 0) {
						$x = $this->width - $img->getWidth() + $wm['left'];
					}
					else {
						$x = $wm['left'];
					}

					if ($wm['top'] == 0) {
						$y = intval($this->height/2 - $img->getHeight()/2);

					}
					elseif ($wm['top'] < 0) {
						$y = $this->height - $img->getHeight() + $wm['top'];
					}
					else {
						$y = $wm['top'];
					}
				}

				self::imagecopymerge_alpha($this->dst_img, $img->getImage(), $x, $y, 0, 0, $img->getWidth(), $img->getHeight(), $wm['alpha']);
			}
		}
		// ------------------------------------------
		
		$this->isDirty = false;
		return $this;
	}
	
	protected function checkPercent($value, $base_value)
	{
		if (substr($value,-1) != '%') return $value;
		return intval(substr($value,0,-1)*$base_value/100);
	}
	
	protected function textWidth($text)
	{
		if (is_int($text['font'])) 
			return strlen($text['text'])*imagefontwidth($text['font']);
		
		$bbox = imageftbbox($text['size'], $text['angle'], $text['font'], $text['text']);
		return $bbox[2]-$bbox[0];
	}
	
	protected function textHeight($text)
	{
		if (is_int($text['font'])) 
			return imagefontheight($text['font']);
		
		$bbox = imageftbbox($text['size'], $text['angle'], $text['font'], $text['text']);
		
		return $bbox[1]-$bbox[7];
	}	
	
	public function save($dst = null)
	{
		$this->transform();
		
		if ($this->functionout == 'imagepng')
		{
			$quality = round((100-$this->quality)/10-1);
		}
		else
		{
			$quality = $this->quality;
		}

		if ($dst) {
			$p = pathinfo($dst);		
			if (!is_dir($p['dirname'])) mkdir($p['dirname'], 0777, true);
		}	
		else {
			header('Content-type: '.$this->src_info['mime']);
		}
		
		$functionout = $this->functionout;		
		$functionout($this->dst_img, $dst, $quality);				
	}
	
	public function display()
	{
		$this->save();
		return $this;
	}
	
	public function getImage()
	{
		$this->transform();
		return $this->dst_img;
	}	
	
	public function getWidth()
	{
		$this->transform();
		return $this->width;
	}

	public function getHeight()
	{
		$this->transform();
		return $this->height;
	}		
    
	/*
	* @deprecated
	*/
	public static function image($src, $dst, $width = 0, $height = 0, $quality = 75, $dontenlarge = 0, $aspect = 1, $fit = 0, $face = 0)
	{
		$img = new self($src);
		$img->setEnlarge(!$dontenlarge)
		    ->setWidth($width)
			->setHeight($height)
			->setQuality($quality)
			->setAspect($aspect)
			->setFit($fit)
			->save($dst);
	}	
	
	public static function transformFromURI()
	{
		$a = Application::getInstance();
		
		$nostore = false;
		
		$path = explode('/', $a->getUnparsedUrl() );
		$params = explode('_', array_shift($path));
		$file = implode('/', $path);

		
		if ($file) {		
			if (!file_exists(WWWROOT.$file)) $nostore = true;		
			$img = new self(WWWROOT.$file);
		}
		else {
			$nostore = true;
			$img = new self();
		}
		
		if ($a->getVar('default_image')) {
			$img->setDefault(WWWROOT.$a->getVar('default_image'));
		}
		
		for ($i = 0; $i < count($params); $i = $i+2)
		{
			if ($params[$i] == 'watermark') {
				$var = 'watermark';
				if (intval($params[$i+1])) {
					$var .= '_'.intval($params[$i+1]);
				}
				else {
					$i--;
				}
				
				$wm = $a->getVar($var);
				if (is_array($wm)) {
					$img->addWatermark(WWWROOT.$wm['src'], $wm);
				}
				
				if ($wm['nostore']) $nostore = true;
				
				continue;
			}
			$method = 'set'.ucfirst($params[$i]);
			if (method_exists($img, $method)) {
				$img->$method($params[$i+1]);
			}
		}
	
		if ($nostore) {
			$img->display();
		}
		else {
			$p = parse_url(urldecode($_SERVER['REQUEST_URI']));
			$img->save(WWWROOT.$p['path']);		
			header('Location: '.$_SERVER['REQUEST_URI'] );
		}
		
		die();
	}	
    
    public static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
       
        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
       
        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    } 	
}
