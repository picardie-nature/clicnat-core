<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_selection extends bobs_extractions_conditions {
	const poste = false;

	protected $id_selection;

	function __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_selection';
		$this->id_selection = bobs_tests::cli($id, bobs_tests::except_si_inf_1);
	}

	static public function get_titre() {
		return 'Dans une sélection';
	}

	public function get_tables() {
		return ['selection_data'];
	}

	public static function new_by_array($t) {
		return new self($t['id_selection']);
	}

	public function __toString() {
		return "Contenu de la sélection #{$this->id_selection}";
	}

	public function get_sql() {
		return sprintf("selection_data.id_selection=%d", $this->id_selection);
	}
}
