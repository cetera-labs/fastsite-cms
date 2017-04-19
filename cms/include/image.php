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
        
        if ($face) {
            $function = 'imagecreatefrom'.$suf;
            $detector = new Face_Detector('detection.dat');
            $detector->face_detect($function($src));
            $face_data = $detector->getFace();
            if ($face_data['w'] == 0) $face = 0;
        }
        
        $ox = 0;
        $oy = 0;
		$dx = 0;
		$dy	= 0;
        
        if ($face) {
            $ox = $face_data['x'];
            $oy = $face_data['y'];
            
            $info[0] = $face_data['w'];
            $info[1] = $face_data['w'];
        }
        
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

class Face_Detector {
    
    protected $detection_data;
    protected $canvas;
    protected $face;
    private $reduced_canvas;
    
    public function __construct($detection_file = 'detection.dat') {
        if (is_file($detection_file)) {
            $this->detection_data = unserialize(file_get_contents($detection_file));
        } else {
            throw new Exception("Couldn't load detection data");
        }
        //$this->detection_data = json_decode(file_get_contents('data.js'));
    }
    
    public function face_detect($canvas) {
        
        $this->canvas = $canvas;
        $im_width = imagesx($this->canvas);
        $im_height = imagesy($this->canvas);

        //Resample before detection?
        $ratio = 0;
        $diff_width = 320 - $im_width;
        $diff_height = 240 - $im_height;
        if ($diff_width > $diff_height) {
            $ratio = $im_width / 320;
        } else {
            $ratio = $im_height / 240;
        }

        if ($ratio != 0) {
            $this->reduced_canvas = imagecreatetruecolor($im_width / $ratio, $im_height / $ratio);
            imagecopyresampled($this->reduced_canvas, $this->canvas, 0, 0, 0, 0, $im_width / $ratio, $im_height / $ratio, $im_width, $im_height);
            
            $stats = $this->get_img_stats($this->reduced_canvas);
            $this->face = $this->do_detect_greedy_big_to_small($stats['ii'], $stats['ii2'], $stats['width'], $stats['height']);
            $this->face['x'] *= $ratio;
            $this->face['y'] *= $ratio;
            $this->face['w'] *= $ratio;
        } else {
            $stats = $this->get_img_stats($this->canvas);
            $this->face = $this->do_detect_greedy_big_to_small($stats['ii'], $stats['ii2'], $stats['width'], $stats['height']);
        }
        return ($this->face['w'] > 0);
    }
    
    
    public function toJpeg() {
        $color = imagecolorallocate($this->canvas, 255, 0, 0); //red
        imagerectangle($this->canvas, $this->face['x'], $this->face['y'], $this->face['x']+$this->face['w'], $this->face['y']+ $this->face['w'], $color);
        header('Content-type: image/jpeg');
        imagejpeg($this->canvas);
    }
    
    public function toJson() {
        return "{'x':" . $this->face['x'] . ", 'y':" . $this->face['y'] . ", 'w':" . $this->face['w'] . "}";
    }
    
    public function getFace() {
        return $this->face;
    }
    
    protected function get_img_stats($canvas){
        $image_width = imagesx($canvas);
        $image_height = imagesy($canvas);     
        $iis =  $this->compute_ii($canvas, $image_width, $image_height);
        return array(
            'width' => $image_width,
            'height' => $image_height,
            'ii' => $iis['ii'],
            'ii2' => $iis['ii2']
        );         
    }
    
    protected function compute_ii($canvas, $image_width, $image_height ){
        $ii_w = $image_width+1;
        $ii_h = $image_height+1;
        $ii = array();
        $ii2 = array();      
                                
        for($i=0; $i<$ii_w; $i++ ){
            $ii[$i] = 0;
            $ii2[$i] = 0;
        }                        
                                    
        for($i=1; $i<$ii_w; $i++ ){  
            $ii[$i*$ii_w] = 0;       
            $ii2[$i*$ii_w] = 0; 
            $rowsum = 0;
            $rowsum2 = 0;
            for($j=1; $j<$ii_h; $j++ ){
                $rgb = ImageColorAt($canvas, $j, $i);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;
                $grey = ( 0.2989*$red + 0.587*$green + 0.114*$blue )>>0;  // this is what matlab uses
                $rowsum += $grey;
                $rowsum2 += $grey*$grey;
                
                $ii_above = ($i-1)*$ii_w + $j;
                $ii_this = $i*$ii_w + $j;
                
                $ii[$ii_this] = $ii[$ii_above] + $rowsum;
                $ii2[$ii_this] = $ii2[$ii_above] + $rowsum2;
            }
        }
        return array('ii'=>$ii, 'ii2' => $ii2);
    }
    
    protected function do_detect_greedy_big_to_small( $ii, $ii2, $width, $height ){
        $s_w = $width/20.0;
        $s_h = $height/20.0;
        $start_scale = $s_h < $s_w ? $s_h : $s_w;
        $scale_update = 1 / 1.2;
        for($scale = $start_scale; $scale > 1; $scale *= $scale_update ){
            $w = (20*$scale) >> 0;
            $endx = $width - $w - 1;
            $endy = $height - $w - 1;
            $step = max( $scale, 2 ) >> 0;
            $inv_area = 1 / ($w*$w);
            for($y = 0; $y < $endy; $y += $step ){
                for($x = 0; $x < $endx; $x += $step ){
                    $passed = $this->detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $width+1, $inv_area);
                    if( $passed ) {
                        return array('x'=>$x, 'y'=>$y, 'w'=>$w);
                    }
                } // end x
            } // end y
        }  // end scale
        return null;
    }
    
    protected function detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $iiw, $inv_area){
        $mean = ( $ii[($y+$w)*$iiw + $x + $w] + $ii[$y*$iiw+$x] - $ii[($y+$w)*$iiw+$x] - $ii[$y*$iiw+$x+$w]  )*$inv_area;
        $vnorm =  ( $ii2[($y+$w)*$iiw + $x + $w] + $ii2[$y*$iiw+$x] - $ii2[($y+$w)*$iiw+$x] - $ii2[$y*$iiw+$x+$w]  )*$inv_area - ($mean*$mean);    
        $vnorm = $vnorm > 1 ? sqrt($vnorm) : 1;
        
        $passed = true;
        for($i_stage = 0; $i_stage < count($this->detection_data); $i_stage++ ){
            $stage = $this->detection_data[$i_stage];  
            $trees = $stage[0];  

            $stage_thresh = $stage[1];
            $stage_sum = 0;
                              
            for($i_tree = 0; $i_tree < count($trees); $i_tree++ ){
                $tree = $trees[$i_tree];
                $current_node = $tree[0];    
                $tree_sum = 0;
                while( $current_node != null ){
                    $vals = $current_node[0];
                    $node_thresh = $vals[0];
                    $leftval = $vals[1];
                    $rightval = $vals[2];
                    $leftidx = $vals[3];
                    $rightidx = $vals[4];
                    $rects = $current_node[1];
                    
                    $rect_sum = 0;
                    for( $i_rect = 0; $i_rect < count($rects); $i_rect++ ){
                        $s = $scale;
                        $rect = $rects[$i_rect];
                        $rx = ($rect[0]*$s+$x)>>0;
                        $ry = ($rect[1]*$s+$y)>>0;
                        $rw = ($rect[2]*$s)>>0;  
                        $rh = ($rect[3]*$s)>>0;
                        $wt = $rect[4];
                        
                        $r_sum = ( $ii[($ry+$rh)*$iiw + $rx + $rw] + $ii[$ry*$iiw+$rx] - $ii[($ry+$rh)*$iiw+$rx] - $ii[$ry*$iiw+$rx+$rw] )*$wt;
                        $rect_sum += $r_sum;
                    } 
                     
                    $rect_sum *= $inv_area;
                         
                    $current_node = null;
                    if( $rect_sum >= $node_thresh*$vnorm ){
                        if( $rightidx == -1 ) 
                            $tree_sum = $rightval;
                        else
                            $current_node = $tree[$rightidx];
                    } else {
                        if( $leftidx == -1 )
                            $tree_sum = $leftval;
                        else
                            $current_node = $tree[$leftidx];
                    }
                } 
                $stage_sum += $tree_sum;
            } 
            if( $stage_sum < $stage_thresh ){
                return false;
            }
        } 
        return true;
    }
}