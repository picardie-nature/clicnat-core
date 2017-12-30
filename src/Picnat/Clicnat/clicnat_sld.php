<?php
namespace Picnat\Clicnat;

abstract class clicnat_sld {
	public static function xml($params) {
		$doc = static::doc($params);
		$doc->formatOutput = true;
		return $doc->saveXML();
	}
}
