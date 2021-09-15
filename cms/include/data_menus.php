<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

include('common_bo.php');

$nodes = [];

foreach (Menu::enum() as $menu) {

    $nodes[] = [
        'name'      => '<b>' . $menu->name . '</b>' . ' ['.$menu->alias.'][ID:' . $menu->id . ']',
        'menu'      => $menu->id,
        'menu_name' => $menu->name,
        'menu_alias'=> $menu->alias,
        'allowDrag' => false,
        'icon'      => 'images/icon_menu.png',
        'iconCls'   => 'x-fa fa-bars',
        'children'  => get_children($menu),
        'expanded'  => true
    ];

}

echo json_encode($nodes);

function get_children($node) {
    
    $children = [];
    foreach ($node->getChildren() as $c) {
    
		$link = 0;
        $subs = [];
		if (is_a($c,'Cetera\\ExternalLink'))
		{
			$name = $c->name.' ['.$c->url.']';
			$data = 'url-'.str_replace('-','%DASH%',$c->url).'-name-'.str_replace('-','%DASH%',$c->name);
			$icon = 'tree-folder-link';
			$link = 1;
            $subs = get_children($c);
		}
        elseif (is_a($c,'Cetera\\Catalog')) {
            $name = '<span class="tree-alias">'.$c->alias.'</span>'.$c->name;
            $data = 'item-'.$c->id;
            if ($c->isServer())
                $icon = 'tree-server';
                else $icon = 'tree-folder-visible';
        } 
		else {
            $name = $c->name;
            $data = 'material-'.$c->id.'-'.$c->table.'-'.$c->type;
            $icon = 'tree-material';        
        }
    
        $children[] = [
            'name'    => $name,
            'data'    => $data,
            'iconCls' => $icon,
            'leaf'    => (boolean)(1-$link),
			'link'    => $link,
            'expanded'=> true,
            'children'=> $subs,
        ];        
    }
    
    return $children;
}