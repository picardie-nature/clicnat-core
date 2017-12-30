<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_import extends bobs_extractions_conditions {
	protected $id_import;
	const poste = true;

	function __construct($id) {
		parent::__construct();
		$this->arguments[] = 'id_import';
		$this->id_import = bobs_tests::cli($id);
	}

	public function  __toString() {
		return "Import : {$this->id_import}";
	}

	static public function get_titre() {
		return 'Lot d\'import';
	}

	public function get_sql() {
		return sprintf('citations.ref_import=\'%d\'', $this->id_import);
	}

	public function get_tables() {
		return ['citations'];
	}

	public static function new_by_array($t) {
		return new self($t['id_import']);
	}

	public static function get_html() {
		return "
			<label for='lcond_rimport'>Identifiant d'import</label>
			<input id='lcond_rimport' type='text' name='id_import' class='form-control' required=true>
		";
	}
}
