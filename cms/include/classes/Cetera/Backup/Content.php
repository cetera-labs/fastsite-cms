<?php
namespace Cetera\Backup;

class Content {
	
	private static $items = [
		'Cetera\\Backup\\ObjectDefinitions',
		'Cetera\\Backup\\Sections',
		'Cetera\\Backup\\Materials',
	];
	
	public static function backup($file, $rootSection = null) {
		
		$xml = new \XMLWriter();
		$xml->openMemory();
		$xml->setIndent ( true );
		$xml->startDocument("1.0", 'UTF-8');
		$xml->startElement("content");
		
		foreach(self::$items as $item) {
			(new $item)->backup($xml, $rootSection);
		}
		
		$xml->endElement();
		$xml->endDocument();
		$f = fopen($file, 'w');
		fwrite($f, $xml->outputMemory());
		fclose($f);	
		
	}
	
	public static function restore($file, $parentSection = null) {
		$reader = new \XMLReader();
		$reader->open($file);
		
		while ($reader->read()) {			
			if ($reader->name == 'content' && $reader->nodeType == \XMLReader::ELEMENT) {
				self::restoreContent($reader, $parentSection);
			}
		}

		$reader->close();
	}

	private static function restoreContent(\XMLReader $xml, $parentSection = null) {
		
		$elements = [];
		foreach(self::$items as $item) {
			$i = new $item();
			$elements[$i->getNodeName()] = $i;
		}		
		
		while ($xml->read()) {
			if ($xml->name == 'content' && $xml->nodeType == \XMLReader::END_ELEMENT) {
				return;
			}
			if (isset($elements[$xml->name]) && $xml->nodeType == \XMLReader::ELEMENT) {
				$elements[$xml->name]->restore($xml, $parentSection);
			}			
		}
	}
	
}