<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_commentaire extends bobs_extractions_conditions {
	const poste = true;

	protected $mot;

	function __construct($mot) {
		$this->mot = $mot;
		bobs_element::cls($this->mot);
		if (empty($this->mot))
			throw new Exception('oops');
		parent::__construct();
		$this->arguments[] = 'mot';
	}

	public function __toString() {
		return "recherche {$this->mot}";
	}

	public static function get_titre() {
		return "Recherche commentaire";
	}

	public function get_sql() {
		return sprintf("citations.commentaire ilike '%%%s%%'", pg_escape_string($this->mot));;
	}

	public function get_tables() {
		return array('citations');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_commentaire($t['mot']);
	}
}
