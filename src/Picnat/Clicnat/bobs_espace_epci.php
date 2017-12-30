<?php
namespace Picnat\Clicnat;

class bobs_espace_epci extends bobs_poly {
	function __construct($db, $id, $table='espace_epci') {
		parent::__construct($db, $id, $table);
	}

	public static function get_list($db, $table='espace_epci') {
		return parent::get_list($db, $table);
	}
}
