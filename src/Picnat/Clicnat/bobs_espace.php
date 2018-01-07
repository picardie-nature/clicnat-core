<?php
namespace Picnat\Clicnat;

class bobs_espace extends bobs_element_espace_commentaire {
	public $id_espace;
	public $id_utilisateur;
	public $reference;
	public $nom;


	const nom_par_defaut = "sans nom";
	const table_commentaires = 'espace_commentaires';

	// liste des champs pour l'export en JSON
	protected $champs_export_json;

	public function __construct($db, $id, $table=false) {
		if (!$table or empty($table)) {
			throw new \Exception(
					'Il ne faut pas instancier bobs_espace '.
					'mais une de ses classes dérivées '.$table);
		}
		parent::__construct($db, $table, 'id_espace', $id);
		$this->champs_export_json = array('nom','reference','id_espace');
	}

	public function get_communes() {
		return $this->get_espaces_intersects('espace_commune');
	}

	public function get_departements() {
		return $this->get_espaces_intersects('espace_departement');
	}

	public function get_littoraux() {
		return $this->get_espaces_intersects('espace_littoral');
	}

	public function get_toponymes() {
		return $this->get_espaces_intersects('espace_toponyme');
	}

	public function __toString() {
		if (empty($this->nom)) return self::nom_par_defaut;
		return $this->nom;
	}

	public function renomme($nouveau_nom) {
		$this->update_field('nom', $nouveau_nom);
	}

	public function get_commentaires() {
		return $this->__get_commentaires(self::table_commentaires, 'id_espace', $this->id_espace);
	}

	public function ajoute_commentaire($type_c, $id_utilisateur, $commtr) {
		return $this->__ajoute_commentaire(self::table_commentaires, 'id_espace', $this->id_espace, $type_c, $commtr, $id_utilisateur);
	}

	public function supprime_commentaire($id_commentaire) {
		return $this->__supprime_commentaire(self::table_commentaires, $id_commentaire);
	}

	const sql_index_atlas = "select * from espace_index_atlas where table_espace=$1 and id_espace=$2 and srid=$3 and pas=$4";

	/**
	 * @brief liste les carrés de répartition occupé par l'objet
	 * @param $srid numéro de le projection utilisé pour la grille
	 * @param $pas pas de la grille
	 * @return un tableau associatif [table_espace,id_espace,srid,pas,x0,y0]
	 */
	public function get_index_atlas_repartition($srid, $pas) {
		$q = bobs_qm()->query($this->db, 'espace_index_c_rep', self::sql_index_atlas, array($this->get_table(), $this->id_espace, $srid, $pas));
		return self::fetch_all($q);
	}

	/**
	 * @brief converti une geométrie en GeoJson vers le format WKT
	 * @param $geojson_str str
	 * @return wkt str
	 */
	public static function wkt_depuis_geojson($geojson_str) {
		$o = json_decode($geojson_str);
		switch ($o->type) {
			case 'Polygon':
				$wkt = 'POLYGON(';
				foreach ($o->coordinates as $c) {
					$wkt .= '(';
					foreach ($c as $e) {
						$x = str_replace(',','.',$e[0]);
						$y = str_replace(',','.',$e[1]);
						$wkt .= "$x $y,";
					}
					$wkt = trim($wkt, ',');
					$wkt .= '),';
				}
				$wkt = trim($wkt,',');
				$wkt.= ')';
				break;
			case 'LineString':
				$wkt = 'LINESTRING(';
				foreach ($o->coordinates as $c) {
					$x = str_replace(',','.',$c[0]);
					$y = str_replace(',','.',$c[1]);
					$wkt .= "$x $y,";
				}
				$wkt = trim($wkt,',');
				$wkt.= ')';
				break;
			default:
				throw new \Exception("{$o->type} inconnu");

		}
		return $wkt;
	}

	function exportJSON($geom=null) {
		$t = array();
		foreach ($this->champs_export_json as $prop)
			$t["$prop"] = $this->$prop;

		if (!empty($geom)) {
			$this->$geom = $this->get_geom($geom);
			$t["$geom"] = $this->$geom;
		}

		return json_encode($t);
	}

	const sql_espace_intersect = 'select * from espace_intersect where id_espace_obs=$1 and table_espace_obs=$2 and table_espace_ref=$3';

	/**
	 * @brief espace de référence intersecté par l'objet
	 * @param $table_espace_ref nom de la table de référence
	 * @return clicnat_iterateur_espaces
	 */
	protected function get_espaces_intersects($table_espace_ref) {
		$q = bobs_qm()->query($this->db, 'sql_espace_intersect', self::sql_espace_intersect, array($this->id_espace, $this->table, $table_espace_ref));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = array(
				"espace_table" => $r['table_espace_ref'],
				"id_espace" => $r['id_espace_ref']
			);
		}
		return new clicnat_iterateur_espaces($this->db, $t);
	}

	const sql_recherche_espace = "
		select * from %s
		where unaccent(lower(regexp_replace(nom,'-',' ','g'))) ilike unaccent(lower(regexp_replace($1||'%%','-',' ','g')))
		order by nom";

	public static function rechercher($db, $args, $table_espace) {
		self::cls($args['nom']);

		if (empty($args['nom']))
			return array();

		$q = bobs_qm()->query($db, 'srch_'.$table_espace, sprintf(self::sql_recherche_espace,$table_espace), array($args['nom']));

		$tr = array();

		while ($r = self::fetch($q)) {
			$tr[] = get_espace($db, $table_espace, $r);
		}

		return $tr;
	}

	/**
	 * @brief retourne la géométrie dans le format WKT
	 * @deprecated
	 */
	public function get_geom($type) {
		$qm = bobs_qm();

		$q_name = 'esp_ggeom_'.$this->table;

		$sql = sprintf('select astext(the_geom) as g
				from %s where id_espace=$1',
				$this->table);

		$q = $qm->query($this->db, $q_name, $sql, array($this->id_espace));
		$t = self::fetch($q);

		if ($t) {
			if (!empty($type))
				$this->$type = $t['g'];
			return $t['g'];
		}

		return null;
	}

	const sql_get_gml = 'select ST_AsGML(st_transform(the_geom,$2)) as gml from %s where id_espace=$1';
	const sql_get_kml = 'select ST_AsKML(the_geom) as kml from %s where id_espace=$1';
	const sql_get_geojson = 'select ST_AsGeoJson(the_geom) as json from %s where id_espace=$1';

	/**
	 * @brief retourne la géométrie dans le format GML
	 */
	public function get_geom_gml($srid=4326) {
		self::cli($this->id_espace, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'esp_get_gml_'.$this->table, sprintf(self::sql_get_gml, $this->table), array($this->id_espace,(int)$srid));
		$r = self::fetch($q);
		return $r['gml'];
	}

	/**
	 * @brief retourne la géométrie dans le format KML
	 */
	public function get_geom_kml() {
		self::cli($this->id_espace, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'esp_get_kml_'.$this->table, sprintf(self::sql_get_kml, $this->table), array($this->id_espace));
		$r = self::fetch($q);
		return $r['kml'];
	}

	public function get_geom_json() {
		self::cli($this->id_espace, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'esp_json_'.$this->table, sprintf(self::sql_get_geojson, $this->table), array($this->id_espace));
		$r = self::fetch($q);

		return "{\"type\": \"Feature\", \"geometry\": {$r['json']}}";
	}

	const sql_get_centroid = 'select st_x(centroid(the_geom)) as x, st_y(centroid(the_geom)) as y from %s where id_espace=$1';

	/**
	 * @brief retourne le centroid de la geometry
	 * @return array [x,y]
	 */
	public function get_centroid() {
		self::cli($this->id_espace, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'esp_get_centroid_'.$this->table, sprintf(self::sql_get_centroid, $this->table), array($this->id_espace));
		$r = self::fetch($q);
		return $r;
	}

	/**
	 * @brief retourne l'enveloppe de la géométrie
	 * @return chaine wkt
	 */
	public function get_envelope() {
		self::cli($this->id_espace, self::except_si_inf_1);

		$sql = sprintf('select astext(st_envelope(the_geom)) as g
				from %s where id_espace=$1',
				$this->table);

		$q = bobs_qm()->query($this->db, 'esp_gmenv_'.$this->table, $sql, array($this->id_espace));
		$t = self::fetch($q);


		if ($t) {
			return $t['g'];
		} else {
			throw new \Exception("no result {$this->table}.{$this->id_espace}");
		}

		return null;
	}


	public static function get_espaces_in_poly($db, $typetab, $poly) {
		$classe = "bobs_espace_$typetab";
		if (!class_exists($classe))
			throw new \Exception("la classe {$classe} n'existe pas");

		$sql = sprintf("select *,astext(the_geom) as geom
					from espace_%s
					where contains(GeomFromText('%s',%s),the_geom)",
			self::escape($typetab), self::escape($poly), SRID_BY_DEFAULT);

		$t = Array();

		$q = self::query($db, $sql);
		while ($r = self::fetch($q))
			$t[] = new $classe($db, $r);

		return $t;
	}

	/**
	 * @brief rercherche les polygones où se trouve le point
	 * @return clicnat_iterateur_espaces
	 */
	protected static function __get_espaces_in_point($db, $table, $classe, $x, $y) {
		$table = self::escape($table);
		$sql = sprintf("select id_espace from %s
			where the_geom && geomfromtext(setsrid(geomfromtext('POINT(%F %F)'),%d))
			and intersects(the_geom,geomfromtext(setsrid(geomfromtext('POINT(%F %F)'),%d)))",
			$table, $x, $y, SRID_BY_DEFAULT, $x, $y, SRID_BY_DEFAULT);
		$q = self::query($db, $sql);
		$t = array();
		if (!class_exists($classe))
			throw new \Exception("la classe $classe existe pas");
		while ($r = self::fetch($q)) {
			$t[] = array('espace_table' => $table, 'id_espace' => $r['id_espace']);
		}
		return new clicnat_iterateur_espaces($db, $t);
	}

	/**
	 *
	 * @deprecated
	 *
	 * encore utilisé dans le QG
	 */
	public function getObservations($prechargeEspecesVues=false) {
		$sql = sprintf("select *
					from observations
					where id_espace = %d
					order by date_observation desc",
				$this->id_espace);
		$t = Array();
		$q = self::query($this->db, $sql);

		$observateurs = array();

		while ($r = self::fetch($q))
			$t[] = new bobs_observation($this->db, $r, $this, $observateurs);

		if ($prechargeEspecesVues)
			foreach ($t as $obs)
				$obs->get_especes_vues();

		return $t;
	}

	/**
	 * @brief retourne les observations directement associées
	 * @return bobs_observation[]
	 */
	public function get_observations()
	{
	    $t = array();
	    $sql = 'select * from observations
		    where id_espace=$1 and espace_table=$2
		    order by date_observation desc';
	    $q = bobs_qm()->query($this->db, 'espace-obs-q', $sql, array($this->id_espace, $this->table));
	    while ($r = self::fetch($q)) {
		$t[] = get_observation($this->db, $r);
	    }
	    return $t;
	}

	/**
	 * @brief comme get_observations() mais uniquement ce que l'utilisateur est autorisé a voir
	 * @param int $id_utilisateur un numéro d'utilisateur
	 * @return bobs_observations[]
	 */
	public function get_observations_auth_ok($id_utilisateur) {
	    self::cli($id_utilisateur);
	    $t = array();
	    $sql = 'select distinct observations.*
		    from observations, citations
		    where id_espace=$1 and espace_table=$2
		    and observations.id_observation=citations.id_observation
		    and exists(select 1 from utilisateur_citations_ok
		    		where utilisateur_citations_ok.id_utilisateur=$3
				and utilisateur_citations_ok.id_citation=citations.id_citation
		    )
		    order by date_observation desc';
	    $q = bobs_qm()->query($this->db, 'espace-obs-qao', $sql,
		    array($this->id_espace, $this->table, $id_utilisateur));
	    while ($r = self::fetch($q)) {
			$t[] = get_observation($this->db, $r);
	    }
	    return $t;
	}

	public static function __get_by_nom($db, $table, $classe, $nom) {
	    $sql = 'select * from '.$table.' where nom = trim($1)';
	    $q = bobs_qm()->query($db, 'esnom_'.$table, $sql, array($nom));
	    $r = bobs_element::fetch($q);

	    if (empty($r['id_espace']))
		return false;

	    return new $classe($db, $r);
	}

	public static function __get_by_ref($db, $table, $classe, $ref) {
	    $sql = 'select * from '.$table.' where reference = trim($1)';
	    $q = bobs_qm()->query($db, 'esref_'.$table, $sql, array($ref));
	    $r = bobs_element::fetch($q);

	    if (empty($r['id_espace']))
		return false;

	    return new $classe($db, $r);
	}

	public function get_tags() {
	    $where = 'and id_espace=$1 and espace_table=\''.$this->table.'\'';
	    $this->tags = $this->__get_tags(BOBS_TBL_TAG_ESPACE, $this->id_espace, $where);
	    return $this->tags;
	}

	/**
	 * @brief Liste les espaces
	 * @param ressource $db
	 * @param string $table
	 * @return array
	 */
	public static function get_list($db, $table) {
	    $sql = "select * from $table order by nom";
	    $q = bobs_qm()->query($db, 'esp_list_'.$table, $sql, array());
	    return self::fetch_all($q);
	}

	/**
	 *
	 * @return bobs_calendrier[] calendriers associés
	 */
	public function get_calendriers() {
	    $sql = 'select * from calendriers_dates
		    where espace_table=$1 and id_espace=$2
		    order by date_sortie desc';
	    $q = bobs_qm()->query($this->db, 'esp_g_cal', $sql,
		    array($this->table, $this->id_espace));
	    $t = array();
	    while ($r = self::fetch($q))
		$t[] = new bobs_calendrier($this->db, $r);
	    return $t;
	}

	public function prospection_prevue() {
	    $sql = 'select max(date_sortie) as s from calendriers_dates
		    where espace_table=$1 and id_espace=$2
		    group by date_sortie
		    order by date_sortie desc';
	    $q = bobs_qm()->query($this->db, 'esp_g_prevue', $sql,
		    array($this->table, $this->id_espace));
	    $r = self::fetch($q);

	    if (!isset($r['s'])) return false;
	    return strtotime($r['s'])>mktime();
	}


	/**
	 * @brief Insertion d'une géométrie dans la base
	 * @param $db ressource base de donnée
	 * @param $data propriétées de la géométrie
	 * @param $table table où elle va être insérée
	 *
	 * Entrées du tableau data :
	 *  - id_utilisateur
	 *  - reference
	 *  - nom
	 *  - wkt
	 *
	 * Les coordonnées du WKT doivent être en WGS84
	 */
	public static function insert_wkt($db, $data, $table='espace') {
		$id_espace = self::nextval($db, 'espace_id_espace_seq');

		if (empty($id_espace))
			throw new \Exception('id_espace vide (échec nextval)');

		self::cli($data['id_utilisateur']);
		self::cls($data['reference']);
		self::cls($data['nom']);
		self::cls($data['wkt']);
		self::cls($table);

		$sql = "insert into $table (id_espace,id_utilisateur,reference,nom,the_geom)".
			'values ($1,$2,$3,$4,st_setsrid(st_geomfromtext($5,$6),4326))';

		$qa = array(
			$id_espace,
			$data['id_utilisateur'],
			$data['reference'],
			$data['nom'],
			$data['wkt'],
			SRID_BY_DEFAULT
		);
		bobs_qm()->query($db, $table.'_insert_wkt', $sql, $qa);

		return $id_espace;
	}

	/**
	 * @brief Insertion d'une géométrie dans la base
	 * @param $db ressource base de donnée
	 * @param $data propriétées de la géométrie
	 * @param $table table où elle va être insérée
	 *
	 * Entrées du tableau data :
	 *  - id_utilisateur
	 *  - reference
	 *  - nom
	 *  - xml
	 *
	 * Les coordonnées du WKT doivent être en WGS84
	 */
	public static function insert_kml($db, $data, $table='espace') {
		$id_espace = self::nextval($db, 'espace_id_espace_seq');

		if (empty($id_espace))
			throw new \Exception('id_espace vide (échec nextval)');

		self::cli($data['id_utilisateur']);
		self::cls($data['reference']);
		self::cls($data['nom']);
		self::cls($data['xml']);
		self::cls($table);

		$sql = "insert into $table (id_espace,id_utilisateur,reference,nom,the_geom)".
			'values ($1,$2,$3,$4,st_setsrid(st_geomfromkml($5),$6))';

		$qa = array(
			$id_espace,
			$data['id_utilisateur'],
			$data['reference'],
			$data['nom'],
			$data['xml'],
			SRID_BY_DEFAULT
		);
		try  {
			bobs_qm()->query($db, $table.'_insert_xml', $sql, $qa);
		} catch (\Exception $e) {
			echo "<pre>kml:";
			echo htmlentities($data['xml']);
			echo "</pre>";
			throw $e;
		}

		return $id_espace;
	}




	const sql_del_espace = 'delete from %s where id_espace=$1';

	/**
	 * @brief Supprime un espace
	 */
	public function supprimer() {
		if ($this->a_des_observations()) {
			throw new \Exception('suppression espace utilisé par des observations impossible');
		}

		if ($this->est_dans_des_repertoires()) {
			throw new \Exception('suppression espace utilisé dans des répertoires');
		}

		$sql = sprintf(self::sql_del_espace, $this->get_table());
		return bobs_qm()->query($this->db, 'espace_del_'.$this->get_table(), $sql, array($this->id_espace));
	}

	const sql_deplacer_obs = 'update observations set id_espace=$3, espace_table=$2 where id_espace=$1';

	public function deplacer_observations($nouvel_espace) {
		return bobs_qm()->query($this->db, 'espace_deplacer_obs', self::sql_deplacer_obs, array($this->id_espace, $nouvel_espace->get_table(), $nouvel_espace->id_espace));
	}
	const sql_test_obs = 'select exists(select * from %s where id_espace=$1) as e';

	private function existence_id_espace($table) {
		$sql = sprintf(self::sql_test_obs, $table);
		$r = self::fetch(bobs_qm()->query($this->db, "espace_stest_$table", $sql, array($this->id_espace)));
		return $r['e'] == 't';
	}

	/**
	 * @brief Test si l'espace est associé à des observations
	 * @return boolean
	 */
	public function a_des_observations() {
		return $this->existence_id_espace("observations");
	}

	/**
	 * @brief Test si l'espace est associé à un répertoire
	 * @return boolean
	 */
	public function est_dans_des_repertoires() {
		return $this->existence_id_espace("utilisateur_repertoire");
	}
}
