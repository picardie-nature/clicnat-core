<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_structure extends bobs_ext_c_poly2 {
	const poste = true;
	function __construct($id_espace_poly) {
		parent::__construct('espace_structure', $id_espace_poly);
	}

	public static function get_titre() {
		return 'Depuis un site géré';
	}

	public static function new_by_array($t) {
		return new bobs_ext_c_structure($t['id_espace']);
	}

	public function  __toString() {
		return 'Site : '.parent::__toString();
	}
}
