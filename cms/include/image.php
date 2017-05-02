<?php
/**
 * 
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
if (!defined('CMS_DIR')) {

    include( __DIR__ . '/constants.php' );
    
    $src = isset($_GET['src'])?urldecode($_GET['src']):'';
    $width  = isset($_GET['width'])?(int)$_GET['width']:0;
    $height = isset($_GET['height'])?(int)$_GET['height']:0;
    $quality = isset($_GET['quality'])?(int)$_GET['quality']:100;
    $dontenlarge = isset($_GET['dontenlarge'])?(int)$_GET['dontenlarge']:0;
    $aspect = isset($_GET['aspect'])?(int)$_GET['aspect']:1; // вписать картинку в размеры без сохранения пропорций
    $fit = isset($_GET['fit'])?(int)$_GET['fit']:0; // вписать картинку в размеры с сохранением пропорций (лишнее обрезается)
    $face = isset($_GET['face'])?(int)$_GET['face']:0;
	  $default = isset($_GET['default'])?DOCROOT.$_GET['default']:NULL;

    $res = image( DOCROOT.$src, $width, $height, $quality, $dontenlarge, $aspect, $fit, $face, $default);
                          
    header('Content-type: '.$res['mime']);
    readfile($res['file']);
    exit();
}


function image($src = '', $width = 0, $height = 0, $quality = 100, $dontenlarge = 0, $aspect = 1, $fit = 0, $face = 0, $default = NULL) {

    $cachedir = CACHE_DIR.'/images/';

    if ($dontenlarge) $enlarge = 0; else $enlarge = 1;

    $src_exists = file_exists($src) && is_file($src);
    
	if (!$src_exists && $default && file_exists($default) && is_file($default)) {
		$src = $default;
		$src_exists = true;
	}	
	
    if ($src_exists) {
		
		$pi = pathinfo($src);
		if ($pi['extension'] == 'svg') {
			return array(
			   'mime' => 'image/svg+xml',
			   'file' => $src
			);			
		}		
		
        $info = getimagesize($src);
        if (!$info) die();
        if (!$width && !$height) {
            $width = $info[0];
            $height = $info[1];
        } else { 
            if (!$width)  if ($aspect) $width = 10000; else $width = $info[0];
            if (!$height) if ($aspect) $height = 10000; else $height = $info[1];
        }
            
    } else {
				
        if (!$width && !$height) {
            $width = 256;
            $height = 256;
        } else { 
            if (!$width)  $width = $height;
            if (!$height) $height = $width;
        }
        $face = 0;
    }
    
    if ($src_exists && $info[0]<$width && $info[1]<$height && !$enlarge && !$fit) {
         return array(
             'mime' => $info['mime'],
             'file' => $src
         );
    }

    $filetime = ($src_exists)?filemtime($src):9999999999;
    $csubdir = 'img_'.substr(md5($src),0,1);
    
    if (!is_dir($cachedir.$csubdir)) mkdir($cachedir.$csubdir, 0777, true);
    $cachedir = $cachedir.$csubdir.'/';
    $src2 = str_replace('/','_',$src);
    $src2 = str_replace('\\','_',$src2);
    $src2 = str_replace(':','',$src2);
    $cachebasename = ((!$src_exists)?'not_found_':'').$width.'x'.$height.'_'.$aspect.'_'.$dontenlarge.'_'.$fit.'_'.$face.'_'.$quality.'_'.$src2;
    $cachename = $cachedir.$cachebasename;
    $cachetime = (file_exists($cachename))?filemtime($cachename):0;
    
    if ($cachetime > $filetime) {
        $info1 = getimagesize($cachename);
    	if (!$info1) die();
        if ($info1[0]==$width || $info1[1]==$height) {
             return array(
                 'mime' => $info1['mime'],
                 'file' => $cachename
             );
        }
    }
	
    if ($src_exists) {
    
        if ($info[2] >= 4) return die('No support for '.$info['mime']);
        
        switch ($info[2]) {
            case 1: $suf = 'gif'; break;
            case 2: $suf = 'jpeg'; break;
            case 3: $suf = 'png'; break;
        }
               
        $ox = 0;
        $oy = 0;
		$dx = 0;
		$dy	= 0;
        
        $w = $info[0]/$width;
        $h = $info[1]/$height;
        
        if ($aspect && !$fit) {     
          if ($w > $h) {
              $new_w = $width;
              $new_h = intval($info[1]*$new_w/$info[0]);
          } else {
              $new_h = $height;
              $new_w = intval($info[0]*$new_h/$info[1]);
          }
        } else {
            $new_w = $width;
            $new_h = $height;
        }
		
        $res_w = $new_w;
        $res_h = $new_h;
		
        if ($new_w==$info[0] && $new_h==$info[1]) {
             return array(
                 'mime' => $info['mime'],
                 'file' => $src
             );
        }
        if ($fit && $aspect)
		{
			if ($fit == 3)
			{
				if ($w > $h)
				{			
					$dy = round(($height - $info[1]/$w)/2);
					$new_h = round ($info[1] / ($info[0] / $width) );
				} 
				else
				{	  
					$dx = round(($width - $info[0]/$h)/2);
					$new_w = round ($info[0] / ($info[1] / $height) );
				}
			}
			else
			{
				if ($w > $h)
				{			  
				  $new0 = intval($info[1]*$width/$height);
				  if ($fit == 2) $ox = round( ($info[0] - $new0)/2 );			  
				  $info[0] = $new0;
				} 
				else
				{	  
				  $new1 = intval($info[0]*$height/$width);
				  if ($fit == 2) $oy = round( ($info[1] - $new1)/2 );
				  $info[1] = $new1;
				}				
			}
        }
    } else {
		
        $suf = 'png';
        $new_w = $width;
        $new_h = $height;
		
    }
    
    $function    = 'imagecreatefrom'.$suf;
  
    if (isset($_GET['format']) && $_GET['format']) {
        switch ($_GET['format']) {
            case 'gif': $suf = 'gif'; break;
            case 'jpg': $suf = 'jpeg'; break;
            case 'png': $suf = 'png'; break;
        }
    }
   
    $functionout = 'image'.$suf;
    
    if (!function_exists($function)) die('Undefined function: '.$function);
    
    if ($new_w<1) $new_w = 1;
    if ($new_h<1) $new_h = 1;
    $dst_img = imagecreatetruecolor($res_w,$res_h);
    if (!$dst_img) die('Error');
    $white = imagecolorallocate($dst_img , 255, 255, 255);
    imagefill($dst_img, 1, 1, $white);
    
    if ($src_exists) {
		
      $src_img = $function($src);
      if (!$src_img) die('Error');  
      ImageCopyResampled($dst_img,$src_img,$dx,$dy,$ox,$oy,$new_w,$new_h,$info[0],$info[1]);
	  
    } else {
		
      $color = imagecolorallocate($dst_img, 100,100,100);
      imagerectangle ($dst_img, 0,0,$new_w-1,$new_h-1,$color);
      $f = 5;
      $text = 'Image not found';
      while ($f > 0 && (2*imagefontheight($f)>$new_h || strlen($text)*imagefontwidth($f)>$new_w || strlen($src)*imagefontwidth($f)>$new_w)) $f--;
      
      $ch = imagefontheight($f)*2;
      $h = 0;
      if (file_exists(CMS_DIR.'/images/cmslogo_small.gif')) {
        $src_img = imagecreatefromgif(CMS_DIR.'/images/cmslogo_small.gif');
        $w = imagesx($src_img); 
        $h = imagesy($src_img);
        $ch = $h+imagefontheight($f)*2+5;
        if ($ch > $new_h) {
          $ch = imagefontheight($f)*2;
          $h = 0;
        } else {
          $x = round(($new_w-$w)/2);
          $y = round(($new_h-$ch)/2);
          ImageCopyResampled($dst_img,$src_img,$x,$y,0,0,$w,$h,$w,$h);
          $h = $h+5;
        }
		
      }
      
	  /*
      $y = round(($new_h-$ch)/2)+$h;
      $x = round($new_w-strlen($text)*imagefontwidth($f))/2;
      imagestring($dst_img, $f, $x, $y, $text, $color);
      $x = round($new_w-strlen($src)*imagefontwidth($f))/2;
      imagestring($dst_img, $f, $x, $y+imagefontheight($f), $src, $color);
	  */
      
      $info['mime'] = 'image/png';
    }
    
    $writable = (file_exists($cachename))?is_writable($cachename):is_writable($cachedir);
    
    if ($functionout == 'imagepng') {
    	$quality = round((100-$quality)/10-1);
    }
    if ($writable) {
    	$functionout($dst_img, $cachename, $quality);
    }

    return array(
       'mime' => $info['mime'],
       'file' => $cachename
    );

}