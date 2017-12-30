<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_ref_invasif extends bobs_extractions_conditions {
	const poste = true;

	public function __construct() {
		parent::__construct();
	}

	function  __toString() {
		return "espèces invasives";
	}

	public function get_tables() {
		return array('especes');
	}

	public static function get_titre() {
		return "Espèces invasives";
	}

	public function get_sql() {
		return "especes.invasif=true";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_ref_invasif();
	}
}
