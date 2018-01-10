<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class bobs_especeTests extends TestCase {
	public function testInsertEspece() {
		$srcs = [
			[
				'espece'       => '',
				'classe'       => '_',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => '',
				'commentaire'  => '',
				'famille'      => '',
				'nom_f'        => 'animaux',
				'nom_s'        => 'animalia',
				'nom_a'        => 'animals',
				'taxref_inpn_especes' => '183716'
			],
			[
				'espece'       => '',
				'classe'       => '_',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => '',
				'commentaire'  => '',
				'famille'      => '',
				'nom_f'        => 'Chordés',
				'nom_s'        => 'Chordata',
				'nom_a'        => 'chordates',
				'taxref_inpn_especes' => '185694'
			],
		];
		foreach ($srcs as $src) {
			$id_espece = bobs_espece::insertEspece(get_db(), $src);
			$this->assertGreaterThan(0, $id_espece);
		}
	}
}
