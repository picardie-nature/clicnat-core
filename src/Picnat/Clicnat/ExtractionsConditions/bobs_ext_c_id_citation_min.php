<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_id_citation_min extends bobs_extractions_conditions {
	const poste = false;
	protected $id_citation;

	function __construct($id_citation) {
		parent::__construct();
		$this->id_citation = $id_citation;
		$this->arguments[] = 'id_citation';
	}

	public function __toString() {
		return "citations.id_citation &gt; {$this->id_citation}";
	}

	static public function get_titre() {
		return 'Numéro de citation minimum';
	}

	public function get_sql() {
		return sprintf('citations.id_citation > %d', $this->id_citation);
	}

	public function get_tables() {
		return ['citations'];
	}

	public static function new_by_array($t) {
		return new self($t['id_citation']);
	}

	public static function html() {
		return "
			<label for='fcond_idcitmin'>Numéro de citation</la
			<input id='fcond_idcitmin' type=\"text\" required=true name=\"id_citation\" class='form-control'/>
		";
	}
}
