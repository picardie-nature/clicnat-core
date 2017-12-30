<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_etiquettes extends clicnat_validation_test {
	public function evaluer() {
		$oks = [618,579,473,580,581,591,593,592,611,590,610];
		$n_total = 0;
		$n_exclusion = 0;
		$message = '';
		foreach ($this->citation->get_tags() as $tag) {
			$n_total++;

			if (array_search($tag['id_tag'], $oks) !== false) {
				continue;
			}

			$tag = get_tag($this->db, $tag['id_tag']);
			if (!$tag->test_association_espece($this->citation->get_espece())) {
				$n_exclusion++;
				$message .= "étiquette \"{$tag->lib} #tag{$tag->id_tag}\" incompatible avec l'espèce ";
			}
		}
		return [
			"passe" => $n_exclusion == 0,
			"message" => "$n_exclusion étiquette(s) sur $n_total incompatible : $message"
		];
	}
}
