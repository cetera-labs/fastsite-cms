<?php
namespace Cetera;
include('common_bo.php');

$path = $_REQUEST['path'];
$size = getimagesize($_SERVER['DOCUMENT_ROOT'].$path);
if (!$size) die();
?>
<div class="img_preview">
    <div>
        <img src="/<?=CMS_DIR?>/include/image.php?src=<?=$path?>&width=230&height=250&cache=1&dontenlarge=1&quality=90" />
    </div>
    <br /><?=$size[0]?>x<?=$size[1]?>px
</div>
