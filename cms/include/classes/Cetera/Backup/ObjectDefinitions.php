<?php
namespace Cetera\Backup;

use \Cetera\ObjectDefinition as ObjectDefinition;

class ObjectDefinitions implements XmlInterface {
	
	private $xml;
	
	public function getNodeName() {
		return 'objectDefinitions';
	}

	public function backup(\XMLWriter $xml, $rootSection = null) {
		$this->xml = $xml;
		$this->xml->startElement( $this->getNodeName() );
		
		$od_array = [];
		
		foreach (ObjectDefinition::enum() as $od) {
			if ($od->alias == 'users') continue;
			
			foreach($od->getFields() as $f) {
				if ( is_a($f, 'Cetera\ObjectFieldMaterial') || is_a($f, 'Cetera\ObjectFieldMaterialSet') ) {
					array_unshift($od_array, $f->getObjectDefinition());
				}				
			}
			
			array_push($od_array, $od);
		}
		
		$od_array2 = [];
		
		foreach ($od_array as $od) {
			if (isset($od_array2[$od->alias])) continue;
			$od_array2[$od->alias] = $od;
		}
		
		foreach ($od_array2 as $od) {
			$this->backupObjectDefinition($od);
		}
		
		$this->xml->endElement();
	}	
	
	public function restore(\XMLReader $xml, $parentSection = null) {
		while ($xml->read()) {
			
			if ($xml->name == 'object' && $xml->nodeType == \XMLReader::ELEMENT) {
				$od_data = [
					'alias'       => $xml->getAttribute('alias'),
					'describ'     => $xml->getAttribute('description'),
					'fixed'       => $xml->getAttribute('fixed'),
					'fields'      => []
				];
			}
			
			if ($xml->name == 'field' && $xml->nodeType == \XMLReader::ELEMENT) {
				$od_data['fields'][ $xml->getAttribute('name') ] = [
					'name'          => $xml->getAttribute('name'),
					'type'          => $xml->getAttribute('type'),
					'pseudo_type'   => $xml->getAttribute('pseudo_type'),
					'description'   => $xml->getAttribute('description'),
					'length'        => $xml->getAttribute('length'),
					'show'          => $xml->getAttribute('show'),
					'required'      => $xml->getAttribute('required'),
					'fixed'         => $xml->getAttribute('fixed'),
					'editor'        => $xml->getAttribute('editor'),
					'editor_user'   => $xml->getAttribute('editor_user'),
					'default_value' => $xml->getAttribute('default_value'),
				];
			}
			
			if ($xml->name == 'object' && $xml->nodeType == \XMLReader::END_ELEMENT) {
				
				try {
					$od = \Cetera\ObjectDefinition::findByAlias($od_data['alias']);
				}
				catch (\Exception $e) {
					// Тип не найден. Создаем.
					$od = \Cetera\ObjectDefinition::create($od_data);
				}
				// Проверяем поля.
				foreach ($od_data['fields'] as $f) {
					
					try {
						$field = $od->getField( $f['name'] );
					}
					catch (\Exception $e) {
						// Поле не найдено, создаем
						$od->addField( $f );
						continue;
					}
					
					if (in_array($field['name'],['name','tag','alias','autor','dat','dat_update','idcat','type'])) continue;
					
					$t = \Cetera\Application::getInstance()->getTranslator();
					
					if ($field['type'] != $f['type']) {
						if (is_a($field, 'Cetera\ObjectFieldText') && in_array($f['type'],[FIELD_TEXT,FIELD_LONGTEXT,FIELD_HUGETEXT])) {
							 if ($f['type'] > $field['type']) {
								 // изменить тип поля на более ёмкий
								 $od->updateField( $f );
								 continue;
							 }
						}
						else {
							throw new \Exception(sprintf($t->_('Несовпадение поля %s у типа материалов %s. Продолжение невозможно.'),$field['name'],$od_data['alias']));
						}
					}
					else {
						if ($field['length'] != $f['length']) {
							if ( is_a($field, 'Cetera\ObjectFieldMaterial') || is_a($field, 'Cetera\ObjectFieldMaterialSet') ) {
								if ( $field->getObjectDefinition()->alias != $f['length'] ) {
									throw new \Exception(sprintf($t->_('Несовпадение поля %s у типа материалов %s. Продолжение невозможно.'),$field['name'],$od_data['alias']));
								}
							}
							elseif (is_a($field, 'Cetera\ObjectFieldLinkAbstract')) {
								throw new \Exception(sprintf($t->_('Несовпадение поля %s у типа материалов %s. Продолжение невозможно.'),$field['name'],$od_data['alias']));
							}
							elseif ($field['type'] == FIELD_TEXT) {
								if ($field['length'] < $f['length']) {
									// увеличить размер текстового поля
									$od->updateField( $f );
									continue;
								}
							}
						}
					}
					
				}
				
			}			
			
			if ($xml->name == $this->getNodeName() && $xml->nodeType == \XMLReader::END_ELEMENT) {
				return;
			}
		}
	}
	
	
	public function backupObjectDefinition(\Cetera\ObjectDefinition $od) {
		$this->xml->startElement( 'object' );
		$this->xml->writeAttribute('id', $od->id);
		$this->xml->writeAttribute('name', $od->alias);
		$this->xml->writeAttribute('alias', $od->alias);
		$this->xml->writeAttribute('description', $od->description);
		$this->xml->writeAttribute('fixed', (int)$od->fixed);
		foreach($od->getFields() as $f) {
			$this->xml->startElement( 'field' );
			$this->xml->writeAttribute('name', $f['name']);
			$this->xml->writeAttribute('type', $f['type']);
			$this->xml->writeAttribute('pseudo_type', $f['pseudo_type']);
			$this->xml->writeAttribute('description', $f['description']);
			$len = $f['length'];	
			if ( is_a($f, 'Cetera\ObjectFieldMaterial') || is_a($f, 'Cetera\ObjectFieldMaterialSet') ) {
				$len = $f->getObjectDefinition()->alias;
			}
			$this->xml->writeAttribute('length', $len);
			$this->xml->writeAttribute('show', $f['show']);
			$this->xml->writeAttribute('required', $f['required']);
			$this->xml->writeAttribute('fixed', $f['fixed']);
			$this->xml->writeAttribute('editor', $f['editor']);
			$this->xml->writeAttribute('editor_user', $f['editor_user']);
			$this->xml->writeAttribute('default_value', $f['default_value']);
			$this->xml->writeAttribute('page', $f['page']);
			$this->xml->endElement();
		}
		$this->xml->endElement();
	}	
	
}