<?php
namespace Picnat\Clicnat;

class clicnat_selection_tr_shp extends clicnat_travail implements i_clicnat_travail {
	public function executer() {
		$sel = new bobs_selection($this->db, (int)$this->args['id_selection']);
		// $this->args[type] = BOBS_EXTRACT_SHP_NCHIRO ou BOBS_EXTRACT_SHP_NORMAL
		$zip = $sel->extract_shp_zip($this->args['epsg'], $this->args['type']);

		$utilisateur = get_utilisateur($this->db, $this->args['id_utilisateur']);
		$utilisateur->fichier_enregistrer($zip, "Shapefile sélection #{$sel->id_selection} au ".strftime('%Y-%m-%d',mktime()), "application/zip");

		unlink($zip);
		return clicnat_tache::ok;
	}
}
