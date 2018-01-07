<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class bobs_utilisateurTests extends TestCase {
	public function testCreateUsers() {
		$r = bobs_utilisateur::nouveau(get_db(), [
			"nom"      => "Doe",
			"prenom"   => "John",
			"username" => "john.doe",
			"tel"      => "123456",
			"port"     => "78910",
			"mail"     => "john.doe@example.com"
		]);
		$this->assertTrue($r !== false);

		$r = bobs_utilisateur::nouveau(get_db(), [
			"nom"      => "Pig",
			"prenom"   => "Peppa",
			"username" => "peppa.pig",
			"tel"      => "7897979",
			"port"     => "121213",
			"mail"     => "peppa.pig@example.com"
		]);
		$this->assertTrue($r !== false);

		$r = bobs_utilisateur::nouveau(get_db(), [
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
		$r = bobs_utilisateur::rechercher2(get_db(), "peppa pig");
		$this->assertCount(1, $r);
		$this->assertInstanceOf(clicnat_utilisateur::class, $r[0]);
		$this->assertEquals("Pig", $r[0]->nom);
		$this->assertEquals("Peppa", $r[0]->prenom);
		$this->assertEquals("peppa.pig", $r[0]->username);

		$r = bobs_utilisateur::rechercher2(get_db(), "pig");
		$this->assertCount(2, $r);

		$r = bobs_utilisateur::rechercher2(get_db(), "gloups");
		$this->assertCount(0, $r);
	}

	public function testParIdentifiant() {
		$u = bobs_utilisateur::par_identifiant(get_db(), "peppa.pig");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("peppa.pig", $u->username);

		$u = bobs_utilisateur::par_identifiant(get_db(), "georges.pig");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("georges.pig", $u->username);

		$u = bobs_utilisateur::par_identifiant(get_db(), "xxx");
		$this->assertFalse($u);
	}

	public function testParMail() {
		$u = bobs_utilisateur::by_mail(get_db(), "georges.pig@example.com");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$this->assertEquals("georges.pig", $u->username);
	}
}
