<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_nouvel_observ extends clicnat_validation_test {
	public function evaluer() {
		$n_j = 0;
		$n = 0;
		foreach ($this->citation->get_observation()->get_observateurs() as $d_observateur) {
			$observateur = get_utilisateur($this->db, $d_observateur);
			if ($observateur->junior())
				$n_j++;
			$n++;
		}
		if ($n_j == $n) {
			$passe = false;
			if ($n > 1)
				$message = "tous les observateurs sont nouveau";
			else
				$message = "nouvel observateur";
		} else { // $n_j < $n
			$passe = true;
			$message = "$n_j nouveau(x) sur $n observateurs";
		}
		return ["passe" => true, "message" => $message];
	}
}
