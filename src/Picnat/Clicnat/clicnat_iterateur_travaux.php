<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_travaux extends clicnat_iterateur {
	function current() {
		return get_travail($this->db, $this->ids[$this->position]);
	}
}
