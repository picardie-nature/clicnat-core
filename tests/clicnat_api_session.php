<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class clicnat_api_sessionTests extends TestCase {
	public function testCreateSession() {
		$u = clicnat_utilisateur::par_identifiant(get_db(), "peppa.pig");
		$this->assertInstanceOf(clicnat_utilisateur::class, $u);
		$session_id = clicnat_api_session::init(get_db(), $u);
		$this->assertNotEmpty($session_id);
		return $session_id;
	}

	/**
	 * @depends testCreateSession
	 */
	public function testGetSession($session_id) {
		$session = new clicnat_api_session(get_db(), $session_id);
		$this->assertInstanceOf(clicnat_api_session::class, $session);
	}

	/**
	 * @depends testCreateSession
	 */
	public function testCheck($session_id) {
		$session = new clicnat_api_session(get_db(), $session_id);
		$this->assertTrue($session->check());
		$session->update_field("date", "2017-01-01 08:00:00", true);
		$this->assertFalse($session->check());
	}
}
