<?php
namespace Picnat\Clicnat;

class bobs_espace_toponyme extends bobs_espace_point {
	const dmax_recherche = 750;

	function __construct($db, $id, $table='espace_toponyme') {
		parent::__construct($db, $id, $table);
	}

	public function get_geom($type='toponyme') {
		return parent::get_geom($type);
	}

	public static function insert($db, $data, $table='x') {
		throw new Exception('pas possible');
	}

	const sql_prox_wkt = 'select * from bob_toponymes_proches(ST_PointFromText($1,$2),$3)';

	/**
	 * @brief liste les toponymes à proximité d'un point
	 * @param $db ressource
	 * @param $wkt le point
	 * @param $srid la projection
	 * @param $dmax distance max de recherche en mètres
	 * @return un tableau d'objet bobs_espace_toponyme
	 */
	public static function a_proximite_wkt($db,$wkt,$srid,$dmax=self::dmax_recherche) {
		self::cls($wkt, self::except_si_vide);
		self::cli($srid, self::except_si_inf_1);
		self::cli($dmax, self::except_si_inf_1);
		$q = bobs_qm()->query($db, 'topo_prox_a', self::sql_prox_wkt, array($wkt,$srid,$dmax));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = get_espace_toponyme($db, $r);
		}
		return $t;
	}

	public static function a_proximite_espace($espace, $dmax=self::dmax_recherche) {
		return self::a_proximite_wkt($espace->db, $espace->get_geom(), 4326, $dmax);
	}
}
