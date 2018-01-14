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

	/**
	 * @depenpds testInsertCommunes
	 */
	public function testByCodeInsee() {
		$commune = bobs_espace_commune::by_code_insee(get_db(), 80021);
		$this->assertInstanceOf(bobs_espace_commune::class, $commune);
		$this->assertEquals('Amiens', $commune->nom);
		$this->assertEquals('Amiens', $commune->nom2);
		$this->assertEquals(21, $commune->code_insee);
		$this->assertEquals("80021", $commune->code_insee_txt);
		$this->assertEquals("80", $commune->get_dept());
		$this->assertEquals("Amiens 80", $commune->__toString());
		return $commune;
	}

	/**
	 * @depends testByCodeInsee
	 */
	public function testVoisins($commune) {
		$voisins = $commune->get_voisins();
		$this->assertTrue(is_array($voisins));
		$this->assertCount(14, $voisins, "pas de voisin pour {$commune}");
	}
}
