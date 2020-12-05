<?php
namespace Cetera;
/**
 * Fastsite CMS 3
 * 
 * Список файлов   
 *
 * @package FastsiteCMS
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
        'name'  => $translator->_('Модули'),
        'items' => array()
    ),            
);

foreach ($application->getBo()->getModules() as $id => $component) {

  $root_folder = '/'.CMS_DIR.'/';
  if (isset($component['path'])) $root_folder = $component['path'].'/';
          
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
  
  if (isset($component['url']))  $component['url']  = truePath($component['url'],$root_folder);
  if (isset($component['icon'])) $component['icon'] = truePath($component['icon'],$root_folder);
  if (isset($component['html'])) $component['html'] = truePath($component['html'],$root_folder);
  if (!isset($component['tree'])) $component['tree'] = 'catalogs';
  $components[$id] = $component;   
  
  if (isset($component['submenu']) && is_array($component['submenu'])) 
     foreach ($component['submenu'] as $ii => $menu_subitem) {
     
          if (isset($menu_subitem['url'])) $menu_subitem['url'] = truePath($menu_subitem['url'],$root_folder);
          if (isset($menu_subitem['icon'])) $menu_subitem['icon'] = truePath($menu_subitem['icon'],$root_folder);
          if (isset($menu_subitem['html'])) $menu_subitem['html'] = truePath($menu_subitem['html'],$root_folder);
          if (isset($menu_subitem['tree'])) $menu_subitem['tree'] = 'catalogs'; 
          
          $components[$id.'_'.$ii] = $menu_subitem;              
     }                 

}
ksort($menu);

header('Content-Type: application/json; charset=UTF-8');

echo json_encode(array(
    'modules' => $components,
    'menu'    => $menu,
    'scripts' => $application->getBo()->getScripts()
));

function truePath($path, $root) {
    if (substr($path,0,1) == '/') {
        return $path;
    }
    return $root.$path;
}