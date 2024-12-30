<?php
namespace Cetera;

class Filter {

    const TYPE_NUMERIC        = 1;
    const TYPE_NUMERIC_SLIDER = 2;
    const TYPE_CHECKBOX       = 3;
    const TYPE_RADIO          = 4;
    const TYPE_DROPDOWN       = 5;
    const TYPE_TEXT           = 6;
    const TYPE_DATE           = 7;
    const TYPE_DATE_INTERVAL  = 8;
    const TYPE_DROPDOWN_MULTIPLE = 9;
    const TYPE_AUTO = 10;

    protected $iterator;
    protected $active = false;
    protected $info = null;
    protected $data = null;
    protected $a;
    public $name = false;

    protected $values = [];

    public function __construct($name, Iterator\DynamicObject $iterator, $values = false)
    {
        $this->iterator = $iterator;
        $this->name = $name;
        $this->a = \Cetera\Application::getInstance();
        $this->data = array();

        if (!$values && isset($_REQUEST[ $this->name ])) {
            $values = $_REQUEST[ $this->name ];
        }

        if (is_array($values)) {
            $this->values = $values;
        }
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    public function submittedValue($name)
    {
        return (isset($this->values[$name]) && $this->values[$name])?$this->values[$name]:null;
    }

    public function getQueryString()
    {
        return http_build_query(array($this->name => $this->values));
    }

    public function isActive()
    {
        $this->getInfo();
        return $this->active;
    }

    public function addField($field_name, $type = self::TYPE_CHECKBOX, $name = null)
    {
        if ($name === null) {
            $name = $field_name;
        }
        $this->data[] = array(
            'field' => $this->iterator->getObjectDefinition()->getField($field_name),
            'filter_type' => $type,
            'name' => $name,
        );
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function getInfo()
    {
        if (!$this->info)
        {
            $this->active = false;

            $this->info = [];
            foreach ($this->data as $d) {

                $d['describ'] = $this->a->decodeLocaleString( $d['field']['describ'] );
                $d['iterator'] = array();
                $d['submitted'] = false;
                $d['field_id'] = $d['field']['field_id'];

                if (is_subclass_of($d['field'], '\\Cetera\\ObjectFieldLinkSetAbstract')) {
                    //$d['iterator'] = $d['field']->getIterator()->joinReverse($this->iterator->getObjectDefinition(), $d['field']->name)->where($d['field']->name.'.id > 0')->groupBy('main.id');
                    $i = clone $this->iterator;
                    $i->setItemCountPerPage(0);
                    $d['iterator'] =  $d['field']
                        ->getIterator()
                        ->where('id IN (:values)')
                        ->setParameter('values', array_map( function($v){ return $v['dest']; }, $i->join($d['field']->name)->select($d['field']->name.'.dest')
                            ->groupBy($d['field']->name.'.dest')->asArray('dest') ), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

                    $d['value'] = $this->submittedValue($d['name']);
                    if ($d['value']) {
                        $this->active = true;
                        $d['submitted'] = true;
                    }

                }
                elseif (is_subclass_of($d['field'], '\\Cetera\\ObjectFieldLinkAbstract')) {
                    //$d['iterator'] = $d['field']->getIterator();
                    $i = clone $this->iterator;
                    $i->setItemCountPerPage(0);
                    $d['iterator'] =  $d['field']
                        ->getIterator()
                        ->where('id IN (:values)')
                        ->setParameter('values', array_map( function($v){ return $v['dest']; }, $i->select($d['field']->name.' as dest')->asArray('dest') ), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

                    $d['value'] = $this->submittedValue($d['name']);
                    if ($d['value']) {
                        $this->active = true;
                        $d['submitted'] = true;
                    }
                }
                elseif ($d['field'] instanceof \Cetera\ObjectField) {
                    if (
                        ($d['field']['type'] == FIELD_INTEGER || $d['field']['type'] == FIELD_DOUBLE)
                        &&
                        ($d['filter_type'] == self::TYPE_NUMERIC_SLIDER || $d['filter_type'] == self::TYPE_NUMERIC)
                    )
                    {
                        $list = clone $this->iterator;

                        $f = $this->generateField($d['field']);
                        $list->select(['MIN('.$f.') as _MIN_','MAX('.$f.') as _MAX_']);
                        $m = $list->current();
                        if (!$m) continue;
                        $d['min'] = $m->fields['_MIN_'];
                        $d['max'] = $m->fields['_MIN_'];
                        if ($d['min'] === NULL && $d['max'] === NULL) continue;
                        if ($d['max'] == $d['min']) $d['max'] += 10;

                        $d['value_min'] = $this->submittedValue($d['name'].'_min');
                        $d['value_max'] = $this->submittedValue($d['name'].'_max');
                        if($d['value_min'] === null) $d['value_min'] = $d['min'];
                        if($d['value_max'] === null) $d['value_max'] = $d['max'];
                        if ($d['value_min'] != $d['min'] || $d['value_max'] != $d['max'])
                        {
                            $this->active = true;
                            $d['submitted'] = true;
                        }
                    }
                    elseif ($d['field']['type'] == FIELD_BOOLEAN) {
                        if ($d['filter_type'] == self::TYPE_RADIO) {
                            $d['iterator'] = array(
                                array('id' => 0, 'name' => self::t()->_('да') ),
                                array('id' => 0, 'name' => self::t()->_('нет') )
                            );
                        }
                        else {
                            $d['iterator'] = false;
                            $d['filter_type'] = self::TYPE_CHECKBOX;
                        }
                        $d['value'] = $this->submittedValue($d['name']);
                        if ($d['value']) {
                            $this->active = true;
                            $d['submitted'] = true;
                        }
                    }
                    else {

                        if ($d['filter_type'] == self::TYPE_DATE_INTERVAL) {

                            $d['value_min'] = $this->submittedValue($d['name'].'_min');
                            $d['value_max'] = $this->submittedValue($d['name'].'_max');
                            if ($d['value_min'] != $d['min'] || $d['value_max'] != $d['max']) {
                                $this->active = true;
                                $d['submitted'] = true;
                            }

                        }
                        else {

                            $d['iterator'] = [];

                            $f = $this->generateField($d['field']);
                            $list = clone $this->iterator;
                            $list->select($f.' AS '.$d['name'])->orderBy($f)->groupBy($f, false)->setItemCountPerPage(0);

                            if (!$list->getCountAll()) continue;
                            foreach($list as $m) {
                                if (!$m->fields[$d['name']]) continue;
                                $d['iterator'][] = array(
                                    'id'   => $m->fields[$d['name']],
                                    'name' => $m->fields[$d['name']],
                                );
                            }
                            $d['value'] = $this->submittedValue($d['name']);
                            if ($d['value']) {
                                $this->active = true;
                                $d['submitted'] = true;
                            }

                            if (!count($d['iterator'])) continue;

                        }
                    }
                }
                $this->info[ $d['name'] ] = $d;
            }
        }
        return $this->info;
    }

    /*
     * применить фильтр к итератору
     */
    public function apply()
    {
        if (!$this->isActive()) return;

        foreach ($this->getInfo() as $f)
        {
            switch($f['filter_type'])
            {
                case self::TYPE_NUMERIC:
                case self::TYPE_NUMERIC_SLIDER:
                    if ($this->submittedValue($f['name'].'_min') !== null)
                    {
                        $this->iterator->where( $this->generateField($f['field']).' >= '.(float)$this->submittedValue($f['name'].'_min') );
                    }
                    if ($this->submittedValue($f['name'].'_max') !== null && $this->submittedValue($f['name'].'_max'))
                    {
                        $this->iterator->where( $this->generateField($f['field']).' <= '.(float)$this->submittedValue($f['name'].'_max') );
                    }
                    break;
                case self::TYPE_RADIO:
                case self::TYPE_DROPDOWN:
                    if ($this->submittedValue($f['name']) !== null) {
                        $this->iterator->where( $this->generateField($f['field']).' = :'.$f['name'] )
                            ->setParameter($f['name'], $this->submittedValue($f['name']));
                    }
                    break;
                case self::TYPE_CHECKBOX:
                    if ($this->submittedValue($f['name']) !== null) {
                        if (is_array($this->submittedValue($f['name']))) {
                            $a = array();
                            foreach ($this->submittedValue($f['name']) as $value => $dummy) {
                                $a[] = '"'.$value.'"';
                            }
                            if (is_subclass_of($f['field'], '\\Cetera\\ObjectFieldLinkSetAbstract')) {
                                $this->iterator->filterInclude( $f['field']['name'],'t.id IN ('.implode(',',$a).')' );
                            }
                            else {
                                $this->iterator->where( $this->generateField($f['field']).' IN ('.implode(',',$a).')' );
                            }
                        }
                        else {
                            if ($this->submittedValue($f['name'])) {
                                $this->iterator->where( $this->generateField($f['field']).' > 0' );
                            }
                        }
                    }
                    break;
                case self::TYPE_DROPDOWN_MULTIPLE:
                    if ($this->submittedValue($f['name']) !== null) {
                        if (is_array($this->submittedValue($f['name']))) {
                            $this->iterator->where( $this->generateField($f['field']).' IN ("'.implode('","',$this->submittedValue($f['name'])).'")' );
                        }
                    }
                    break;
                case self::TYPE_DATE_INTERVAL:
                    if ($f['value_min']) {
                        $this->iterator->where( $this->generateField($f['field']).' >= STR_TO_DATE(:'.$f['name'].'_min,"%Y-%m-%d")' )
                            ->setParameter($f['name'].'_min', $f['value_min']);
                    }
                    if ($f['value_max']) {
                        $this->iterator->where( $this->generateField($f['field']).' <= DATE_ADD(STR_TO_DATE(:'.$f['name'].'_max,"%Y-%m-%d"), INTERVAL 1 DAY)' )
                            ->setParameter($f['name'].'_max', $f['value_max']);
                    }
                    break;
                case self::TYPE_DATE:
                    if ($f['value']) {
                        $this->iterator->where( 'DATE_FORMAT('.$this->generateField($f['field']).',"%Y-%m-%d") = :'.$f['name'] )
                            ->setParameter($f['name'], $f['value']);
                    }
                    break;
                case self::TYPE_TEXT:
                    if ($f['value']) {
                        $this->iterator->where( $this->generateField($f['field']).' LIKE :'.$f['name'] )
                            ->setParameter($f['name'], '%'.$f['value'].'%');
                    }
                    break;
                case self::TYPE_AUTO:
                    if (is_int($f['value'])) {
                        $this->iterator->where( $this->generateField($f['field']).' = :'.$f['name'] )
                            ->setParameter($f['name'], $f['value']);
                    }
                    elseif (is_array($f['value'])) {
                        $this->iterator->where( $this->generateField($f['field']).' IN ('.implode(',',$f['value']).')' );
                    }
                    elseif ($f['value']) {
                        $this->iterator->where( $this->generateField($f['field']).' LIKE :'.$f['name'] )
                            ->setParameter($f['name'], '%'.$f['value'].'%');
                    }
                    break;
            }
        }
        $this->iterator->groupBy('main.id');
    }

    protected function generateField($field)
    {
        if (is_subclass_of($field, '\\Cetera\\ObjectFieldLinkSetAbstract')) {
            return '`'.$field['name'].'`';
        }
        else {
            return 'main.'.$field['name'];
        }

    }

}
