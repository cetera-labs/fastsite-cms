<?php
namespace Cetera;
include('../include/common_bo.php');

$widget = $application->getWidget( (int)$_REQUEST['widgetId'] );

if ($widget->widgetProtected) throw new \Exception( $translator->_('Виджет защищен.') );
  
if ( (int)$_REQUEST['containerId'] ) {

    $widget->removeFromContainer((int)$_REQUEST['containerId'], true);

} else {
  
    $widget->delete();
    
}

echo json_encode(array(
    'success' => true
));