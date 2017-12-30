<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_espace_hirondelle extends clicnat_iterateur{
	const sql_id_espaces = '
		select
			espace_point.id_espace,
			espace_point.id_utilisateur,
			etb.id_tag>0 as publique
		from
			espace_tags,
			espace_point left join espace_tags etb on (etb.id_espace=espace_point.id_espace and etb.id_tag=$1),
			espace_point espace_point2 left join espace_tags etc on (etc.id_espace=espace_point2.id_espace and etc.id_tag=$2)
		where
			espace_tags.id_espace=espace_point.id_espace and espace_tags.id_tag=$3
			and espace_point2.id_espace=espace_point.id_espace';

	function __construct($db,$ids,$hash = null){
		parent::__construct($db,$ids,'clicnat_iterateur_espace_hirondelle_'.$hash);
	}

	public function current(){
		return new clicnat_espace_hirondelle($this->db,$this->ids[$this->position]);
	}

	public static function in_session($hash){
		return clicnat_iterateur::in_session('clicnat_iterateur_espace_hirondelle_'.$hash);
	}

	public static function from_session($db,$hash){
		if (self::in_session($hash)){
			$session_it = $_SESSION['iterateurs']['clicnat_iterateur_espace_hirondelle_'.$hash];
			$ids = $_SESSION['iterateurs']['clicnat_iterateur_espace_hirondelle_'.$hash]['ids'];
			return new clicnat_iterateur_espace_hirondelle($db,$ids,$hash);
		}
		return false;
	}

	public static function espaces_visibles($db,$utilisateur){
		$hash = $utilisateur ? $utilisateur->id_utilisateur : 0;
		if(!clicnat_iterateur_espace_hirondelle::in_session($hash)){
			$q = bobs_qm()->query($db,'hir_id_espaces', self::sql_id_espaces, [CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE,CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT,CLICNAT_HIRONDELLE_TAG]);
			while ($r = bobs_element::fetch($q)) {
				if ($r['publique'] == 't' || ($hash != 0 && $utilisateur->id_utilisateur == $r['id_utilisateur'])) {
					$ids[] = $r['id_espace'] ;
				}
			}
			if(!is_null($ids)){
				$espaces = new clicnat_iterateur_espace_hirondelle($db,$ids,$hash);
				$espaces->to_session();
			}
		} else {
			$espaces = clicnat_iterateur_espace_hirondelle::from_session($db,$hash);
			return $espaces;
		}
	}

	public static function geojson($db,$utilisateur=false) {
		$geo = [ "type" => "FeatureCollection", "features" => [] ];
		$espaces = clicnat_iterateur_espace_hirondelle::espaces_visibles($db,$utilisateur);
		foreach ($espaces as $espace){
			$point = get_espace_point($db, $espace->id_espace);
			$geo["features"][] = [
				"type" => "Feature",
				"geometry" => [
					"type" => "Point",
					"coordinates" => [(Float)$point->get_x(),(Float)$point->get_y()]
				],
				"properties" => [
					"id_espace" => $espace->id_espace,
					"publique" => $espace->info_publique,
					"occupant" => $espace->occupant_ou_visiteur
				]
			];
		}
		return json_encode($geo);
	}
}
