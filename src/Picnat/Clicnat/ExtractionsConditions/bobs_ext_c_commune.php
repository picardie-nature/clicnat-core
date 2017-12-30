<?php
namespace Picnat\Clicnat\ExtractionsConditions;

/**
 * @todo Prendre en charge les chiros
 */
class bobs_ext_c_commune extends bobs_extractions_conditions {
	protected $id_espace;
	const poste = true;

	function  __construct($id_espace) {
		parent::__construct();
		$this->arguments[] = 'id_espace';
		$this->id_espace = $id_espace;
	}

	public function  __toString() {
		$db = $this->extraction->get_db();
		$commune = get_espace_commune($db, $this->id_espace);
		return "Commune de <b>{$commune->nom}</b>";
	}

	public static function get_titre() {
		return 'Commune';
	}

	public function get_sql() {
		return sprintf("espace_intersect.id_espace_ref=%d and espace_intersect.table_espace_ref='espace_commune'", $this->id_espace);
	}

	public static function new_by_array($t) {
		return new self($t['id_espace']);
	}

	public function get_tables() {
		return ['espace_intersect', 'observations'];
	}

	public static function get_html() {
		return "
			<label for='lcond_commune'>Commune</label>
			<input id='lcond_commune' type='text' name='id' class='autocomplete_commune form-control' required=true/>
		";
	}
}
