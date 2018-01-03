<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_l93_10x10 extends bobs_ext_c_poly {
	const clicnat1 = true;
	const poste = true;
	function __construct($id_espace_poly) {
		parent::__construct('espace_l93_10x10', 'espace_point', $id_espace_poly);
	}

	public static function get_titre() {
		return "Depuis un carré Atlas L93 10x10";
	}

	public static function new_by_array($t) {
		return new \Picnat\Clicnat\bobs_ext_c_l93_10x10($t['id_espace']);
	}

	public function __toString() {
		return 'Atlas L93 10x10 : '.parent::__toString();
	}
}
