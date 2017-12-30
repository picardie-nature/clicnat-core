<?php
namespace \Picnat\Clicnat;

class bobs_espace_zsc extends bobs_poly {
	function __construct($db, $id, $table='espace_zsc') {
		parent::__construct($db, $id, $table);
	}

	public static function get_list($db, $table='espace_zsc') {
		return parent::get_list($db, $table);
	}
}
