<?php
namespace Cetera;

if (!isset($application)) {
    require_once('include/common.php');
    $application = Application::getInstance();
    
    $application->connectDb();
    $application->initSession();
    $application->initPlugins();
}

if (isset($_REQUEST['preview']))
    $application->setPreviewMode(true);

$GLOBALS['field_prefix'] = 1;

set_exception_handler('api_exception_handler');
header('Content-Type:	application/json; charset=UTF-8');

check_params(array('mode'));

if ($_REQUEST['mode'] == 'material') {

    check_params(array('id','catalog'));
       
    $catalog = Catalog::getById((int)$_REQUEST['catalog']);
    
    $material = $catalog->getMaterialById((int)$_REQUEST['id']);
       
    $res = array(
        'success'  => true,
        'data'     => get_material($material)
    );
     
}

if ($_REQUEST['mode'] == 'materials') {

    check_params(array('id'));
    
    $catalog = Catalog::getById($_REQUEST['id']);
    $fields = get_fields(array('id', 'name', 'alias'));
    
    $count = (int)$_REQUEST['count'];
    if (!$count) $count = 1000;
    $page = (int)$_REQUEST['page'];
    if (!$page) $page = 1;
    
    $sort = check_sql($_REQUEST['sort']);
    $where = check_sql($_REQUEST['where']);
    if (!$sort) $sort = 'dat DESC';
    $link_where = check_sql($_REQUEST['link_where']);
    
    $materials = $catalog->getMaterials('*',$where,$sort,'',($page-1)*$count.','.$count,false,$link_where);
    
    $res = array(
        'success'  => true,
        'data'     => array()
    );  
    
    foreach($materials as $m) $res['data'][] = get_material($m);    
    
}

if ($_REQUEST['mode'] == 'catalog') {
    check_params(array('id'));
    
    if (is_array($_REQUEST['id']))
        $array = $_REQUEST['id']; 
        else $array = explode(',', $_REQUEST['id']); 
  
    $res = array(
        'success'  => true,
        'data'     => enum_catalogs($array, get_fields(array('id', 'name', 'alias')))
    );     
    
}

if ($_REQUEST['mode'] == 'children') {
// Дочерние разделы

    check_params(array('id'));
    
    $catalog = Catalog::getById($_REQUEST['id']);
    $res = array(
        'success'  => true,
        'data'     => enum_catalogs($catalog->children, get_fields(array('id', 'name', 'alias')))
    );

}

if (isset($res)) {

    echo json_encode($res);
    
} else {

    throw new Exception('Неправильное значение "mode='.$_REQUEST['mode'].'"');
    
}

function check_sql($str) {
    $str = strtolower($str);
    $str = str_replace('union', '', $str);
    $str = str_replace('select', '', $str);
    $str = str_replace('truncate', '', $str);
    $str = str_replace('drop', '', $str);
    $str = str_replace('replace', '', $str);
    $str = str_replace('insert', '', $str);
    $str = str_replace('update', '', $str);
    $str = str_replace('table', '', $str);
    $str = str_replace('join', '', $str);
    $str = str_replace('file', '', $str);
    $str = str_replace('where', '', $str);
    $str = str_replace('delete', '', $str);
    $str = str_replace('from', '', $str);
    return $str;
}

function get_material($material) {
        
    $prefix = $GLOBALS['field_prefix'];
    
    if ($GLOBALS['field_prefix'] > 1) 
        $fparam = 'fields'.$GLOBALS['field_prefix'];
        else $fparam = 'fields';
         
    $res = array();
         
    foreach (get_fields(array('id', 'name', 'alias'), $fparam) as $f) {

        $a = explode(' as ', $f);
        if (isset($a[1])) $f = $a[1];
         
        if (is_array($material->$f) || is_a($material->$f, 'Cetera\\Iterator\\Object')) {
        
            $res[$f] = array();
            $GLOBALS['field_prefix']++;
            foreach ($material->$f as $m)
                $res[$f][] = get_material($m);  
                
        } elseif (is_object($material->$f)) {
        
            $GLOBALS['field_prefix']++;
            $res[$f] = get_material($material->$f);
            
        } else {
        
            $res[$f] = $material->$f;
            
            if ($f == 'dat') {
                $d = date_parse_from_format("Y-m-d H:i:s", $res[$f]);
                $res[$f] =  mktime ($d['hour'],$d['minute'],$d['second'],$d['month'],$d['day'],$d['year']);
            }
            
            if (!$res[$f] && !is_int($res[$f])) $res[$f] = null;
        }
        
    } 

    $GLOBALS['field_prefix'] = $prefix;
    return $res;
    
}

function get_fields($default, $param = 'fields') {

    if (isset($_REQUEST[$param])) {
        if (is_array($_REQUEST[$param]))
            $fields = $_REQUEST[$param];
            else $fields = explode(',',$_REQUEST[$param]);
        $fields[] = 'id';
        $fields = array_unique($fields);
    } else {
        $fields = $default;
    }
    
    foreach ($fields as $id => $val) if ($val == 'dat') $fields[$id] = 'UNIX_TIMESTAMP('.$val.') as '.$val;
    
    return $fields;
}

function enum_catalogs($array, $fields) {
    $res = array();
    foreach($array as $c) {
    
        if (!is_object($c)) $c = Catalog::getById((int)$c);
    
        $data = array();
        foreach ($fields as $f) $data[$f] = $c->$f;    
        $res[] = $data;
    
    } 
    return $res;
}

function check_params($params) {

    foreach ($params as $p) {
    
        if (!isset($_REQUEST[$p])) throw new Exception('Не задан обязательный параметр "'.$p.'"');
    
    }

}

function api_exception_handler($exception) {
                
    $res = array(
        'success'     => false,
        'message'     => $exception->getMessage()
    );
    echo json_encode($res);

}
