<?php
namespace Picnat\Clicnat;

/**
 * @brief définition d'un nid d'hirondelle
 */
class clicnat_espace_hirondelle extends bobs_espace_point implements interface_clicnat_espace {
	/** @brief détermine si l'utilisateur qui a créé le point occupe ou est propriétaire du lieu où il se trouve */
	protected $occupant_ou_visiteur = null;

	/** @brief le nid peut-il être présenté à d'autres utilisateurs pour visites ultérieurs ? */
	protected $info_publique = null;

	const sql_visites = 'select * from visite_espace_hirondelle where id_espace=$1 order by date_visite_nid asc';

	const sql_espaces = '
	select
		espace_point.id_espace,
		espace_point.id_utilisateur,
		etb.id_tag>0 as publique,
		etc.id_tag>0 as occupant,
		st_x(espace_point.the_geom) as x,
		st_y(espace_point.the_geom) as y
	from
		espace_tags,
		espace_point left join espace_tags etb on (etb.id_espace=espace_point.id_espace and etb.id_tag=$1),
		espace_point espace_point2 left join espace_tags etc on (etc.id_espace=espace_point2.id_espace and etc.id_tag=$2)
	where
		espace_tags.id_espace=espace_point.id_espace and espace_tags.id_tag=$3
		and espace_point2.id_espace=espace_point.id_espace';

	public function __construct($db, $id, $table='espace_point') {
		parent::__construct($db, $id, $table);
		$this->set_occupant_publique();
	}

	public function visites() {
		if(!clicnat_iterateur::in_session('clicnat_iterateur_visites_espace_hirondellle_'.$this->id_espace)){
			$q = bobs_qm()->query($this->db, 'hir_l_vis_s', self::sql_visites, [$this->id_espace]);
			$r = self::fetch_all($q);
			$visites = new clicnat_iterateur_visites_espace_hirondelle($this->db, array_column($r,"id_visite_nid"),$this->id_espace);
			$visites->to_session();
		}else
			$visites = clicnat_iterateur_visites_espace_hirondelle::from_session('clicnat_iterateur_visites_espace_hirondelle_'.$this->id_espace);
		return $visites;
	}

	const sql_occupant_publique = '
	select
		etb.id_tag>0 as publique,
		etc.id_tag>0 as occupant
		from
		espace_tags,
		espace_point left join espace_tags etb on (etb.id_espace=espace_point.id_espace and etb.id_tag=$1),
		espace_point espace_point2 left join espace_tags etc on (etc.id_espace=espace_point2.id_espace and etc.id_tag=$2)
	where
		espace_point.id_espace = $4 and
		espace_tags.id_espace=espace_point.id_espace and espace_tags.id_tag=$3
		and espace_point2.id_espace=espace_point.id_espace';


	public function set_occupant_publique(){
		$q = bobs_qm()->query($this->db, 'hir_s_occ_pub', self::sql_occupant_publique,[CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE,CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT,CLICNAT_HIRONDELLE_TAG,$this->id_espace]);
		$r = self::fetch($q);
		$this->info_publique = $r['publique'] == 't' ;
		$this->occupant_ou_visiteur = $r['occupant'] == 't';
	}


	public function occupant_ou_visiteur(){
		return $this->occupant_ou_visiteur;
	}

	public function info_publique(){
		return $this->info_publique;
	}

	/**
	 * @brief produit une couche geojson des colonies visible par un utilisateur
	 * @return une chaine de caractéres en GeoJSON
	 */
	public static function geojson_utilisateur($db, $utilisateur) {
		return clicnat_iterateur_espace_hirondelle::geojson($db,$utilisateur);
	}

	/**
	 * @brief produit une couche geojson des colonies visible par tous
	 * @return une chaine de caractéres en GeoJSON
	 */
	public static function geojson_public($db) {
		$geo = ["type" => "FeatureCollection", "features" => [] ];
		$q = bobs_qm()->query($db, 'hir_espaces_l', self::sql_espaces, [CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE,CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT,CLICNAT_HIRONDELLE_TAG]);
		while ($r = bobs_element::fetch($q)) {
			if ($r['publique'] == 't' ) {
				$geo["features"][] = [
					"type" => "Feature",
					"geometry" => [
						"type" => "Point",
						"coordinates" => [(float)$r['x'], (float)$r['y']]
					],
					"properties" => [
						"id_espace" => (int)$r['id_espace'],
						"publique" => $r['publique'] == 't',
						"occupant" => $r['occupant'] == 't'
					]
				];
			}
		}
		return json_encode($geo);
	}

	public static function count_colonies($db){
		$nb_colonies = 0;
		$q = bobs_qm()->query($db, 'hir_espaces_l', self::sql_espaces, [CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE,CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT,CLICNAT_HIRONDELLE_TAG]);
		while ($r = bobs_element::fetch($q)) {
			$nb_colonies ++;
		}
		return $nb_colonies;
	}
}
