<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_validateurs extends clicnat_iterateur_utilisateurs {
	public function current() {
		return new clicnat_validateur($this->db, $this->ids[$this->position]['id_utilisateur'], $this->ids[$this->position]['id_espece']);
	}

	public function in_array($id_utilisateur) {
		$ids = array_column($this->ids, 'id_utilisateur');
		return in_array($id_utilisateur, $ids);
	}
}
