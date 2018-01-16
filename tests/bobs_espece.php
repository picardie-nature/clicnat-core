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
			[
				'espece'       => '',
				'classe'       => 'M',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => 'Rodentia',
				'commentaire'  => '',
				'famille'      => '',
				'nom_f'        => 'Rongeurs',
				'nom_s'        => 'Rodentia',
				'nom_a'        => 'Rodents',
				'taxref_inpn_especes' => '186251'
			],
			[
				'espece'       => '',
				'classe'       => 'M',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => 'Rodentia',
				'commentaire'  => '',
				'famille'      => 'Muridae',
				'nom_f'        => 'Souris, Campagnols, Mulots, Rats',
				'nom_s'        => 'Muridae',
				'nom_a'        => 'Murid rodents',
				'taxref_inpn_especes' => '186259'
			],
			[
				'espece'       => '',
				'classe'       => 'M',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => 'Rodentia',
				'commentaire'  => '',
				'famille'      => 'Muridae',
				'nom_f'        => '',
				'nom_s'        => 'Micromys',
				'nom_a'        => '',
				'taxref_inpn_especes' => '194740'
			],
			[
				'espece'       => '',
				'classe'       => 'M',
				'type_fiche'   => '',
				'systematique' => '',
				'ordre'        => 'Rodentia',
				'commentaire'  => '',
				'famille'      => 'Muridae',
				'nom_f'        => 'Rat des moissons',
				'nom_s'        => 'Micromys minutus  Pallas 1771',
				'nom_a'        => '',
				'taxref_inpn_especes' => '61543'
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

	public function testRecherche() {
		$e = bobs_espece::recherche_par_nom(get_db(), "rat des moisson");
		$this->assertTrue(is_array($e));
		$this->assertCount(1, $e);
		$this->assertEquals("Rat des moissons", $e[0]['nom_f']);
	}
}
