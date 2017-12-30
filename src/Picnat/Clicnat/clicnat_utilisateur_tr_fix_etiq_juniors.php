<?php
namespace Picnat\Clicnat;

class clicnat_utilisateur_tr_fix_etiq_juniors extends clicnat_travail implements i_clicnat_travail {
	public function __construct($db, $args) {
		parent::__construct($db, $args);
		$this->opts = array();
	}

	public function executer() {
		$sql = 'select distinct observations.id_utilisateur
			from citations_tags,citations,observations
			where id_tag=592
			and citations.id_citation=citations_tags.id_citation
			and observations.id_observation=citations.id_observation';

		$q = bobs_qm()->query($this->db, 'l1', $sql, array());

		while ($u = bobs_element::fetch($q)) {
			if (empty($u['id_utilisateur'])) continue;
			$utl = get_utilisateur($this->db, $u['id_utilisateur']);
			if ($utl->junior()) {
				// passe toujours junior
				$sql = "select citations.id_citation
					from observations,citations,citations_tags
					where observations.id_utilisateur=$1
					and observations.id_observation=citations.id_observation
					and citations_tags.id_citation=citations.id_citation and id_tag=592";
				$qa = bobs_qm()->query($this->db, 'l2', $sql, array($utl->id_utilisateur));
				while ($cit = bobs_element::fetch($qa)) {
					$citation = get_citation($this->db, $cit['id_citation']);
					if (!$citation->en_attente_de_validation()) {
						// pas en attente
						if (!$citation->invalide()) {
							// pas invalide : supprime tag
							$citation->supprime_tag(592, 0);
						}
					}
				}
				continue;
			} else {
				$sql = "select citations.id_citation
					from observations,citations,citations_tags
					where observations.id_utilisateur=$1
					and observations.id_observation=citations.id_observation
					and citations_tags.id_citation=citations.id_citation and id_tag=592";
				$qa = bobs_qm()->query($this->db, 'l3', $sql, array($utl->id_utilisateur));
				while ($cit = bobs_element::fetch($qa)) {
					$citation = get_citation($this->db, $cit['id_citation']);
					$citation->supprime_tag(592, 0);
				}
			}
		}
	}

	public static function planifier($db) {
		clicnat_tache::ajouter($db, strftime("%Y-%m-%d %H:%M:%S",mktime()), 0, 'Etiquettes juniors', 'clicnat_utilisateur_tr_fix_etiq_juniors', array());
	}
}
