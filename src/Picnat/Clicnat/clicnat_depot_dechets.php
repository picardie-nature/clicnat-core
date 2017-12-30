<?php
namespace Picnat\Clicnat;

/**
 * @brief Liste d'espaces
 */
class clicnat_depot_dechets extends bobs_element {
	protected $id_depotoir;
	protected $auteur;
	protected $date_creation;
	protected $date_modif;
	protected $sur_voie_public;
	protected $statut;

	const __table__ = 'depotoirs';
	const __prim__ = 'id_depotoir';
	const __seq__ = 'depotoirs_id_depotoir_seq';

	function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
		$this->champ_date_maj = 'date_modif';
	}

	public static function creer($db, $x, $y, $auteur) {
		$data = [
			self::__prim__ => self::nextval($db, self::__seq__),
			'the_geom' => sprintf("SRID=4326;POINT(%F %F)", $x ,$y),
			'auteur' => $auteur,
		];
		parent::insert($db, self::__table__, $data);
		return $data[self::__prim__];
	}

	const sql_pt_voisins = 'select id_depotoir,st_x(the_geom) as x,st_y(the_geom) as y
	       			from depotoirs
	       			where st_intersects(
					the_geom,
					st_transform(st_buffer(st_transform(st_setsrid(st_point($1,$2),4326),2154),$3),4326)
				)';

	/**
	 * @brief liste les points voisins
	 * @param $db ressource postgres
	 * @param $x longitude wgs84
	 * @param $y latitude wgs84
	 * @param $distance distance en m
	 * @return points[] => id_depotoir,x,y
	 */
	public static function depots_voisins($db, $x, $y, $distance) {
		$q = bobs_qm()->query($db, 'depots_voisins', self::sql_pt_voisins, [$_POST['x'], $_POST['y'], $distance]);
		return self::fetch_all($q);
	}

	public function ajouter_observation($data) {
		$data['id_depotoir'] = $this->id_depotoir;
		return clicnat_depot_dechets_observations::creer($this->db, $data);
	}
}
