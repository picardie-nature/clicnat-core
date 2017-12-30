<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_ordre extends bobs_extractions_conditions {
	const poste = true;
	protected $ordre;
	const clicnat1 = true;

	function __construct($ordre) {
		parent::__construct();
		bobs_element::cls($ordre);
		$this->ordre = pg_escape_string($ordre);
		$this->arguments[] = 'ordre';
	}

	public function  __toString() {
		return 'ordre : '.$this->ordre;
	}

	public function get_tables() {
		return array('especes');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_ordre($t['ordre']);
	}

	public function get_sql() {
		return sprintf("especes.ordre='%s'", $this->ordre);
	}

	static public function get_titre() {
		return 'Ordre de l\'espèce';
	}
}
