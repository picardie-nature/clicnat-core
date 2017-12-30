<?php

namespace Picnat\Clicnat;

class clicnat_validation_test_periode_obs extends clicnat_validation_test {
	const sql_select_periode = 'select id_espece,decade from stats_validation.periodes_especes where id_espece=$1 and decade=$2';
	public function evaluer() {
		$decade = bobs_element::decade($this->citation->get_observation()->date_observation);
		$q = bobs_qm()->query($this->db, 's_select_period_obs', self::sql_select_periode, array($this->citation->id_espece, $decade));
		$r = bobs_element::fetch($q);
		$passe = isset($r['decade']) && $r['decade'] == $decade;
		return [
			"passe" => $passe,
			"message" => $passe?"espèce déjà vu dans la décade {$decade}":"En dehors des périodes d'observation de l'espèce decade $decade ".var_dump($r)
		];
	}
}
