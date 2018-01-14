<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class bobs_espace_communeTests extends TestCase {
	public function testInsertCommunes() {
		static $pathsrc = __DIR__."/data/communes_somme.geojson";
		$src = json_decode(file_get_contents($pathsrc), true);
		$n = bobs_espace_commune::insertGeoJsonOSM(get_db(), $pathsrc);
		$this->assertEquals(count($src['features']), $n);
	}
}
