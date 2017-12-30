<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_rarete_menace extends clicnat_validation_test {
	function __construct($citation) {
		parent::__construct($citation);
		$this->params = array(
			"menace" => ["VU","EN","CR","RE"],
			"rarete" => ["EX","TR","R","AR","PC"]
		);
	}

	public function evaluer() {
		$ref_reg = $this->citation->get_espece()->get_referentiel_regional();
		if (!$ref_reg) {
			return [
				"passe" => true,
				"message" => "espèce pas évaluée dans le référentiel régional"
			];
		} else {
			$passe = true;
			$message = "Référentiel régional : ";
			if (($n = array_search($ref_reg['categorie'], $this->get_param('menace'))) !== false) {
				$passe = false;
				$message .= "menace : {$ref_reg['categorie']} ";
			}
			if (($n = array_search($ref_reg['indice_rar'], $this->get_param('rarete'))) !== false) {
				$passe = false;
				$message .= " rareté : {$ref_reg['indice_rar']} ";
			}
			if ($passe) {
				$message .= "OK";
			}
			return [
				"passe" => $passe,
				"message" => $message
			];
		}
	}
}
