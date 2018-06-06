<?php
namespace Cetera\Backup;

interface XmlInterface {
	public function getNodeName();
	public function backup(\XMLWriter $xml, $rootSection = null);
	public function restore(\XMLReader $xml, $parentSection = null);
}