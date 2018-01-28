<?php
namespace Picnat\Clicnat;

class bobs_commune extends bobs_poly {
	const sql_recherche_espace_commune = "select * from espace_commune where lower(regexp_replace(nom,'-',' ','g')) ilike lower(regexp_replace($1||'%','-',' ','g')) order by nom";

	function __construct($db, $id, $table='espace_commune') {
		parent::__construct($db, $id, $table);
	}


	public function get_geom($type='commune') {
		return parent::get_geom($type);
	}

	public function get_espaces_interieurs() {
		$geometry = $this->get_geom('commune');
		return self::get_espaces_in_poly($this->db, 'point', $geometry);
	}

	public static function get_all($db) {
		$t = array();
		$q = bobs_qm()->query($db, 'commune_all', 'select * from espace_commune order by nom', array());
		while ($r = self::fetch($q))
			$t[] = get_espace_commune($db, $r);
		return $t;
	}


	public static function rechercher($db, $args,$table='espace_commune') {
		return parent::rechercher($db,$args,$table);
	}

	/**
	 * récupérer les observations associé à ce point
	 * @todo prendre en charge les chiros
	 * @return bobs_observation[]
	 */
	public function get_observations() {
		$sql = "select o.*
			from observations o,espace_point pt
			where o.id_espace=pt.id_espace
			and o.espace_table='espace_point'
			and commune_id_espace=".$this->id_espace."
			order by date_observation desc";
		$t = self::query_fetch_all($this->db, $sql);
		$rt = [];
		if (is_array($t) && count($t) > 0) {
			foreach ($t as $obs) {
				$rt[] = get_observation($this->db, $obs);
			}
		}
		return $rt;
	}

	/**
	 * @deprecated
	 */
	public static function get_commune_for_point($db, $wkt, $srid=SRID_BY_DEFAULT) {
		self::cls($wkt);
		self::cli($srid);
		if ($srid != SRID_BY_DEFAULT) {
			$sql = 'select * from espace_commune
					where st_contains(the_geom, transform(geomfromtext($1,$2),$3))';
			$q = bobs_qm()->query($db, 'g_commune_for_pt_tr', $sql, array($wkt, $srid, SRID_BY_DEFAULT));
		} else {
			$sql = 'select * from espace_commune
			where st_contains(the_geom, geomfromtext($1,$2))';
			$q = bobs_qm()->query($db, 'g_commune_for_pt', $sql, array($wkt, $srid));
		}
		return self::fetch($q);
	}

	public static function get_list($db, $table='espace_commune') {
		return parent::get_list($db, $table);
	}
}
