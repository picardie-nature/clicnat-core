<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_espece_det_znieff extends bobs_extractions_conditions {
	const poste = true;

	function __construct() {
		parent::__construct();
	}

	public function  __toString() {
		return 'espèce déterminante ZNIEFF';
	}

	public function get_tables() {
		return array('especes');
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_espece_det_znieff();
	}

	public function get_sql() {
		return sprintf("especes.determinant_znieff = true");
	}

	static public function get_titre() {
		return 'Espèces déterminante ZNIEFF';
	}
}
