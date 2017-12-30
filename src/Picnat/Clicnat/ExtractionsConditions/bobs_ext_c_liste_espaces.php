<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_liste_espaces extends bobs_extractions_conditions {
	const poste = true;
	protected $id_liste_espace;

	public function __construct($id_liste_espace) {
		$this->id_liste_espace = (int)$id_liste_espace;
		parent::__construct();
		$this->arguments[] = 'id_liste_espace';
	}

	public function __toString() {
		$db = $this->extraction->get_db();
		require_once(OBS_DIR.'liste_espace.php');
		$liste = new clicnat_listes_espaces($db, $this->id_liste_espace);
		return "Liste d'espaces : $liste";
	}

	public static function get_titre() {
		return "Associé à une liste d'espaces (identifiants)";
	}

	public function get_tables() {
		return array('observations');
	}

	public static function new_by_array($t) {
		return new self($t['id_liste_espace']);
	}

	public function get_sql() {
		return "observations.id_espace in (select id_espace from listes_espaces_data where id_liste_espace={$this->id_liste_espace})";
	}
}
