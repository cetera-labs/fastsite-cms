<?php
namespace Cetera;
include('../include/common_bo.php');

$widget = $application->getWidget( (int)$_REQUEST['id'] ? (int)$_REQUEST['id'] : $_REQUEST['widgetName'] );
$containerId = (int)$_REQUEST['container_id'];

if ($_REQUEST['widgetName'] != 'Container' || !$_REQUEST['container_id']) {
    
    if (isset($_REQUEST['widgetAlias']))
        $widget->widgetAlias = $_REQUEST['widgetAlias'];
    
    if (isset($_REQUEST['widgetTitle']))
        $widget->widgetTitle = $_REQUEST['widgetTitle'];
    
    unset($_REQUEST['widgetName'], $_REQUEST['id'], $_REQUEST['widgetAlias'], $_REQUEST['widgetTitle'], $_REQUEST['container_id']);
    
    foreach ($_REQUEST as $name => $value)
        $widget->setParam($name, $value);
        
    $widget->save();
    
}

if ($widget->getId() && $containerId)
    $widget->addToContainer($containerId);


echo json_encode(array(
    'success'       => true,
    'id'            => $widget->getId(),
    'widgetName'    => $widget->widgetName,
    'widgetDescrib' => $widget->widgetDescrib,
    'widgetAlias'   => $widget->widgetAlias,
    'widgetTitle'   => $widget->widgetTitle
));