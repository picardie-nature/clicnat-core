<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_taches extends clicnat_iterateur {
	public function current() {
		return new clicnat_tache($this->db, $this->ids[$this->position]);
	}
}
