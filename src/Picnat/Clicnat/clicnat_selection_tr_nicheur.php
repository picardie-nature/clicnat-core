<?php
namespace Picnat\Clicnat;

class clicnat_selection_tr_nicheur extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function __construct($db, $args) {
		parent::__construct($db, $args);
	}

	public function executer() {
		$tache = new bobs_selection_extraction_nicheurs($this->db);
		$tache->set('id_selection', (int)$this->args['id_selection']);
		$tache->prepare();
		$tache->execute();
	}
}
