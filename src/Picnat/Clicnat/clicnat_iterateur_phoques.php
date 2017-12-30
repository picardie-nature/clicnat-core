<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_phoques extends clicnat_iterateur {
	function current() {
		return get_phoque($this->db, $this->ids[$this->position]);
	}
}
