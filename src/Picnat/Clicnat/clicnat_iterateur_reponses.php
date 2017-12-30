<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_reponses extends clicnat_iterateur {
	public function current () {
		return new clicnat_reponse($this->db, $this->ids[$this->position]);
	}
}
