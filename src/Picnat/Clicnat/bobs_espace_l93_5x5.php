<?php
namespace Picnat\Clicnat;

class bobs_espace_l93_5x5 extends bobs_poly {
	function __construct($db, $id, $table='espace_l93_5x5') {
	    parent::__construct($db, $id, $table);
	}

	public static function get_by_nom($db, $nom) {
		self::cls($nom, self::except_si_vide);
		return self::__get_by_nom($db, 'espace_l93_5x5', 'bobs_espace_l93_5x5', $nom);
	}
}
