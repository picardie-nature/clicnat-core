<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_epci extends bobs_ext_c_poly {
	const poste = true;
	function __construct($id_espace_poly) {
		parent::__construct('espace_epci', 'espace_point', $id_espace_poly);
	}

	public static function get_titre() {
		return 'Depuis un EPCI';
	}

	public static function new_by_array($t) {
		if (array_key_exists('espace_po_id_es', $t))
			$t['id_espace'] = $t['espace_po_id_es'];
		return new bobs_ext_c_epci($t['id_espace']);
	}

	public function  __toString() {
		return 'EPCI '.parent::__toString();
	}
}
