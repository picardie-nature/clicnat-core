<?php
namespace Picnat\Clicnat;

/**
 * @deprecated utiliser clicnat_utilisateur
 */
class bobs_utilisateur extends clicnat_utilisateur {
	/**
	 * @deprecated utiliser par_identifiant()
	 */
	static public function by_login($db, $login) {
		return self::par_identifiant($db, trim($login));
	}

	/**
	 * @deprecated utiliser verifier_mot_de_passe()
	 */
	public function auth_ok($pwd) {
		return $this->verifier_mot_de_passe($pwd);
	}

	/**
	 * @deprecated utiliser reseaux()
	 */
	public function get_reseaux() {
		return $this->reseaux();
	}
}
