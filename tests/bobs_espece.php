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
			[
				'espece'       => '',
				'classe'       => 'M',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => '',
				'commentaire'  => '',
				'famille'      => '',
				'nom_f'        => 'Mammifère',
				'nom_s'        => 'Mammalia',
				'nom_a'        => '',
				'taxref_inpn_especes' => '186206'
			],

		];
		$id_espece = $id_prev = null;
		foreach ($srcs as $src) {
			if (!empty($id_espece)) $id_prev = $id_espece;
			$id_espece = bobs_espece::insertEspece(get_db(), $src);

			$e = get_espece(get_db(), $id_espece);
			$this->assertEquals($src['nom_f'], $e->nom_f);
			$this->assertEquals($src['nom_a'], $e->nom_a);
			$this->assertEquals($src['nom_s'], $e->nom_s);
			$this->assertGreaterThan(0, $id_espece);

			if (!empty($id_prev)) {
				$e->set_id_espece_parent($id_espece);
				$p = $e->taxon_parent();
				$this->assertEquals($id_espece, $p->id_espece);
			}
		}
	}
}
