<?php
namespace Picnat\Clicnat;

class clicnat_selection_tr_validation extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function executer() {
		$tache = new bobs_selection_filtre_validation($this->db);
		$tache->set('id_selection', (int)$this->args['id_selection']);
		$tache->prepare();
		$tache->execute();
	}
}
