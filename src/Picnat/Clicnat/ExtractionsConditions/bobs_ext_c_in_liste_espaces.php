<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_in_liste_espaces extends bobs_extractions_conditions {
	const poste = true;
	protected $id_liste_espace;

	public function __construct($id_liste_espace) {
		$this->id_liste_espace = (int)$id_liste_espace;
		parent::__construct();
		$this->arguments[] = 'id_liste_espace';
	}

	public function __toString() {
		$db = $this->extraction->get_db();
		$liste = new clicnat_listes_espaces($db, $this->id_liste_espace);
		return "Liste d'espaces : $liste";
	}

	public static function get_titre() {
		return "Intersecte une liste d'espaces";
	}

	public function get_tables() {
		return ['observations'];
	}

	public static function new_by_array($t) {
		return new self($t['id_liste_espace']);
	}

	public function get_sql() {
		return sprintf("observations.id_espace in (select id_espace from clicnat_espace_in_poly_englob_liste_espaces(%d))",$this->id_liste_espace);
	}
}
