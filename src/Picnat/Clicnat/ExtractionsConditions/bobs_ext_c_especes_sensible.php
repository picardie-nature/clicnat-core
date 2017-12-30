<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_especes_sensible extends bobs_extractions_conditions {
	const poste = true;

	public function __toString() {
		return "Espèces classées sensibles";
	}

	public function get_tables() {
		return array('especes');
	}

	public static function get_titre() {
		return "Espèces classées sensibles";
	}

	public function get_sql() {
		$n = bobs_espece::restitution_public;
		return "especes.niveaux_restitutions&{$n}!={$n}";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_especes_sensible();
	}
}
