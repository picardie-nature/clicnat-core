<?php
namespace Picnat\Clicnat;

class clicnat_selection_tr_csv extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function __construct($db, $args) {
		parent::__construct($db, $args);
	}

	public function executer() {
		$sel = new bobs_selection($this->db, (int)$this->args['id_selection']);
		$f = tempnam("/tmp/", "clicnat_selection_{$sel->id_selection}_").".csv";
		$fh = fopen($f, 'w');
		$sel->extract_csv($fh, $this->args['opts']);
		fclose($fh);

		$utilisateur = get_utilisateur($this->db, $this->args['id_utilisateur']);
		$utilisateur->fichier_enregistrer($f, "Sélection #{$sel->id_selection} au ".strftime('%Y-%m-%d',mktime()), "text/csv");

		unlink($f);
		return clicnat_tache::ok;
	}
}
