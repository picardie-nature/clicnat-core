<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_chr extends clicnat_validation_test {
	public function evaluer() {
		$chr = $this->citation->get_espece()->get_chr();
		return array(
			"passe" => $chr == false,
			"message" => $chr?"espèce dans le CHR : {$chr}":"pas de CHR pour cette espèce"
		);
	}
}
