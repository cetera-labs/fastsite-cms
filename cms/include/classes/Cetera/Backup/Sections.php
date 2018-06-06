<?
namespace Cetera\Backup;

use \Cetera\Catalog as Catalog;

class Sections implements XmlInterface {
		
	private $xml;
	
	private static $realId = [];
		
	public function getNodeName() {
		return 'sections';
	}
		
	public function backup(\XMLWriter $xml, $rootSection = null) {
		
		if (!$rootSection) $rootSection = Catalog::getRoot();
		
		$this->xml = $xml;
		$this->xml->startElement( $this->getNodeName() );
		$this->backupSection( $rootSection );
		$this->xml->endElement();
	}
	
	public function restore(\XMLReader $xml, $parentSection = null) {
		
		$stack = [];
		
		while ($xml->read()) {
			if ($xml->name == $this->getNodeName() && $xml->nodeType == \XMLReader::END_ELEMENT) {
				return;
			}			
			if ($xml->name == 'section' && $xml->nodeType == \XMLReader::ELEMENT) {
				$section = $this->restoreSection($xml, $parentSection);
				$permissions = [];
			}
			if ($xml->name == 'permission') {
				$pid = $xml->getAttribute('id');
				$gid = $xml->getAttribute('group_id');
				if (!isset($permissions[$pid])) $permissions[$pid] = [];
				$permissions[$pid][] = $gid;
			}
			if ($xml->name == 'section' && $xml->nodeType == \XMLReader::END_ELEMENT) {
				if ($section) {
					if (count($permissions)) $section->updatePermissions($permissions);
				}
			}
			if ($xml->name == 'children' && $xml->nodeType == \XMLReader::ELEMENT) {
				array_push($stack,$parentSection);
				$parentSection = $section;
			}		
			if ($xml->name == 'children' && $xml->nodeType == \XMLReader::END_ELEMENT) {
				$parentSection = array_pop($stack);
			}						
		}		
	}
	
	public static function realId($id) {
		if (isset(self::$realId[$id])) return self::$realId[$id];
		return $id;
	}
	
	public function restoreSection(\XMLReader $xml, $parent = null) {
		
		$sid = $xml->getAttribute('id');
		$permissions = [];
		if ($sid == 0) {
			$section = Catalog::getRoot();
		}
		else {
			if (!$parent) $parent = Catalog::getRoot();
			
			if ( $xml->getAttribute('isServer') ) {
				
				if ($parent->isServer()) {
					self::$realId[$sid] = $parent->id;
					return $parent;
				}
				else {
					$parent = Catalog::getRoot();
				}
				
				$alias = $xml->getAttribute('alias');
				do {
					try {
						$section = $parent->getChildByAlias( $alias );
						$alias .= '_copy';
					}
					catch (\Exception $e) {
						$section = false;
					}
				} while ($section != false);
				
				$new_sid = $parent->createChild(array(
					'name'		=> $xml->getAttribute('name'),
					'alias'		=> $alias,
					'typ'	  	=> \Cetera\ObjectDefinition::findByTable( $xml->getAttribute('materialsType') ),
					'link'		=> 0,
					'server'    => 1,
					'autoalias' => $xml->getAttribute('autoalias')
				));
				self::$realId[$sid] = $new_sid;
				$section = \Cetera\Catalog::getById($new_sid);				
	
			}
			else {
				
				try {
					$section = $parent->getChildByAlias( $xml->getAttribute('alias') );
					self::$realId[$sid] = $section->id;
				}
				catch (\Exception $e) {
					
					$new_sid = $parent->createChild(array(
						'name'		=> $xml->getAttribute('name'),
						'alias'		=> $xml->getAttribute('alias'),
						'typ'	  	=> \Cetera\ObjectDefinition::findByTable( $xml->getAttribute('materialsType') ),
						'link'		=> 0,
						'server'    => 0,
						'autoalias' => $xml->getAttribute('autoalias')
					));
					self::$realId[$sid] = $new_sid;
					$section = \Cetera\Catalog::getById($new_sid);
					
				}				
			}	

		}
		
		return $section;		
	}
	
	private function backupSection($section) {
		
		$this->xml->startElement( 'section' );
		
		$this->xml->writeAttribute('id', (int)$section->id);
		$this->xml->writeAttribute('alias', $section->alias);
		$this->xml->writeAttribute('name', $section->name);
		$this->xml->writeAttribute('materialsType', $section->materialsObjectDefinition->alias);
		$this->xml->writeAttribute('isServer', (int)$section->isServer());
		$this->xml->writeAttribute('isRoot', (int)$section->isRoot());		
		$this->xml->writeAttribute('hidden', (int)$section->hidden);
		$this->xml->writeAttribute('prototype', (int)$section->prototype->id);
		$this->xml->writeAttribute('isLink', (int)$section->isLink());
		$this->xml->writeAttribute('inheritFields', (int)$section->inheritFields);
		$this->xml->writeAttribute('inheritPermissions', (int)$section->isInheritsPermissions());
		$this->xml->writeAttribute('autoalias', (int)($this->catalogType&Catalog::AUTOALIAS)?1:0);
		$this->xml->writeAttribute('autoaliasTranslit', (int)($this->catalogType&Catalog::AUTOALIAS_TRANSLIT)?1:0);
		$this->xml->writeAttribute('autoaliasId', (int)($this->catalogType&Catalog::AUTOALIAS_ID)?1:0);	
		$this->xml->writeAttribute('template', $section->template);
		$this->xml->writeAttribute('templateDir', $section->templateDir);		
				
		if (!$section->isInheritsPermissions()) {
			$this->xml->startElement( 'permissions' );
			foreach ($section->getPermissions() as $p) {
				$this->xml->startElement( 'permission' );
				$this->xml->writeAttribute('id', $p['permission']);
				$this->xml->writeAttribute('group_id', $p['group_id']);	
				$this->xml->endElement();				
			}
			$this->xml->endElement();
		}
		
		if (count($section->children)) {
			$this->xml->startElement( 'children' );
			foreach ($section->children as $c) {
				$this->backupSection($c);
			}
			$this->xml->endElement();
		}
		
		$this->xml->endElement();
	}
	
}