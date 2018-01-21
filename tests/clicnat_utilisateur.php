<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class clicnat_utilisateurTests extends TestCase {
	public function testCreateUsers() {
		$r = clicnat_utilisateur::nouveau(get_db(), [
			"nom"      => "Doe",
			"prenom"   => "John",
			"username" => "john.doe",
			"tel"      => "123456",
			"port"     => "78910",
			"mail"     => "john.doe@example.com"
		]);
		$this->assertTrue($r !== false);

		$r = clicnat_utilisateur::nouveau(get_db(), [
			"nom"      => "Pig",
			"prenom"   => "Peppa",
			"username" => "peppa.pig",
			"tel"      => "7897979",
			"port"     => "121213",
			"mail"     => "peppa.pig@example.com"
		]);
		$this->assertTrue($r !== false);

		$r = clicnat_utilisateur::nouveau(get_db(), [
			"nom"      => "Pig",
			"prenom"   => "Georges",
			"username" => "georges.pig",
			"tel"      => "7897979",
			"port"     => "121213",
			"mail"     => "georges.pig@example.com"
		]);
		$this->assertTrue($r !== false);
	}

	public function testRecherche() {
		$r = clicnat_utilisateur::rechercher2(get_db(), "peppa pig");
		$this->assertCount(1, $r);
		$this->assertInstanceOf(clicnat_utilisateur::class, $r[0]);
		$this->assertEquals("Pig", $r[0]->nom);
		$this->assertEquals("Peppa", $r[0]->prenom);
		$this->assertEquals("peppa.pig", $r[0]->username);

		$r = clicnat_utilisateur::rechercher2(get_db(), "pig");
		$this->assertCount(2, $r);

		$r = clicnat_utilisateur::rechercher2(get_db(), "gloups");
		$this->assertCount(0, $r);
	}

	public function testParIdentifiant() {
		$u = clicnat_utilisateur::par_identifiant(get_db(), "peppa.pig");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("peppa.pig", $u->username);

		$u = clicnat_utilisateur::par_identifiant(get_db(), "georges.pig");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("georges.pig", $u->username);

		$u = clicnat_utilisateur::par_identifiant(get_db(), "xxx");
		$this->assertFalse($u);
	}

	public function testParMail() {
		$u = clicnat_utilisateur::by_mail(get_db(), "georges.pig@example.com");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("georges.pig", $u->username);
		return $u;
	}

	/**
	 * @depends testParMail
	 */
	public function testReglement($utilisateur) {
		$this->assertFalse($utilisateur->agreed_the_rules(), "vrai avant d'avoir accepté");
		$utilisateur->accept_rules(false);
		$this->assertTrue($utilisateur->agreed_the_rules(), "toujours faux après avoir accepté");
	}

	/**
	 * @depends testParMail
	 */
	public function testMotDePasse($utilisateur) {
		$utilisateur->set_password("ioSDOI9e__erEK");
		$this->assertEmpty($utilisateur->last_login);
		$u2 = new clicnat_utilisateur(get_db(), $utilisateur->id_utilisateur);
		$this->assertTrue($u2->verifier_mot_de_passe("ioSDOI9e__erEK"));
		$u3 = new clicnat_utilisateur(get_db(), $utilisateur->id_utilisateur);
		$this->assertNotEmpty($u3->last_login);
		$this->assertFalse($u3->verifier_mot_de_passe("ioSDO_erEK"));
	}

	public function testIterateur() {
		$utilisateurs = clicnat_utilisateur::tous(get_db());
		// count = sys et admin + ceux crées par les tests
		$this->assertEquals(2+3, $utilisateurs->count());
		foreach ($utilisateurs as $u) {
			$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		}
	}
}
