<?php
namespace \Picnat\Clicnat;

/**
 * @brief statut nicheur d'un groupe de citations
 */
class bobs_aonfm_nicheur {
	/** @brief statut de nidif peut être vide ou avoir comme valeur 'possible', 'probable' ou 'certain' */
	public $statut;
	/** @brief id_espace d'un carré atlas l93_10x10 */
	public $carre_atlas;
	/** @brief tableau des citations associées à ce statut */
	public $citations;
	public $n_statut; // statut nicheur "numérique"
	public $commentaire;

	public function def_carre_atlas() {
		if (count($this->citations) > 0) {
			reset($this->citations);
			$citation = current($this->citations);
			if (!is_object($citation)) {
				print_r($this);
				throw new \Exception('$citation est pas un objet');
			}
			$obs = $citation->get_observation();
			$espace = $obs->get_espace();
			switch (get_class($espace)) {
				case 'bobs_espace_point':
					$this->carre_atlas = $espace->l93_10x10_id_espace;
					break;
				default:
					throw new \Exception('ne sait pas encore faire');
			}
		}
	}

	public function def_statut() {
		$tags_defs = array();
                foreach (bobs_aonfm::get_tags_possible() as $tag)
                        $tags_defs[$tag] = NICHEUR_POSSIBLE;
                foreach (bobs_aonfm::get_tags_probable() as $tag)
                        $tags_defs[$tag] = NICHEUR_PROBABLE;
                foreach (bobs_aonfm::get_tags_certains() as $tag)
                        $tags_defs[$tag] = NICHEUR_CERTAIN;

		$this->statut = '';

		$this->n_statut = PAS_NICHEUR;

		// Changement du statut en fonction des codes comportements
		$tags_impossible = bobs_aonfm::get_tags_impossible();
		foreach ($this->citations as $citation) {
			foreach ($citation->get_tags() as $tag) {
				if (array_search($tag['ref'], $tags_impossible) !== false) {
					$this->n_statut = PAS_NICHEUR;
					$this->statut = '';
					// on sort de la boucle, on examinera pas les autres tags
					break;
				}
				if (array_key_exists($tag['ref'], $tags_defs)) {
					$ns =  $tags_defs[$tag['ref']];
					if ($ns < NICHEUR_CERTAIN) {
						if ($ns > $this->n_statut) {
							$obs = $citation->get_observation();
							if (($ns == NICHEUR_PROBABLE) && ($tag['ref'] != '3110')) {
								$this->n_statut = max($this->n_statut, $ns);
							} else if ($citation->get_espece()->est_dans_date_nidif($obs->date_observation)) {
								$this->n_statut = max($this->n_statut, $ns);
							}
						}
					} else {
						$this->n_statut = NICHEUR_CERTAIN;
						// on continu a examiner les autres tags au cas où on tomberai sur un
						// impossible
					}
				}
			}
		}

		if ($this->n_statut >= NICHEUR_POSSIBLE) {
			$this->commentaire = 'codes';
		}

		// si les obs sont faites sur un interval de temps assez grand
		// on peut en faire un nicheur probable
		//if (empty($this->statut) && count($this->citations) > 0) {
		if (($this->n_statut == NICHEUR_POSSIBLE) && count($this->citations) > 1) {
			reset($this->citations);
			$citation = current($this->citations);
			$obs = $citation->get_observation();
			$dmin = $dmax = $obs->date_obs_tstamp;

			foreach ($this->citations as $citation) {
				$obs = $citation->get_observation();
				if ($dmin > $obs->date_obs_tstamp)
					$dmin = $obs->date_obs_tstamp;
				else if ($dmax < $obs->date_obs_tstamp)
					$dmax = $obs->date_obs_tstamp;
			}

			if (($dmax-$dmin) >= (bobs_aonfm::nombre_jours_min*86400)) {
				$this->n_statut = NICHEUR_PROBABLE;
				$this->commentaire = ' pobable (prox) ';
			}
		}


		// si le statut est toujours pas nicheur et que l'on est dans les dates
		// de nidif on a un nicheur possible
		if ($this->n_statut == PAS_NICHEUR) {
			// la premiere citation pour avoir l'espece
			$citation = reset($this->citations);

			// l'observation pour obtenir la date
			$observation = $citation->get_observation();
			$espece = $citation->get_espece();

			if ($espece->est_dans_date_nidif($observation->date_observation)) {
				$this->n_statut = NICHEUR_POSSIBLE;
				$this->commentaire .= ' possible (date) ';
			}
		}

		switch ($this->n_statut) {
			case PAS_NICHEUR:
				$this->statut = '';
				break;
			case NICHEUR_POSSIBLE:
				$this->statut = 'possible';
				break;
			case NICHEUR_PROBABLE:
				$this->statut = 'probable';
				break;
			case NICHEUR_CERTAIN:
				$this->statut = 'certain';
				break;
		}
	}
}
