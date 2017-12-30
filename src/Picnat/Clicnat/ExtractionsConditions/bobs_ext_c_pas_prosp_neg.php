<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_pas_prosp_neg extends bobs_extractions_conditions {
	const poste = true;

	public function __toString() {
		return "Sans prospection négative";
	}

	public function get_tables() {
		return array('citations');
	}

	public static function get_titre() {
		return "Sans prospection négative";
	}

	public function get_sql() {
		return "coalesce(citations.nb,0) >= 0";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_pas_prosp_neg();
	}
}
