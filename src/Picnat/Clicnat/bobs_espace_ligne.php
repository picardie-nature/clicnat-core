<?php
namespace Picnat\Clicnat;

class bobs_espace_ligne extends bobs_espace {
	const table = 'espace_line';

	function __construct($db, $id, $table='espace_line') {
		parent::__construct($db, $id, $table);
	}

	public static function insert_wkt($db, $data, $table=self::table) {
		return parent::insert_wkt($db, $data, $table);
	}

	public static function insert_kml($db, $data, $table=self::table) {
		return parent::insert_kml($db, $data, $table);
	}

	public function get_geom() {
		return parent::get_geom('ligne');
	}

	public static function get_by_ref($db, $ref) {
		return self::__get_by_ref($db, 'espace_line', 'bobs_espace_ligne', $ref);
	}
}
