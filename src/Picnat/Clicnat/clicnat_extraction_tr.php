<?php
namespace Picnat\Clicnat;

class clicnat_extraction_tr extends clicnat_travail implements i_clicnat_travail {
	public function executer() {
		$ex = bobs_extractions::charge_xml($this->db, $this->args['xml'], $this->args['id_utilisateur']);
		$ex->dans_selection($this->args['id_selection']);
	}
}
