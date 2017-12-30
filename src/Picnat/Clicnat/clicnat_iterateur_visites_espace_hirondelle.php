<?php
namespace Picnat\Clicnat;

/**
 * @brief Iterateur de visites
 */
class clicnat_iterateur_visites_espace_hirondelle extends clicnat_iterateur {
	function __construct($db,$ids,$hash = null) {
		parent::__construct($db,$ids,'clicnat_iterateur_visites_espace_hirondelle_'.$hash);
	}

	function current() {
		return new clicnat_visite_espace_hirondelle($this->db, $this->ids[$this->position]);
	}

	public static function in_session($hash) {
		return clicnat_iterateur::in_session('clicnat_iterateur_visites_espace_hirondelle_'.$hash);
	}

	public static function from_session($db,$hash){
		if (self::in_session($hash)){
			$session_it = $_SESSION['iterateurs']['clicnat_iterateur_visites_espace_hirondelle_'.$hash];
			$ids = $_SESSION['iterateurs']['clicnat_iterateur_visites_espace_hirondelle_'.$hash]['ids'];
			return new clicnat_iterateur_visites_espace_hirondelle($db,$ids,$hash);
		}
		return false;
	}
}
