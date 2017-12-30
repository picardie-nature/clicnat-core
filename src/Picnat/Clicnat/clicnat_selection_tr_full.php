<?php
namespace Picnat\Clicnat;

class clicnat_selection_tr_full extends clicnat_travail implements i_clicnat_travail {
	public function executer() {
		$a = new clicnat_selection_export_full($this->db);
		$a->set('chemin', tmpdir());
		$a->set('id_selection', $this->args['id_selection']);
		$a->prepare();
		$zip = $a->execute();

		$selection = new bobs_selection($this->db, (int)$this->args['id_selection']);

		$utilisateur = get_utilisateur($this->db, $this->args['id_utilisateur']);
		$utilisateur->fichier_enregistrer($zip, "Export csv séparés sélection #{$selection->id_selection} au ".strftime('%Y-%m-%d',mktime()), "application/zip");

		unlink($zip);
		return clicnat_tache::ok;
	}
}
