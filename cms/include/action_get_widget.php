<?php
namespace Cetera;
include('../include/common_bo.php');

$widget = $application->getWidget( (int)$_REQUEST['id'] ? (int)$_REQUEST['id'] : $_REQUEST['widgetName'] );

$res = array(
    'success'     => true,
    'widgetId'    => $widget->getId(),
    'widgetName'  => $widget->widgetName,
    'widgetTitle' => $widget->widgetTitle,
    'widgetAlias' => $widget->widgetAlias,
    'widgetProtected' => (bool)$widget->widgetProtected,
    'params'      => $widget->getParams()
);

if ($widget instanceof \Cetera\Widget\Container) {
    $res['widgets'] = array();
    foreach ($widget->getChildren() as $w)
        
        $res['widgets'][] = array(
            'widgetId'     => $w->getId(),
            'widgetName'   => $w->widgetName,
            'widgetAlias'  => $w->widgetAlias,
            'widgetTitle'  => $w->widgetTitle,
            'widgetDisabled'=> (bool)$w->widgetDisabled,
            'widgetProtected' => (bool)$widget->widgetProtected,
            'params'       => $w->getParams()
        );
        
}

echo json_encode($res);