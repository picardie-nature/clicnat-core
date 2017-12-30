<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_indice extends clicnat_validation_test {
	public function evaluer() {
		$indice = empty($this->citation->indice_qualite)?4:$this->citation->indice_qualite;
		return [
			"passe" => $indice == 4,
			"message" => "indice fiabilité : $indice"
		];
	}
}
