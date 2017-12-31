<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_liste_especes extends bobs_extractions_conditions {
	const poste = true;
	protected $id_liste_espece;

	public function __construct($id_liste_espece) {
		$this->id_liste_espece = (int)$id_liste_espece;
		parent::__construct();
		$this->arguments[] = 'id_liste_espece';
	}

	public function __toString() {
		$db = $this->extraction->get_db();
		$liste = new clicnat_listes_especes($db, $this->id_liste_espece);
		return "Liste d'espèces : {$liste}";
	}

	public static function get_titre() {
		return 'Associé à une liste d\'espèces';
	}

	public function get_tables() {
		return ['listes_especes_data'];
	}

	public static function new_by_array($t) {
		return new self($t['id_liste_espece']);
	}

	public function get_sql() {
		return "citations.id_espece=listes_especes_data.id_espece and listes_especes_data.id_liste_espece={$this->id_liste_espece}";
	}
}
