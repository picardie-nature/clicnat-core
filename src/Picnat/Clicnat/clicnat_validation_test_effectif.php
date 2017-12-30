<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_effectif extends clicnat_validation_test {
	protected $tolerance = 25;

	const sql_sel_eff_moy = 'select moyenne from stats_validation.effectifs where id_espece=$1';

	public function evaluer() {
		if ($this->citation->nb == -1) {
			return array(
				'message' => 'prospection négative',
				'passe' => true
			);

		}
		if ($this->citation->nb == 0 || is_null($this->citation->nb)) {
			return array(
				'message' => 'effectif indéterminé',
				'passe' => true
			);
		}

		$eff_moy = $this->citation->get_espece()->validation_effectif_moyen();

		if (!$eff_moy) {
			return array(
				'message' => 'pas de moyenne calculée pour cette espèce',
				'passe' => true
			);
		}

		if ($this->citation->nb <= $eff_moy) {
			return array(
				'message' => "effectif {$this->citation->nb} inférieur à moy : {$eff_moy}",
				'passe' => true
			);
		}

		if ($this->citation->nb > $eff_moy*(1+$this->tolerance/100)) {
			return array('message' => "effectif supérieur à la moyenne + {$this->tolerance}% (eff: {$this->citation->nb} moy: {$eff_moy})", "passe" => false);
		} else {
			return array('message' => "effectif inférieur à la moyenne + {$this->tolerance}% (eff: {$this->citation->nb} moy: {$eff_moy})", "passe" => true);
		}
	}
}
