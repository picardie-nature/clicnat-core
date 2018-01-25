<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class clicnat2_reseauTests extends TestCase {
	public function testCreation() {
		$id = "te";
		clicnat2_reseau::insert(
			get_db(),
			clicnat2_reseau::table,
			[
				"id" => $id,
				"nom" => "Test",
				"restitution_nom_s" => true,
				"restitution_nombre_jours" => 42,
				"restitution_auto" => true
			]
		);
		$reseau = new clicnat2_reseau(get_db(), $id);
		$this->assertInstanceOf(clicnat2_reseau::class, $reseau);
		$this->assertEquals($id, $reseau->id);
		return $id;
	}

	/**
	 * @depends testCreation
	 */
	public function testAjoutBranche($id_reseau) {
		$id_taxref = 186206;
		$espece = bobs_espece::by_id_ref_tiers(get_db(), "taxref", $id_taxref);
		$reseau = new clicnat2_reseau(get_db(), $id_reseau);
		$reseau->ajouter_branche($espece);

		$reseau = new clicnat2_reseau(get_db(), $id_reseau);
		clicnat2_reseau::liste_reseaux(get_db(), true);
		$branches = $reseau->liste_branches_especes();
		$this->assertInstanceOf(clicnat_iterateur_especes::class, $branches);
		$this->assertEquals(1, $branches->count());

		$this->assertTrue($reseau->espece_dans_le_reseau($espece));
		return [$id_reseau, $id_taxref];
	}

	/**
	 * @depends testAjoutBranche
	 */
	public function testAjoutValidateur($params) {
		list($id_reseau, $id_taxref) = $params;
		$this->assertNotEmpty($id_reseau);
		$espece = bobs_espece::by_id_ref_tiers(get_db(), "taxref", $id_taxref);
		$reseau = new clicnat2_reseau(get_db(), $id_reseau);
		$this->assertEquals($id_reseau, $reseau->id);
		$v1 = clicnat_utilisateur::by_mail(get_db(), "peppa.pig@example.com");
		$v2 = clicnat_utilisateur::by_mail(get_db(), "john.doe@example.com");
		$this->assertInstanceOf(clicnat_utilisateur::class, $v1);
		$this->assertInstanceOf(clicnat_utilisateur::class, $v2);
		$reseau->ajouter_validateur($v1->id_utilisateur, $espece);
		$reseau->ajouter_validateur($v2, $espece);
		$this->assertTrue($reseau->est_validateur($v1));
		$this->assertTrue($reseau->est_validateur($v2));
	}
}
