<?php
namespace Picnat\Clicnat;

class bobs_espace_polygon extends bobs_poly {
	const table = 'espace_polygon';

	function __construct($db, $id, $table=self::table) {
		parent::__construct($db, $id, $table);
	}

	public static function insert_wkt($db, $data, $table=self::table) {
		return parent::insert_wkt($db, $data, $table);
	}

	public static function insert_kml($db, $data, $table=self::table) {
		return parent::insert_kml($db, $data, $table);
	}

	public function get_geom() {
		return parent::get_geom('wkt_poly');
	}

	// surcharge st_multi en +
	const sql_get_gml = 'select ST_AsGML(st_transform(st_multi(the_geom),$2)) as gml from %s where id_espace=$1';
	public function get_geom_gml($srid=4326) {
		self::cli($this->id_espace, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'esp_get_gmlmult_'.$this->table, sprintf(self::sql_get_gml, $this->table), array($this->id_espace,(int)$srid));
		$r = self::fetch($q);
		return $r['gml'];
	}

	public static function get_by_ref($db, $ref) {
		return self::__get_by_ref($db, self::table, __CLASS__, $ref);
	}
	public static function get_espaces_in_point($db, $x, $y) {
		return self::__get_espaces_in_point($db, self::table, __CLASS__, $x, $y);
	}
}
