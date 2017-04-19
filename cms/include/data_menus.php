<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

include('common_bo.php');

$nodes = array();

foreach (Menu::enum() as $menu) {

    $children = array();
    foreach ($menu->children as $c) {
    
		$link = 0;
		if (is_a($c,'Cetera\\ExternalLink'))
		{
			$name = $c->name.' ['.$c->url.']';
			$data = 'url-'.str_replace('-','%DASH%',$c->url).'-name-'.str_replace('-','%DASH%',$c->name);
			$icon = '';
			$link = 1;
		}
        elseif (is_a($c,'Cetera\\Catalog'))
		{
            $name = '<span class="tree-alias">'.$c->alias.'</span>'.$c->name;
            $data = 'item-'.$c->id;
            if ($c->isServer())
                $icon = 'tree-server';
                else $icon = 'tree-folder-visible';
        } 
		else
		{
            $name = $c->name;
            $data = 'material-'.$c->id.'-'.$c->table.'-'.$c->type;
            $icon = 'tree-material';        
        }
    
        $children[] = array(
            'name'    => $name,
            'data'    => $data,
            'iconCls' => $icon,
            'leaf'    => true,
			'link'    => $link,
        );        
    }

    $nodes[] = array(
        name      => '<b>' . $menu->name . '</b>' . ' ['.$menu->alias.'][ID:' . $menu->id . ']',
        menu      => $menu->id,
        menu_name => $menu->name,
        menu_alias=> $menu->alias,
        allowDrag => false,
        icon      => 'images/icon_menu.png',
        children  => $children,
        expanded  => true
    );

}

echo json_encode($nodes);