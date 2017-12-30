<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_textes extends clicnat_iterateur {
	function current() {
		return get_texte($this->db, $this->ids[$this->position]);
	}
}
