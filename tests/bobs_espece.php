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
				$e->set_id_espece_parent($id_prev);
				$p = $e->taxon_parent();
				$this->assertEquals($id_prev, $p->id_espece);
			}
		}
	}

	/**
	 * @depends testInsertEspece
	 */
	public function testRecherche() {
		$e = bobs_espece::recherche_par_nom(get_db(), "rat des moisson");
		$this->assertTrue(is_array($e));
		$this->assertCount(1, $e);
		$this->assertEquals("Rat des moissons", $e[0]['nom_f']);
	}

	/**
	 * @depends testInsertEspece
	 */
	public function testParRefTiers() {
		$e = bobs_espece::by_id_ref_tiers(get_db(), "taxref", 183716);
		$this->assertInstanceOf(bobs_espece::class, $e);
		$this->assertEquals("animalia", $e->nom_s);
	}

	public function testBornage() {
		$e = bobs_espece::by_id_ref_tiers(get_db(), "taxref", 183716);
		bobs_espece::bornage(get_db(), $e->id_espece);
		foreach (bobs_espece::tous(get_db()) as $espece) {
			$espece = new bobs_espece(get_db(), $espece->id_espece);
			$this->assertNotEmpty($espece->borne_a);
			$this->assertNotEmpty($espece->borne_b);
			$this->assertGreaterThan($espece->borne_a, $espece->borne_b);
		}
	}
}
