<?php
namespace Picnat\Clicnat;

class bobs_citation_validation extends bobs_citation {
	function necessite_attention() {
		// indice qualité en 1 et 3
		if ($this->indice_qualite >= 1 && $this->indice_qualite <= 3) {
			return true;
		}

		// nouvel observateur
		foreach ($this->get_observation()->get_observateurs() as $observ) {
			$u = get_utilisateur($this->db, $observ['id_utilisateur']);
			if ($u->junior()) {
				return true;
			}
		}

		// date pas habituelle

		// R sup a 10km

		return false;
	}
}
