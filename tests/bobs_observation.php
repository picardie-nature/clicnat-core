<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class bobs_observationTests extends TestCase {
	public function testNouvelObservation() {
		$u = bobs_utilisateur::by_mail(get_db(), "john.doe@example.com");
		$this->assertInstanceOf(bobs_utilisateur::class, $u);
		$espace = bobs_espace_commune::by_code_insee(get_db(), 80021);
		$this->assertInstanceOf(bobs_espace_commune::class, $espace);

		$id_observation = bobs_observation::insertObservation(get_db(), [
			"id_utilisateur" => $u->id_utilisateur,
			"date_observation" => "2010-12-10",
			"id_espace" => $espace->id_espace,
			"table_espace" => $espace->get_table()
		]);

		$this->assertGreaterThan(0, $id_observation);

		$observation = get_observation(get_db(), $id_observation);
		$this->assertInstanceOf(bobs_observation::class, $observation);

		$auteur = $observation->get_auteur();
		$this->assertInstanceOf(bobs_utilisateur::class, $auteur);
		$this->assertEquals($u->id_utilisateur, $auteur->id_utilisateur);

		$espace_obs = $observation->get_espace();
		$this->assertInstanceOf(bobs_espace_commune::class, $espace_obs);
		$this->assertEquals($espace_obs->id_espace, $espace->id_espace);

		$date_deb = $observation->date_deb;
		$this->assertInstanceOf(\DateTime::class, $date_deb);

		$date_fin = $observation->date_fin;
		$this->assertInstanceOf(\DateTime::class, $date_fin);

		$this->assertEquals($date_deb, $date_fin);

		return $id_observation;
	}

	/**
	 * @depends testNouvelObservation
	 */
	public function testAjoutObservateur($id_observation) {
		$observation = new bobs_observation(get_db(), $id_observation);
		$observateurs = bobs_utilisateur::rechercher2(get_db(), "pig");
		$n = count($observateurs);
		foreach ($observateurs as $observateur) {
			$observation->ajoute_observateur($observateur);
		}

		$observateurs = $observation->get_observateurs();
		$this->assertTrue(is_array($observateurs));
		$this->assertCount($n, $observateurs);
		foreach ($observateurs as $o) {
			$this->assertTrue(is_int($o["id_utilisateur"]));
		}

		return [$id_observation, end($observateurs)['id_utilisateur'], $n];
	}

	/**
	 * @depends testAjoutObservateur
	 */
	public function testRetirerObservateur($params) {
		list($id_observation, $id_utilisateur, $n) = $params;
		$observation = new bobs_observation(get_db(), $id_observation);
		$observateur = get_utilisateur(get_db(), $id_utilisateur);

		$observation->retire_observateur($observateur);
		$observation = new bobs_observation(get_db(), $id_observation);
		$observateurs = $observation->get_observateurs();
		$this->assertCount($n-1, $observateurs);
		return $id_observation;
	}

	/**
	 * @depends testNouvelObservation
	 */
	public function testAjouterCitation($id_observation) {
		$e = bobs_espece::recherche_par_nom(get_db(), "rat des moisson");
		$observation = new bobs_observation(get_db(), $id_observation);
		$id_citation = $observation->add_citation($e[0]['id_espece']);
		$this->assertTrue(is_int($id_citation));

		$observation = new bobs_observation(get_db(), $id_observation);
		$citations = $observation->get_citations();

		$this->assertInstanceOf(clicnat_iterateur_citations::class, $citations);
		$this->assertCount(1, $citations);
		$this->assertEquals($citations->current()->id_espece, $e[0]['id_espece']);

		foreach ($observation->get_citations_ids() as $id) {
			$this->assertTrue(is_int($id));
		}
		return $id_citation;
	}

	/**
	 * @depends testAjouterCitation
	 */
	public function testValidationCitation($id_citation) {
		$citation = get_citation(get_db(), $id_citation);
		$this->assertInstanceOf(bobs_citation::class, $citation);
		$v1 = bobs_utilisateur::by_mail(get_db(), "peppa.pig@example.com");
		$v2 = bobs_utilisateur::by_mail(get_db(), "john.doe@example.com");

		$citation->prevalidation_ajoute_evaluation($v1, 1);
		$citation->prevalidation_ajoute_evaluation($v2, 1);
	}

	/**
	 * @depends testNouvelObservation
	 */
	public function testSupprimer($id_observation) {
		$observation = get_observation(get_db(), $id_observation);
		$observation->delete();
		$this->expectException(clicnat_exception_pas_trouve::class);
		$obs = new bobs_observation(get_db(), $id_observation);
	}
}
