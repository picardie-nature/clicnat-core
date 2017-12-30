<?php
namespace \Picnat\Clicnat;

class bobs_espace_littoral extends bobs_poly {
	function __construct($db, $id, $table='espace_littoral') {
		parent::__construct($db, $id, $table);
	}

	/**
	 * @deprecated
	 */
	public static function get_littoral_for_point($db, $wkt, $srid=SRID_BY_DEFAULT) {
		self::cls($wkt);
		self::cli($srid);
		if ($srid != SRID_BY_DEFAULT) {
			$sql = 'select * from espace_littoral
					where contains(the_geom, transform(geomfromtext($1,$2),$3))';
			$q = bobs_qm()->query($db, 'g_littoral_for_pt_tr', $sql, array($wkt, $srid, SRID_BY_DEFAULT));
		} else {
			$sql = 'select * from espace_littoral
			where contains(the_geom, geomfromtext($1,$2))';
			$q = bobs_qm()->query($db, 'g_littoral_for_pt', $sql, array($wkt, $srid));
		}
		return self::fetch($q);
	}
}
