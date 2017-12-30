<?php
namespace Picnat\Clicnat;

class bobs_espace_point extends bobs_espace implements interface_clicnat_espace {
	const table = 'espace_point';

	function __construct($db, $id, $table=self::table) {
		parent::__construct($db, $id, $table);
	}

	public static function insert_wkt($db, $data, $table=self::table) {
		return parent::insert_wkt($db, $data, $table);
	}

	public static function insert_kml($db, $data, $table=self::table) {
		return parent::insert_kml($db, $data, $table);
	}

	public function get_geom($type='point') {
		return parent::get_geom($type);
	}

	/**
	 * @brief commune associée au point
	 * @return bobs_espace_commune
	 */
	public function get_commune() {
		$i = $this->get_communes();
		if ($i->valid())
			return $i->current();
		return false;
	}

	/**
	 * @brief département associé à un point
	 * @return bobs_espace_departement
	 */
	public function get_departement() {
		$i = $this->get_departements();
		if ($i->valid())
			return $i->current();
		return false;
	}

	/**
	 * @brief polygone littoral associé à ce point
	 * @return bobs_espace_littoral
	 */
	public function get_littoral() {
		$i = $this->get_littoraux();
		if ($i->valid())
			return $i->current();
		return false;
	}

	/**
	 * @brief recherche les espace_point dans la boite passée en argument
	 * @param $boite ax ay bx by : un tableau des coordonnées en wgs84
	 * @return rien, le résultat est envoyé sur stdout
	 *
	 * le paramétre $boite est un tableau avec les coordonnées du
	 * coin inférieur gauche a et supérieur droit b de cette forme :
	 * 		array(ax,ay,bx,by)
	 */
	public static function get_espaces_in_box_geojson($db, $boite, $table='espace_point') {
		list($ax,$ay,$bx,$by) = $boite;
		$polygone = sprintf("POLYGON((%F %F,%F %F,%F %F,%F %F,%F %F))",
			$ax,$ay,
			$ax,$by,
			$bx,$by,
			$bx,$ay,
			$ax,$ay);

		$sql = "select *,ST_AsGeoJSON(the_geom) from $table where st_intersects(the_geom, setsrid(geomfromtext($1),4326))";
		$q = bobs_qm()->query($db, 'box_geojson_'.$table, $sql, array($polygone));

		$r = array(
			'type' => "FeatureCollection",
			'features' => array(

			)
		);
		while ($pt = self::fetch($q)) {
			$feature = array();
			$feature['type'] = 'Feature';
			$feature['geometry'] = json_decode($pt['st_asgeojson'],true);
			$feature['properties'] = array();
			foreach ($pt as $k => $v) {
				if ($k == 'st_asgeojson' || $k == 'the_geom')
					continue;
				$feature['properties'][$k] = $v;
			}
			$r['features'][] = $feature;

		}
		return json_encode($r);
	}

	public function get_reference() {
		return $this->reference;
	}

	/**
	 * @brief insertion d'un point
	 *
	 * data doit contenir les clés suivantes : id_utilisateur,reference,nom,x,y
	 *
	 * @param $db le handler de la base
	 * @param $data un tableau associatif
	 * @param $table (opt) le nom de la table (espace_chiros)
	 * @return le numéro du nouveau point
	 */
	public static function insert($db, $data, $table='espace_point') {
		$id_espace = self::nextval($db, 'espace_id_espace_seq');

		if (empty($id_espace))
			throw new Exception('id_espace vide (échec nextval)');

		self::cli($data['id_utilisateur']);
		self::cls($data['reference']);
		self::cls($data['nom']);
		self::cls($data['x']);
		self::cls($data['y']);

		$data['x'] = str_replace(',', '.',  $data['x']);
		$data['y'] = str_replace(',', '.',  $data['y']);

		if (empty($data['x']) or empty($data['y']))
			throw new Exception('$data[x] ou $data[y] vide');

		$sql = "insert into $table (id_espace,id_utilisateur,reference,nom,the_geom)".
			'values ($1,$2,$3,$4,ST_PointFromText(\'POINT(\'||$5||\' \'||$6||\')\', $7))';

		$qm = bobs_qm();
		$qm->query($db, $table.'_insert', $sql, array(
			$id_espace, $data['id_utilisateur'], $data['reference'],
			$data['nom'], $data['x'], $data['y'], SRID_BY_DEFAULT));

		return $id_espace;
	}

	/**
	 * @param $pt 'x' or 'y' character
	 */
	private function get_xy($pt) {
		if ($pt != 'x' and $pt != 'y')
			throw new InvalidArgumentException('$pt values allowed values : "x" or "y"');

		self::cli($this->id_espace);

		$q_name = 'esp_point_'.$pt.'_'.$this->table;

		$qm = bobs_qm();
		$sql = '';
		if (!$qm->ready($q_name))
			$sql = sprintf('select st_%s(the_geom) as coord from %s where id_espace=$1', $pt, $this->table);

		$q = $qm->query($this->db, $q_name, $sql, array($this->id_espace));
		$r = self::fetch($q);
		return $r['coord'];
	}

	public function get_x() {
		return $this->get_xy('x');
	}

	public function get_y() {
		return $this->get_xy('y');
	}

	public function get_image($width, $height, $fond) {
		$commune = $this->get_commune();
		$p = bobmap_point_reproject($this->get_x(), $this->get_y());
		return $img = $commune->get_ms_img($width, $height, $fond, $p);
	}

	public static function get_list($db, $table='espace_point') {
		return parent::get_list($db, $table);
	}

	public static function get_json_points($points) {
		$data = array();
		$data['type'] = 'FeatureCollection';
		$data['features'] = array();
		foreach ($points as $point) {
			$p = get_espace_point(get_db(), $point);
			$data['features'][] = array(
				'type' => 'Feature',
				'properties' => array(
					'id_espace' => $p->id_espace,
					'id_utilisateur' => $p->id_utilisateur,
					'reference' => $p->reference,
					'nom' => $p->nom
				),
				'geometry' => array(
					'type' => 'Point',
					'coordinates' => array($p->get_x(), $p->get_y())
				)
			);
		}
		return json_encode($data);
	}

	public static function recherche($db, $nom) {
		$sql = 'select id_espace from bob_recherche_espace_point($1)';
		$q = bobs_qm()->query($db, 'ep_by_name', $sql, array($nom));
		$r = self::fetch_all($q);

		$points = array_column($r, 'id_espace');

		if (count($points) > 0)
			return self::get_json_points($points);

		return '';
	}

	/**
	 * @brief fournit le nom du point
	 *
	 * si le point n'a pas de nom alors on va utiliser celui de la commune où il se trouve, et si
	 * il est sur le littoral, le nom de la zone correspondante
	 */
	public function __toString() {
		$r = parent::__toString();
		if (empty($r)) {
			$commune = $this->get_commune();
			if (!$commune) {
				$r = "Commune : $commune";
			} else {
				$littoral = $this->get_littoral();
				if (!$l) {
					$r = "Littoral : $littoral";
				}
			}
		}
		return $r;
	}

	public function ajoute_tag($id_tag, $intval=null, $textval=null) {
	    return $this->__ajoute_tag(BOBS_TBL_TAG_ESPACE, 'id_espace', $id_tag, $this->id_espace, $intval, $textval);
	}

	public function supprime_tag($id_tag) {
	    return $this->__supprime_tag(BOBS_TBL_TAG_ESPACE, 'id_espace', $id_tag, $this->id_espace);
	}

	const sql_type_sol = "select reference from espace_corine where reference in ('111','112','121') and contains(the_geom,ST_PointFromText($1,$2))";

	public static function point_dans_zone_urbaine_dense($db, $wkt, $srid) {
		self::cls($wkt, self::except_si_vide);
		self::cli($srid, self::except_si_inf_1);
		$q = bobs_qm()->query($db, 'topo_ts', self::sql_type_sol, array($wkt,$srid));
		$r = self::fetch_all($q);
		return count($r)>0;
	}

	public function dans_zone_urbaine_dense() {
		return self::point_dans_zone_urbaine_dense($this->db, $this->get_geom(), 4326);
	}

	public static function get_by_ref($db, $ref) {
		return self::__get_by_ref($db, 'espace_point', 'bobs_espace_point', $ref);
	}
}
