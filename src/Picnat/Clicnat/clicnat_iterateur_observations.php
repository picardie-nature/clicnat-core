<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_observations extends clicnat_iterateur {
	function current() {
		return get_observation($this->db, $this->ids[$this->position]);
	}
}
