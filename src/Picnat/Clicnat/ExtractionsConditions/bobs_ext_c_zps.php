<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_zps extends bobs_ext_c_poly {
	const poste = true;
	function __construct($id_espace_poly) {
		parent::__construct('espace_zps', 'espace_point', $id_espace_poly);
	}

	public static function get_titre() {
		return "Depuis une ZPS";
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_zps($t['id_espace']);
	}

	public function __toString() {
		return 'ZPS : '.parent::__toString();
	}
}
