<?php
namespace Picnat\Clicnat;

/**
 * @property-read string $code_insee
 * @property-read string $code_insee_txt
 * @property-read string $nom
 * @property-read string $nom2
 * @property-read integer $id_pays
 * @property-read integer $nombre_espece
 */
class bobs_espace_commune extends bobs_commune {
	protected $code_insee;
	protected $code_insee_txt;
	protected $nom2;
	protected $id_pays;
	protected $nombre_espece;

	private $__cache_annees_obs;

	public function __construct($db, $id, $table='espace_commune') {
		parent::__construct($db, $id, $table);
		$this->__cache_annees_obs = [];
	}

	public function __get($c) {
		switch($c) {
			case 'code_insee':
				return $this->code_insee;
			case 'code_insee_txt':
				return $this->code_insee_txt;
			case 'nom2':
				return empty($this->nom2)?$this->nom:$this->nom2;
			case 'id_pays':
				return $this->id_pays;
			case 'nombre_espece':
				return $this->nombre_espece;
			case 'dept':
				return $this->dept;
		}
		return parent::__get($c);
	}

	/**
	 * insertion des communes depuis un fichier geojson
	 * @param ressource $db
	 * @param string $pathToSrc
	 * @return integer nombre de ligne intégrée
	 */
	public static function insertGeoJsonOSM($db, $pathToSrc) {
		$n = 0;
		static $sql = "
			insert into espace_commune
				(id_espace,reference,nom,the_geom,code_insee,dept,code_insee_txt)
			values
			($1,$2,$3,st_setsrid(ST_GeomFromGeoJSON($4),4326),$5,$6,$7)
		";

		$src = file_get_contents($pathToSrc);

		if (empty($src))  {
			throw new \Exception("Ne peut ouvrir $pathToSrc");
		}

		$osm = json_decode($src, true);

		foreach ($osm['features'] as $feature) {
			$id_espace = self::nextval($db, 'espace_id_espace_seq');

			if (empty($id_espace)) {
				throw new \Exception('id_espace vide (échec nextval)');
			}

			$params = [
				$id_espace,
				$feature["properties"]["insee"],
				$feature["properties"]["nom"],
				json_encode($feature["geometry"]),
				substr($feature["properties"]["insee"],2,3),
				substr($feature["properties"]["insee"],0,2),
				sprintf("%05d",$feature["properties"]["insee"])
			];

			bobs_qm()->query($db, 'epcommune_insert_geojson', $sql, $params);

			$n++;
		}
		return $n;
	}

	public function __toString() {
		return sprintf("%s %02d", empty($this->nom2)?$this->nom:$this->nom2, $this->dept);
	}

	public static function by_code_insee($db, $code) {
		$sql = 'select * from espace_commune where trim(to_char(dept*1000+code_insee,\'09999\'))=$1';
		$q = bobs_qm()->query($db, 'esp_com_insee', $sql, [$code]);
		return new self($db, self::fetch($q));
	}

	public function pays_statistique() {
		return clicnat_pays_statistique::getInstance($this->db, $this->id_pays);
	}

	/**
	 * @todo supprimer python
	 */
	public function get_json() {
		$f = sprintf('espace_commune_%d', $this->id_espace);

		$cmd = sprintf('%s %s/espace_commune_query.py %s %s',
				PYTHON_BIN, PYTHON_GEOJSON_SCRIPTS,
				$this->id_espace, $f);
		exec($cmd, $retour, $r2);
		readfile('/tmp/'.$f);
		unlink('/tmp/'.$f);
	}

	/**
	 * @brief compte le nombre d'espèces recensées par commune et met à jour la base
	 * @param ressource $db
	 */
	public static function index_nombre_especes_par_commune($db) {
		$sql = "select
				ei.id_espace_ref as id_espace,
				count(distinct id_espece) as n
			from
				observations o,
				citations c,
				espace_intersect ei
			where
				o.id_espace=ei.id_espace_obs and
				ei.table_espace_ref='espace_commune' and
				o.id_observation=c.id_observation and
				o.brouillard = false and
				coalesce(c.nb,0)>=0 and
				coalesce(c.indice_qualite,4)>=3 and
				c.id_citation not in (select id_citation from citations_tags where id_tag in (591))
			group by
				ei.id_espace_ref";

		$q = bobs_qm()->query($db, "sel_commune_n_esp", $sql, array());
		$r = bobs_element::fetch_all($q);

		foreach ($r as $ville) {
		    error_log("commune {$ville['id_espace']} a {$ville['n']} especes");
		    bobs_qm()->query($db, "upd_commune_n_esp" , 'update espace_commune set nombre_espece=$2 where id_espace=$1', array ($ville['id_espace'], $ville['n']));
		}

		bobs_element::query($db, "commit");
	}

	public function get_dept() {
		return sprintf('%02d', $this->dept);
	}

	/**
	 * @brief retourne la première année d'observation
	 * @param integer $id_espece
	 * @return integer
	 */
	public function get_premiere_annee_obs($id_espece) {
		$t = $this->__get_annees_obs($id_espece);
		return $t['ymin'];
	}

	/**
	 * @brief retourne la dernière année d'observation
	 * @param integer $id_espece
	 * @return integer
	 */
	public function get_derniere_annee_obs($id_espece) {
		$t = $this->__get_annees_obs($id_espece);
		return $t['ymax'];
	}

	/**
	 * @brief retourne la première et la dernière année d'observation
	 * @param integer $id_espece
	 */
	private function __get_annees_obs($id_espece) {
		if (isset($this->__cache_annees_obs[$id_espece]))
			return $this->__cache_annees_obs[$id_espece];
		$sql = "	select
					max(extract('year' from date_observation)) as ymax,
					min(extract('year' from date_observation)) as ymin
				from espace_intersect, especes, citations, observations op
				left join espace_polygon on espace_polygon.id_espace=op.id_espace
				where
					op.id_espace=espace_intersect.id_espace_obs
	    				and citations.id_observation=op.id_observation
					and citations.id_espece=$2
					and espace_intersect.id_espace_ref=$1
					and espace_intersect.table_espace_ref='espace_commune'
					and coalesce(citations.nb,0)>=0
					and coalesce(citations.indice_qualite,4)>=3
					and op.brouillard=false
					and especes.id_espece=citations.id_espece
					and (coalesce(superficie,0)<=superficie_max or superficie_max=0)
					and not exists(select * from citations_invalides where citations.id_citation=citations_invalides.id_citation)";

		$q = bobs_qm()->query($this->db, 'espace_com_ln_esp', $sql, array($this->id_espace, $id_espece));
		$r = self::fetch($q);
		$this->__cache_annees_obs[$id_espece] = $r;
		return $r;
	}

	private $cache_annees_obs;

	public function entrepot_premiere_annee_obs($id_espece) {
		if (!isset($this->cache_annees_obs))
			$this->entrepot_liste_especes();
		if (!isset($this->cache_annees_obs[$id_espece]))
			return false;
		return $this->cache_annees_obs[$id_espece][0];
	}

	public function entrepot_derniere_annee_obs($id_espece) {
		if (!isset($this->cache_annees_obs))
			$this->entrepot_liste_especes();
		if (!isset($this->cache_annees_obs[$id_espece]))
			return false;
		return $this->cache_annees_obs[$id_espece][1];
	}

	public function entrepot_liste_especes() {
		$faire_cache_annees_obs = !isset($this->cache_annees_obs);
		if ($faire_cache_annees_obs)
			$this->cache_annees_obs = array();

		$ids_especes = array();
		$resultat = entrepot::db()->especes_presence_communes_data->find(array("id_espace"=>"{$this->id_espace}"));
		foreach ($resultat as $espece) {
			$ids_especes[] = $espece['id_espece'];
			if ($faire_cache_annees_obs) {
				$this->cache_annees_obs[$espece['id_espece']] = array($espece['ymin'],$espece['ymax']);
			}
		}
		return new clicnat_iterateur_especes($this->db, $ids_especes);
	}

	public function get_liste_especes($return_iterateur=false) {
		// Attention voir get_listes_especes() aussi si modif de cette requete
		$sql = "select distinct * from especes where id_espece in (
				select distinct especes.id_espece
				from espace_intersect, especes, citations, observations op
				left join espace_polygon on espace_polygon.id_espace=op.id_espace
				where
					op.id_espace=espace_intersect.id_espace_obs
	    				and citations.id_observation=op.id_observation
					and espace_intersect.id_espace_ref=$1
					and espace_intersect.table_espace_ref='espace_commune'
					and coalesce(citations.nb,0)>=0
					and coalesce(citations.indice_qualite,4)>=3
					and op.brouillard=false
					and especes.id_espece=citations.id_espece
					and (coalesce(superficie,0)<=superficie_max or superficie_max=0)
					and not exists(select * from citations_tags where citations_tags.id_citation=citations.id_citation and id_tag=591)
			)
			and exclure_restitution=false
			order by classe,ordre,famille,nom_f,nom_s";
		$q = bobs_qm()->query($this->db, 'espace_com_lesp', $sql, array($this->id_espace));

		if (!$return_iterateur) {
			return self::fetch_all($q);
		}

		$ids = array();
		foreach (self::fetch_all($q) as $l) $ids[] = $l['id_espece'];
		return new clicnat_iterateur_especes($this->db, $ids);
	}

	public function get_espece($id_espece) {
		return get_espece($this->db, $id_espece);
	}

	const sql_voisins = '
		select a.*
		from espace_commune a,espace_commune b
		where b.id_espace=$1
		and st_distance(a.the_geom, b.the_geom) = 0
		order by nom2';

	public function get_voisins() {
		$q = bobs_qm()->query(
			$this->db,
			'esp_commune_voisins',
			self::sql_voisins,
			[$this->id_espace]
		);
		return bobs_element::fetch_all($q);
	}

	const sql_liste_ecoles = 'select * from espace_point ep,espace_tags et,espace_intersects ei
		where et.id_espace=ep.id_espace
		and ei.id_espace_obs=ep.id_espace
		and ei.id_espace_ref=$2
		and ei.table_espace_ref=\'espace_commune\'
		and et.id_tag=$1
		order by ep.nom';

	/**
	 * @deprecated
	 */
	public function get_ecoles() {
		$q = bobs_qm()->query($this->db, 'esp_com_l_ecoles', self::sql_liste_ecoles, array(clicnat_ecole::tag($this->db)->id_tag, $this->id_espace));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new clicnat_ecole($this->db, $r);
		}
		return $t;
	}

	const sql_liste_dept = 'select id_espace from espace_commune where dept::integer=$1';

	/**
	 * @brief liste les commnues d'un département
	 * @param $db connection à la base
	 * @param $num_dept le numéro du département
	 * @return clicnat_iterateur_espaces
	 */
	public static function liste_pour_departement($db, $num_dept) {
		$q = bobs_qm()->query($db, 'l_commune_dept', self::sql_liste_dept, array($num_dept));
		$r = self::fetch_all($q);
		$ids = array();
		foreach ($r as $c) $ids[] = array('espace_table' => 'espace_commune', 'id_espace' => $c['id_espace']);
		return new clicnat_iterateur_espaces($db, $ids);
	}
}
