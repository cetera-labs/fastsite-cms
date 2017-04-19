<?php
namespace Cetera;
include('common_bo.php');

$path = $_REQUEST['path'];

if (file_exists(WWWROOT.$path))  {
    $size = getimagesize(WWWROOT.$path);
}

if ($size) {
    echo json_encode(array(
        'success' => true,
        'data'    => $size,
        'size'    => normal_size(filesize(WWWROOT.$path))
    ));
} else {
    echo json_encode(array(
        'success' => false
    ));
}

function normal_size($size) {
   $kb = 1024;         // Kilobyte
   $mb = 1024 * $kb;   // Megabyte
   $gb = 1024 * $mb;   // Gigabyte
   $tb = 1024 * $gb;   // Terabyte
   if($size < $kb) {
       return $size." B";
   }
   else if($size < $mb) {
       return round($size/$kb,2)." Kb";
   }
   else if($size < $gb) {
       return round($size/$mb,2)." Mb";
   }
   else if($size < $tb) {
       return round($size/$gb,2)." Gb";
   }
   else {
       return round($size/$tb,2)." Tb";
   }
}