<?php
namespace Picnat\Clicnat;

class clicnat_validation_donnees_neg extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function executer() {
		require_once(OBS_DIR.'extractions.php');
		require_once(OBS_DIR.'extractions-conditions.php');

		$extraction = new bobs_extractions($this->db);
		$extraction->ajouter_condition(new bobs_ext_c_tag_attente());
		$extraction->ajouter_condition(new bobs_ext_c_effectif_egal(-1));
		foreach ($extraction->get_citations() as $citation) {
			$citation->validation(-1);
		}
		return clicnat_tache::ok;
	}
}
?>
