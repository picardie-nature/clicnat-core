<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_esp_homologation extends bobs_extractions_conditions {
	const poste = true;
	public function  __toString() {
		return 'Espèces à homologation';
	}

	public static function get_titre() {
		return 'Espèces à homologation';
	}

	public function get_sql() {
		return 'especes.id_chr is not null';
	}

	public function get_tables() {
		return ['especes'];
	}

	public static function new_by_array($t) {
		return new self();
	}
}
