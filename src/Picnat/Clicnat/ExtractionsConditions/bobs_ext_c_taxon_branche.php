<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_taxon_branche extends bobs_extractions_conditions {
	const poste = true;
	protected $id;

	function __construct($id_espece) {
		if (empty($id_espece))
			throw new \Exception("pas d'identifiant de taxon");
		$this->id = $id_espece;
		$this->arguments[] = 'id';
	}

	public function __toString() {
		$db = \Picnat\Clicnat\get_db();
		$espece = \Picnat\Clicnat\get_espece($db, $this->id);
		return "Taxons en dessous de {$espece}";
	}

	public static function get_titre() {
		return "Taxons en dessous dans l'arbre taxonomique";
	}

	public function get_sql() {
		$db = \Picnat\Clicnat\get_db();
		$esp = \Picnat\Clicnat\get_espece($db, $this->id);
		if (!$esp) throw new \Exception("id_espece={$this->id}");
		$borne_a = $esp->borne_a;

		$borne_b = $esp->borne_b;
		if (empty($borne_a)) throw new \Exception("Borne_a est vide {$this->id}");
		if (empty($borne_b)) throw new \Exception("Borne_b est vide {$this->id}");
		return "(especes.borne_a between {$esp->borne_a} and {$esp->borne_b})";
	}

	public function get_tables() {
		return ['especes'];
	}

	public static function new_by_array($t) {
		return new self($t['id']);
	}

	public static function get_html() {
		return "
			<label for='lcond_taxon'>Espèce</label>
			<input id='lcond_taxon' type='text' name='id' class='autocomplete_espece form-control' required=true/>
		";
	}
}
