<?php
namespace Picnat\Clicnat;

//
//
// Travaux : Taches qui sont exécutées en arrière plan (pas de limite de volume)
//
//
class clicnat_selection_tr_mix_a extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function executer() {
		$tache = new bobs_selection_mix_a($this->db);
		$tache->set('projection', (int)$this->args['projection']);
		$tache->set('pas', (int)$this->args['pas']);
		$tache->set('id_selection', (int)$this->args['id_selection']);
		$tache->prepare();
		$tache->execute();
		$zip = $s->extract_shp_mix_zip($this->args['projection'], $this->args['id_selection']);
		$tache->utilisateur()->fichier_enregistrer($zip, "Sélection agrégée par maille {$this->args['projection']} {$this->args['id_selection']}","application/zip");
	}
}
