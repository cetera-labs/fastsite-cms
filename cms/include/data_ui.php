<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Список файлов   
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
include('common_bo.php');

$components = array(
    'welcome' => array(
        'name'     => $translator->_('Добро пожаловать'), 
        'html'     => 'include/welcome_new.php', 
        'icon'     => '../images/cmslogo_small.gif', 
        'toolbar'  => 0    
    )
);

$menu = array(
    MENU_SERVICE => array(
        'name'  => $translator->_('Сервис'),
        'items' => array()
    ),
    MENU_SITE => array(
        'name'  => $translator->_('Сайт'),
        'items' => array()
    ),
    MENU_PLUGINS => array(
        'name'  => $translator->_('Плагины'),
        'items' => array()
    ),            
);

foreach ($application->getBo()->getModules() as $id => $component) {

  $root_folder = '/'.CMS_DIR.'/';
  if ($component['path']) $root_folder = $component['path'].'/';
          
  $component['id'] = $id;          
          
  if ($component['position']) {
      if (isset($menu[$component['position']])) {
          $menu[$component['position']]['items'][] = $component;
      } else {
          $menu[$component['position']] = array(
              'name'  => isset($component['position_name'])?$component['position_name']:$component['name'],
              'items' => array($component)              
          );
      }    
  } else {
      $menu[MENU_PLUGINS]['items'][] = $component;
  }
  
  if ($component['url'])  $component['url']  = $root_folder.$component['url'];
  if ($component['icon']) $component['icon'] = $root_folder.$component['icon'];
  if ($component['html']) $component['html'] = $root_folder.$component['html'];
  if (!$component['tree']) $component['tree'] = 'catalogs';
  $components[$id] = $component;   
  
  if (isset($component['submenu']) && is_array($component['submenu'])) 
     foreach ($component['submenu'] as $ii => $menu_subitem) {
     
          if ($menu_subitem['url']) $menu_subitem['url'] = $root_folder.$menu_subitem['url'];
          if ($menu_subitem['icon']) $menu_subitem['icon'] = $root_folder.$menu_subitem['icon'];
          if ($menu_subitem['html']) $menu_subitem['html'] = $root_folder.$menu_subitem['html'];
          if ($menu_subitem['tree']) $menu_subitem['tree'] = 'catalogs'; 
          
          $components[$id.'_'.$ii] = $menu_subitem;              
     }                 

}
ksort($menu);



echo json_encode(array(
    'modules' => $components,
    'menu'    => $menu,
    'scripts' => $application->getBo()->getScripts()
));