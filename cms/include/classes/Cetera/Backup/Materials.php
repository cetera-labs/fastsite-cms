<?php
namespace Cetera\Backup;

use \Cetera\ObjectDefinition as ObjectDefinition;

class Materials implements XmlInterface {
	
	private $xml;
	
	private static $realId = [];
	private $additional = [];
	
	public function getNodeName() {
		return 'materials';
	}

	public function restore(\XMLReader $xml, $parentSection = null) {
		$res = [];
		
		if ($xml->getAttribute('section') !== NULL) {
			$sid = Sections::realId( $xml->getAttribute('section') );	
			$section = \Cetera\Catalog::getById( $sid );
			$od = false;
		}
		elseif ($xml->getAttribute('objectDefinition') !== NULL) {
			$section = false;
			$od = \Cetera\ObjectDefinition::findByAlias( $xml->getAttribute('objectDefinition') );
		}

		while ($xml->read()) {
			if ($xml->name == $this->getNodeName() && $xml->nodeType == \XMLReader::END_ELEMENT) {
				return $res;
			}
			
			if ($xml->name == $this->getNodeName() && $xml->nodeType == \XMLReader::ELEMENT) {
				$fields[$field] = $this->restore($xml, $parentSection);
			}			
			
			if ($xml->name == 'material' && $xml->nodeType == \XMLReader::ELEMENT) {				
				$fields = [
					'DESIRABLE_ID' => $xml->getAttribute('id')
				];
			}
			
			if ($xml->name == 'field' && $xml->nodeType == \XMLReader::ELEMENT) {
				$field = $xml->getAttribute('name');
			}
			if ($xml->name == 'material' && $xml->nodeType == \XMLReader::END_ELEMENT) {
				
				if ($section) {
					$list = $section->getMaterials()->unpublished()->where('alias=:alias')->setParameter('alias', $fields['alias']);

					if (!$list->getCountAll()) {
						
						unset($fields['idcat']);
						$m = $section->createMaterial();
						$m->setFields($fields);
						$m->save();
						$res[] = $m->id;
						
					}
				}
				elseif ($od) {
					
					try {
						$m = \Cetera\Material::fetch($fields, $od);				
						$m->save();
						$res[] = $m->id;
					}
					catch (\Exception $e) {
					}
					
				}
			}
			if (in_array($xml->nodeType, array(\XMLReader::TEXT, \XMLReader::CDATA))) {
                $fields[$field] .= $xml->value;	
			}
		}
	}
	
	public function backup(\XMLWriter $xml, $rootSection = null) {
		$this->xml = $xml;
		
		if (!$rootSection) $rootSection = Catalog::getRoot();
		
		$this->additional = [];
		
		foreach ($rootSection->getSubs() as $cid) {	
			$this->backupMaterialsFromSection( \Cetera\Catalog::getById($cid) );
		}	

		foreach (ObjectDefinition::enum() as $od) {
			if ($od->alias == 'users' || $od->alias == 'dir_data') continue;
			$this->backupMaterialsFromObjectDefinition($od);
		}	

		if (count($this->additional)) {
			
			$this->xml->startElement( 'materials' );
			
			while($data = array_shift($this->additional)) {
				//print $data.'<br>';
				list($id,$type,$table) = explode('***', $data);
				$m = \Cetera\Material::getById($id, $type, $table);
				$this->backupMaterial($m);
				
			}
			
			$this->xml->endElement();			
			
		}
	}	
	
	public function backupMaterialsFromSection(\Cetera\Catalog $section) {
		$materials = $section->getMaterials()->unpublished();
		if (!$materials->getCountAll()) return;
		
		$this->xml->startElement( 'materials' );
		$this->xml->writeAttribute('section', $section->id);
		
		$this->backupMaterialsFromIterator($materials);
		
		$this->xml->endElement();
	}
	
	public function backupMaterialsFromObjectDefinition(\Cetera\ObjectDefinition $od) {
		$materials = $od->getMaterials()->where('idcat=0');
		if ($materials instanceof \Cetera\Iterator\Material) {
			$materials->unpublished();
		}
		if (!$materials->getCountAll()) return;		
		
		$this->xml->startElement( 'materials' );
		$this->xml->writeAttribute('objectDefinition', $od->alias);
				
		$this->backupMaterialsFromIterator($materials);
		
		$this->xml->endElement();
		
	}
	
	public function backupMaterialsFromIterator(\Cetera\Iterator\Material $materials) {
		foreach ($materials as $m) {
			$this->backupMaterial($m);
		}		
	}
	
	public function backupMaterial($m) {
			$this->xml->startElement( 'material' );
			$this->xml->writeAttribute('id', $m->id);
			$this->xml->writeAttribute('objectDefinition', $m->objectDefinition->alias);
			foreach($m->objectDefinition->getFields() as $f) {			
				$value = $m->getDynamicField($f['name']);
							
				$this->xml->startElement( 'field' );
				$this->xml->writeAttribute('name', $f['name']);	
				if ($f instanceof \Cetera\ObjectFieldText) {
					if ($value) $this->xml->writeCdata( $value );
				}				
				elseif ($f instanceof \Cetera\ObjectFieldScalar) {
					$this->xml->text( $value );
				}
				elseif ($f instanceof \Cetera\ObjectFieldLinkSet) {
					foreach ($m->{$f['name']} as $link) {
						$this->xml->startElement( 'link' );
						$this->xml->writeAttribute('id', $link->id);
						$this->xml->endElement();	
					}					
				}
				elseif ($f instanceof \Cetera\ObjectFieldMaterialSet) {
					$this->xml->startElement( 'materials' );
					$this->xml->writeAttribute('objectDefinition', $m->{$f['name']}->getObjectDefinition()->alias);
					foreach ($m->{$f['name']} as $link) {						
						$this->backupMaterial($link);						
					}
					$this->xml->endElement();	
				}
				$this->xml->endElement();				
			}	
			$this->xml->endElement();	
	}	
	
}