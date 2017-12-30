<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_phoque_photos extends clicnat_iterateur {
	function current() {
		return get_phoque_photos($this->db, $this->ids[$this->position]);
	}
}
