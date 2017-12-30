<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_mammifere_terrestre extends bobs_extractions_conditions {
	public function __toString() {
		return 'Mammifères terrestres';
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_mammifere_terrestre();
	}

	public function get_tables() {
		return array('especes');
	}

	public function get_sql() {
		return "classe='M' and ordre not ilike 'chiro%' and ordre not ilike 'pinni%' and ordre not ilike 'c%tac%'";
	}

	public static function get_titre() {
		return 'Mammifères terrestre';
	}
}
