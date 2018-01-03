<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_a_document extends bobs_extractions_conditions {
	const poste = true;

	public function __toString() {
		return self::get_titre();
	}

	public static function get_titre() {
		return 'avec un document (photos ou sons) associé';
	}

	public function get_sql() {
		// c'est la jointure avec la table
		// citations_documents qui est interessante
		return "1=1";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_a_document();
	}

	public function get_tables() {
		return ['citations_documents'];
	}
}
