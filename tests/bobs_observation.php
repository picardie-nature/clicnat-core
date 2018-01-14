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
	}
}
