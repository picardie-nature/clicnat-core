<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_structure extends clicnat_iterateur {
	function current() {
		return get_structure($this->db, $this->ids[$this->position]);
	}
}
