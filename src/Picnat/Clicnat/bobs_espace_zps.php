<?php
namespace \Picnat\Clicnat;

class bobs_espace_zps extends bobs_poly {
	function __construct($db, $id, $table='espace_zps') {
		parent::__construct($db, $id, $table);
	}

	public static function get_list($db, $table='espace_zps') {
		return parent::get_list($db, $table);
	}
}
