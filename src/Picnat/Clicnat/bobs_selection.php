<?php
namespace Picnat\Clicnat;

/**
 * @brief Sélection de citations (voir ça comme un panier)
 * @property-read id_selection
 * @property-read id_utilisateur
 * @property-read nom_selection
 * @property-read nom
 * @property-read date_creation
 * @property-read partage_qg
 * @property-read extraction_xml
 */
class bobs_selection extends bobs_element {
	protected $id_selection;
	protected $id_utilisateur;
	protected $nom_selection;
	protected $date_creation;
	protected $partage_qg;
	protected $extraction_xml;
	protected $date_modif;

	public function __construct($db, $id) {
		parent::__construct($db, 'selection', 'id_selection', $id);
		$this->champ_date_maj = 'date_modif';
	}

	public function __toString() {
		return $this->nom_selection;
	}

	public function __get($prop) {
		switch ($prop) {
			case 'id_selection':
				return $this->id_selection;
			case 'id_utilisateur':
				return $this->id_utilisateur;
			case 'nom_selection':
			case 'nom':
				return $this->nom_selection;
			case 'date_creation':
				return $this->date_creation;
			case 'partage_qg':
				return $this->partage_qg == 't';
			case 'extraction_xml':
				return $this->extraction_xml;
			default:
				throw new \Exception('propriétée inconnue ou inaccessible "'.$prop.'"');
		}
	}

	const sql_nbre_carres = 'select pas,srid,count(distinct x0||\'-\'||y0) from selection_data sd,citations c,observations o,espace_index_atlas ei where id_selection = $1 and c.id_citation=sd.id_citation and o.id_observation=c.id_observation and ei.id_espace=o.id_espace group by pas,srid';

	/**
	 * @brief liste les grilles dans l'index et le nombre carrés occupés par la sélection
	 */
	public function nombre_de_carres() {
		$q = bobs_qm()->query($this->db, 'sel_liste_carres', self::sql_nbre_carres, array($this->id_selection));
		return self::fetch_all($q);
	}

	const sql_nbre_carres_esp = 'select x0,y0,count(distinct id_citation) as count_citation from selection_data sd,citations c,observations o,espace_index_atlas ei where id_selection = $1 and c.id_citation=sd.id_citation and o.id_observation=c.id_observation and ei.id_espace=o.id_espace and c.id_espece=$2 and srid=$3 and pas=$4 group by x0,y0';

	const sql_liste_carres = '
		select x0,y0,count(distinct c.id_citation) as count_citation, count(distinct c.id_espece) as count_especes, json_agg(c.id_citation) as citations
		from selection_data sd,citations c,observations o,espace_index_atlas ei
		where id_selection = $1 and c.id_citation=sd.id_citation and o.id_observation=c.id_observation and ei.id_espace=o.id_espace and srid=$2 and pas=$3
		group by x0,y0
	';

	/**
	 * @brief liste les carrés occupés par l'espèce pour la sélection
	 * @param $pas pas de la grille
	 * @param $srid système de coordonnées de la grille
	 * @param $espece instance de l'espèce
	 * @return array x0,y0,count_citation
	 */
	public function carres_espece($pas,$srid,$espece) {
		$q = bobs_qm()->query($this->db, 'sel_nbre_carres_esp', self::sql_nbre_carres, [$this->id_selection,$espece->id_espece,$srid,$pas]);
		return self::fetch_all($q);
	}

	/**
	 * @brief liste des carrés occupés avec nbre de taxon et nbre de citations
	 * @param $pas pas de la grille
	 * @param $srid srid de la grille
	 * @return array x0,y0,count_citation,count_selection
	 */
	public function carres_nespeces_ncitations($pas,$srid) {
		$q = bobs_qm()->query($this->db, 'sel_carres_esp_cit', self::sql_liste_carres, [$this->id_selection,$srid,$pas]);
		return self::fetch_all($q);
	}


	const sql_par_nom = 'select id_selection from selection where id_utilisateur=$1 and nom_selection=$2 limit 1';

	/**
	 * @brief Créé ou récupérer une sélection avec un nom donné pour un utilisateur
	 */
	public static function par_nom_ou_creer($db, $id_utilisateur, $nom) {
		$q = bobs_qm()->query($db, 'sel_par_nom_ou', self::sql_par_nom, [$id_utilisateur, $nom]);
		$r = self::fetch($q);

		if (isset($r['id_selection'])) {
			$id_selection = $r['id_selection'];
		} else {
			$id_selection = self::__nouvelle($db, $id_utilisateur, $nom, null);

		}
		return get_selection($db, $id_selection);
	}


	/**
	 * @brief Création d'une nouvelle sélection
	 * @param ressource $db
	 * @param integer $id_utilisateur
	 * @param string $nom
	 * @param integer $id_selection
	 */
	protected static function __nouvelle($db, $id_utilisateur, $nom, $id_selection) {
		self::cli($id_utilisateur);
		self::cls($nom);

		if (empty($id_utilisateur) or empty($nom))
			throw new InvalidArgumentException("id_utilisateur=$id_utilisateur et nom=$nom");

		if (is_null($id_selection))
			$id_selection = self::nextval($db, 'selection_id_selection_seq');

		self::insert($db, 'selection',
			array(
				'id_selection' => $id_selection,
				'id_utilisateur' => $id_utilisateur,
				'nom_selection' => $nom,
				'date_creation' => strftime('%Y-%m-%d', mktime())
			)
		);

		return $id_selection;
	}

	/**
	 * @brief création d'une nouvelle sélection
	 * @param handler $db
	 * @param int $id_utilisateur
	 * @param int $nom
	 * @return int new selection id
	 */
	public static function nouvelle($db, $id_utilisateur, $nom) {
		return self::__nouvelle($db, $id_utilisateur, $nom, null);
	}

	/**
	 * @return bobs_utilisateur
	 */
	public function get_utilisateur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}


	/**
	 * @brief liste de sélection
	 * @param $db une connection à la base de données
	 * @param $id_utilisateur le numéro de l'utilisateur
	 * @param $tri le critère de tri ('date_creation' ou 'nom')
	 * @return array un tableau (pas des objets)
	 */
	public static function liste($db, $id_utilisateur, $tri) {
		self::cli($id_utilisateur);
		self::cls($tri);

		if (empty($id_utilisateur) or empty($tri))
			throw new \InvalidArgumentException();

		if (!in_array($tri, array('date_creation', 'nom', 'id_selection')))
			throw new \InvalidArgumentException();

		if (in_array($tri, array('date_creation','id_selection')))
			$tri .= ' desc';

		$sql = sprintf("select * from selection where id_utilisateur=%d order by %s", $id_utilisateur, $tri);

		return self::query_fetch_all($db, $sql);
	}

	/**
	 * @brief fusionne plusieurs selections
	 * @param $db connection à la base de données
	 * @param $utilisateur un objet bobs_utilisateur
	 * @param $nom_selection le nom d'une sélection
	 * @param $selections un tableau de numéro de sélection
	 * @return le résultat de la requête
	 */
	public static function fusionner($db,$utilisateur,$nom_selection,$selections) {
		$id_selection = $utilisateur->selection_creer($nom_selection);
		$in = '';
		foreach ($selections as $s) {
			$in .= sprintf("%d,",$s->id_selection);
		}
		$in = trim($in, ',');
		return self::query($db,"insert into selection_data (id_selection,id_citation) select distinct $id_selection,id_citation from selection_data where id_selection in ($in)");
	}

	/**
	 * @brief liste des sélections partagées dans le QG
	 * @param $db une connection à la base de données
	 * @return array un tableau de ligne de la table 'selection' (pas des objets)
	 */
	public static function liste_qg($db) {
		return self::fetch_all(bobs_qm()->query($db, 'sel-list-qg',
			"select selection.*,coalesce(utilisateur.nom, '')||' '||coalesce(utilisateur.prenom) as proprietaire
			    from selection,utilisateur where partage_qg=true and utilisateur.id_utilisateur=selection.id_utilisateur",
			array()));
	}

	const sql_vider_a = 'delete from selection_mix_annees where id_selection=$1';
	const sql_vider_b = 'delete from selection_data where id_selection=$1';

	/**
	 * @brief vide la sélection de son contenu
	 * @return boolean
	 */
	public function vider() {
		self::cli($this->id_selection);
		if (!bobs_qm()->query($this->db, 'sel-del-a', self::sql_vider_a, array($this->id_selection))) return false;
		if (!bobs_qm()->query($this->db, 'sel-del-b', self::sql_vider_b, array($this->id_selection))) return false;
		$this->update_date_maj_field();
		return true;
	}

	public function ajouter($id_citation) {
		self::cli($id_citation);
		return $this->ajouter_ids(array($id_citation));
	}

	public function ajouter_objs($tableau_obj_citations) {
		$t_ids = array();

		foreach ($tableau_obj_citations as $cit)
			if (!empty($cit->id_citation))
				$t_ids[] = $cit->id_citation;

		return $this->ajouter_ids($t_ids);
	}

	public function ajouter_ids($tableau_citation_id) {
		$t = array();

		foreach ($tableau_citation_id as $id) {
			$id = sprintf("%d", $id);
			if (!empty($id))
				$t[] = $id;
		}

		if (count($tableau_citation_id) <= 0)
			return false;

		$in_str = '';
		foreach ($t as $id)
			$in_str .= ','.$id;

		$in_str = trim($in_str,',');

		$sql = sprintf('insert into selection_data
				(id_selection,id_citation)
				    select %d, id_citation from citations
					    where id_citation in (%s)',
					    $this->id_selection, $in_str);

		$this->update_date_maj_field();

		return bobs_element::query($this->db, $sql);
	}

	public function enlever($id_citation) {
		if (empty($id_citation))
			return false;

		return $this->enlever_ids(array($id_citation));
	}

	public function enlever_ids($tableau_citation_id) {
		if (!is_array($tableau_citation_id)) {
			throw new InvalidArgumentException("ids est pas un tableau");
			return false;
		}

		if (count($tableau_citation_id) <= 0) {
			throw new LengthException("la liste est vide");
			return false;
		}

		$in_str = '';
		foreach ($tableau_citation_id as $id)
			$in_str .= ','.$id;
		$in_str = sprintf('(%s)', trim($in_str,','), $in_str);
		$sql = sprintf("delete from selection_data
				where id_selection=%d
				and id_citation in %s",
				$this->id_selection, $in_str);

		$this->update_date_maj_field();

		return self::query($this->db, $sql);
	}

	const sql_n = 'select count(*) as n from selection_data where id_selection=$1';

	/**
	 * @brief retourne le nombre de citations dans la sélection
	 * @return int un entier
	 */
	public function n() {
		$q = bobs_qm()->query($this->db, 'sel-count-cit', self::sql_n, array($this->id_selection));
		$r = self::fetch($q);
		return $r['n'];
	}

	public function change_nom($nom) {
		self::cls($nom);

		if (empty($nom)) return false;

		$this->update_field('nom_selection', $nom);
		$this->nom_selection = $nom;
		return true;
	}

	public function change_extraction_xml($xml) {
		return $this->update_field('extraction_xml', $xml);
	}

	const sql_tab_citations = "select sd.id_citation
			from
				selection_data sd,
				citations c,
				observations o
			where
				sd.id_selection=$1 and
				sd.id_citation=c.id_citation and
				o.id_observation=c.id_observation
			order by
				o.date_observation desc ";

	public function tab_citations($n=0, $page=0) {
		if ($n > 0) {
			$sql = sprintf("%s limit %d offset %d", self::sql_tab_citations , $n, $page*$n);
		} else {
			$sql = self::sql_tab_citations;
		}

		$q = bobs_qm()->query($this->db, "t_citations_{$n}_{$page}", $sql, array($this->id_selection));
		$r = self::fetch_all($q);
		$citations = new clicnat_iterateur_citations($this->db, array_column($r, 'id_citation'));
		$data = array();
		foreach ($citations as $c) {
			$lieu = "...";
			$n = "...";
			$observ = "";
			try {
				$e = $c->get_observation()->get_espace();
				$lieu = $e->__toString();
				if ($lieu == bobs_espace::nom_par_defaut) $lieu = '';
				else $lieu .= " ";
				foreach ($e->get_communes() as $commune) {
					$lieu .= $commune->__toString().", ";
				}
				$lieu = trim(trim($lieu," "), ',');
			} catch (Exception $e) {
				$lieu = "impossible a nommer";
			}
			if (!is_null($c->nb_min) && !is_null($c->nb_max)) {
				$n = "{$c->nb_min} - {$c->nb_max}";
			} else {
				if ($c->nb < 0)
					$n = "prospection nég.";
				else
					$n = $c->nb;
			}
			foreach ($c->get_observation()->get_observateurs() as $obser) {
				$observ .= "{$obser['prenom']} {$obser['nom']}, ";
			}
			$observ = trim(trim($observ, " "),",");
			$data[] = array(
				'id_citation' => $c->id_citation,
				'date' => strftime("%d-%m-%Y", strtotime($c->get_observation()->date_observation)),
				'lieu' => $lieu,
				'nom_f' => $c->get_espece()->nom_f,
				'nom_s' => $c->get_espece()->nom_s,
				'nb' => $n,
				'observateurs' => $observ,
				'id_observation' => $c->id_observation,
				'espace_table' => $c->get_observation()->espace_table,
				'id_espace' => $c->get_observation()->id_espace
			);
		}
		return $data;
	}

	public function get_datatable($args) {
		$sql = "select *,o.date_observation,coalesce(nom_f,nom_s) as nom
			from citations c,observations o, selection_data sd,especes e
			where sd.id_selection=$1
			and sd.id_citation = c.id_citation
			and c.id_observation = o.id_observation
			and c.id_espece=e.id_espece";
		$q_nom = 'gdatatble_';

		if (array_key_exists('in', $args)) {
			$s_in = '';
			foreach (explode(',',$args['in']) as $id_citation) {
				$s_in .= intval($id_citation).',';
			}
			$s_in = '('.trim($s_in,',').')';
			$q_nom = md5($q_nom.$s_in);
			$sql .= ' and c.id_citation in '.$s_in;
		}
		$sort = false;
		switch ($args['iSortCol_0']) {
			case 0:
				$sql .=  " order by c.id_citation ";
				$sort = true;
				break;
			case 1:
				$sql .=  " order by o.date_observation ";
				$sort = true;
				break;
			case 2: // effectif
				$sql .= " order by coalesce(c.nb,0) ";
				$sort = true;
				break;
			case 3: // nom esp
				$sql .= " order by coalesce(nom_f,nom_s) ";
				$sort = true;
				break;
		}
		if ($sort) {
			$q_nom .= '_s'.intval($args['iSortCol_0']);
			switch ($args['sSortDir_0']) {
				case 'asc':
					$sql .= ' asc';
					break;
				case 'desc':
					$sql .= ' desc';
					break;
			}
		}

		$sql .= sprintf(" limit %d offset %d ", $args['iDisplayLength'], $args['iDisplayStart']);
		$q_nom = sprintf("%s-f%d-t%d", $q_nom, $args['iDisplayLength'], $args['iDisplayStart']);
		$q = bobs_qm()->query($this->db, $q_nom, $sql , array($this->id_selection));
		if (!array_key_exists('in', $args)) {
			$n = $this->n();
		} else {
			$n = count(explode(',', $_GET['in']));
		}
		$rep = array(
			'sEcho' => $args['sEcho'],
			'iTotalRecords' => $n,
			'iTotalDisplayRecords' => $n,
			'aaData' => array()
		);
		while ($r = self::fetch($q)) {
			$citation = get_citation($this->db, $r);
			$observation = $citation->get_observation();
			$espace = $observation->get_espace();
			$n = "-";
			if ($r['nb'] < 0) $n = "prosp. négative";
			if ($r['nb'] > 0) $n = $r['nb'];
			$rep['aaData'][] = array(
				$r['id_citation'],
				strftime("%d-%m-%Y", strtotime($r['date_observation'])),
				$n,
				$r['nom'],
				$espace->__toString(),
				$observation->get_observateurs_str(),
				$citation->get_str_tags()
			);
		}
		return $rep;
	}

	public function get_id_utilisateur() {
		return self::cli($this->id_utilisateur);
	}

	/**
	 * @brief liste les observateurs
	 * @return array liste des observateurs (pas objet)
	 */
	public function get_observateurs() {
		self::cli($this->id_selection);
		$sql = 'select distinct u.*,count(c.id_citation) as n_citations
			from
				selection_data sd, citations c,
				observations_observateurs oo,
				utilisateur u
			where sd.id_selection=$1
			and sd.id_citation=c.id_citation
			and c.id_observation=oo.id_observation
			and oo.id_utilisateur=u.id_utilisateur
			group by u.id_utilisateur
			order by u.nom,u.prenom';
		$q = bobs_qm()->query($this->db, 'sel_obs_get', $sql, array($this->id_selection));
		return self::fetch_all($q);
	}

	const sql_get_auteurs = 'select distinct u.*,count(c.id_citation) as n_citations
				from utilisateur u,citations c, selection_data sd, observations o
				where sd.id_selection=$1
				and sd.id_citation=c.id_citation
				and o.id_observation=c.id_observation
				and u.id_utilisateur=o.id_utilisateur
				group by u.id_utilisateur
				order by u.nom,u.prenom';
	/**
	 * @brief liste les auteurs de citations
	 * @return array liste des observateurs (pas objet)
	 */
	public function get_auteurs() {
		self::cli($this->id_selection);
		$q = bobs_qm()->query($this->db, 'sel_l_obs_get', self::sql_get_auteurs, array($this->id_selection));
		return self::fetch_all($q);
	}

	const sql_get_especes = 'select distinct e.*,count(c.id_citation) as n_citations
		from selection_data sd, citations c, especes e
		where sd.id_selection=$1
		and sd.id_citation=c.id_citation
		and c.id_espece=e.id_espece
		group by e.id_espece
		order by classe,ordre,nom_f,nom_s';

	/**
	 * @brief liste des espèces dans la sélection
	 * @return array liste des especes
	 */
	public function get_especes() {
		self::cli($this->id_selection);
		$q = bobs_qm()->query($this->db, 'selection_get_especes', self::sql_get_especes, array($this->id_selection));
		return self::fetch_all($q);
	}


	const sql_get_classes = 'select classe,count(*) as n
		from selection_data sd,citations c,especes e
		where sd.id_selection=$1
		and sd.id_citation=c.id_citation
		and c.id_espece=e.id_espece
		group by classe';
	/**
	 * @brief liste les classes d'espèce de la sélection
	 * @return array bobs_classe
	 */
	public function get_classes() {
		$q = bobs_qm()->query($this->db, 'selection_get_classes', self::sql_get_classes, array($this->id_selection));
		$t = self::fetch_all($q);
		foreach ($t as $k=>$v)
			$t[$k]['obj'] = get_classe($this->db, $v['classe']);
		return $t;
	}

	/**
	 * @brief liste des espèces dans la sélection
	 * @return array iterateur clicnat_iterateur_especes
	 */
	public function especes() {
		$especes = $this->get_especes();
		$ids = array();
		foreach ($especes as $e) $ids[] = $e['id_espece'];
		unset($especes);
		return new clicnat_iterateur_especes($this->db, $ids);
	}


	const sql_drop = 'delete from selection where id_selection=$1';

	/**
	 * @brief supprimer la sélection
	 */
	public function drop() {
	    self::cli($this->id_selection);
	    $this->vider();
	    bobs_qm()->query($this->db, 'selection_drop', self::sql_drop , array($this->id_selection));
	}

	const sql_liste_type_geom = 'select distinct espace_table from observations o, citations c, selection_data sd
		where sd.id_citation=c.id_citation and
		o.id_observation=c.id_observation and
		sd.id_selection=$1';

	public function liste_espaces_table() {
		$t=array();
		$tt = self::fetch_all(bobs_qm()->query($this->db, 'sel_liste_table_esp', self::sql_liste_type_geom, array($this->id_selection)));
		return array_column($tt, 'espace_table');
	}

	/**
	 * Création d'un shapefile pour la sélection
	 * @param string $path chemin où extraire le fichier
	 * @param int $epsg_id numéro de la projection
	 */
	public function extract_shp($path, $epsg_id) {
		self::cli($epsg_id);
		self::cls($path);

		if (empty($path))
			throw new \Exception('empty path');
	    	foreach ($this->liste_espaces_table() as $table) {
			$l = exec(sprintf('%s %d %s %d %s', BOBS_BIN_EXTRACT_SELECTION, $this->id_selection, $path, $epsg_id,$table),$o,$rv);
			bobs_log("exec ($rv): $l");
		}
	}

	const bin_extract_selection_cchiro = "/usr/local/bin/extract_selection_complete_chiro";
	const bin_extract_selection_mix = "/usr/local/bin/shp_selection_mix";

	public function extract_shp_chiro($path, $epsg_id) {
		$this->extract_shp($path, $epsg_id);

		if (!file_exists(self::bin_extract_selection_cchiro))
			throw new \Exception('Le programme d\'extraction n\'est pas installé');

		$l = exec(sprintf("%s %s/points.shp", self::bin_extract_selection_cchiro, $path), $output, $rv);

		bobs_log("exec ($rv): $l");
	}


	public function extract_shp_mix_zip($epsg_id, $pas) {
		if (!file_exists(self::bin_extract_selection_mix))
			throw new \Exception('Le programme d\'extraction n\'est pas installé');

		$chemin = exec(sprintf("%s %d %d %d", self::bin_extract_selection_mix, $this->id_selection, $pas, $epsg_id));

		if (file_exists($chemin)) {
			$zip = $chemin.".zip";
			if (file_exists($zip)) unlink($zip);
			$zf = new \ZipArchive();
			$zf->open($zip, \ZipArchive::CREATE);
			$zf->setArchiveComment($this->nom_selection);
			foreach (glob($chemin.'/*') as $filename) {
				$zf->addFile($filename, "selection_{$this->id_selection}/".basename($filename));
			}
			$zf->close();
			return $zip;
		}

		return false;
	}

	/**
	 * Retourne le contenu de la sélection dans un shapefile zippé
	 * @param int $epsg_id numéro de projection
	 * @return string chemin vers le zip
	 */
	public function extract_shp_zip(&$epsg_id, $type=BOBS_EXTRACT_SHP_NORMAL) {
	    self::cli($espg_id);
	    self::cli($this->id_selection);

	    $zip = sprintf(BOBS_EXTRACTSHP_TMP.'.zip', $this->id_selection);
	    $dir = sprintf(BOBS_EXTRACTSHP_TMP, $this->id_selection);

	    if (file_exists($zip)) {
		bobs_log("rm $zip");
		unlink($zip);
	    }

	    if (file_exists($dir)) {
		foreach (glob($dir.'/*') as $f) {
			bobs_log("rm $f");
			unlink($f);
		}
		rmdir($dir);
	    }
	    switch ($type) {
		case BOBS_EXTRACT_SHP_NORMAL:
			$this->extract_shp($dir, $epsg_id);
			break;
		case BOBS_EXTRACT_SHP_NCHIRO:
			$this->extract_shp_chiro($dir, $epsg_id);
			break;
		case BOBS_EXTRACT_SHP_1KM:
		    $epsg_id = 2154;
		    $this->extract_shp_atlas($dir);
		    break;
		case BOBS_EXTRACT_SHP_MIX:
		    $epsg_id = 2154;
		    $this->extract_shp_atlas_mix($dir);
		    foreach (glob($dir.'/points.*') as $filename) {
		    	bobs_log('supprime fichier '.$filename);
		    	unlink($filename);
		    }
		    break;
	    }

	    $zf = new ZipArchive();
	    $zf->open($zip, ZipArchive::CREATE);
	    $zf->setArchiveComment($this->nom_selection);
	    foreach (glob($dir.'/*') as $filename)
			$zf->addFile($filename, "selection_{$this->id_selection}/".basename($filename));
	    $zf->close();
	    return $zip;
	}

	public function extract_shp_atlas($path) {
	    self::cls($path);
	    $this->extract_shp($path, 2154);
	    if (!file_exists($path))
		throw new Exception('Pas de fichier');
	    exec('/usr/local/bin/atlas-1km '.$path);
	}

	public function extract_shp_atlas_mix($path) {
		throw new Exception('ne pas utiliser');
		$this->extract_shp_atlas($path);
		exec('/usr/local/bin/atlas-mix '.$path);
	}

	const sql_mix = 'select bob_selection_mix_annee($1,$2,$3) as n';
	const sql_l_mix = 'select srid,pas from selection_mix_annees where id_selection=$1 group by srid,pas';

	/**
	 * @brief Agrégation des données dans des carrés
	 * @param $pas int pas de la grille
	 * @param $srid int numéro de la projection
	 * @return int le nombre de carrés créés
	 * @see bobs_selection::liste_mix()
	 *
	 */
	public function mix($pas, $srid) {
		$q = bobs_qm()->query($this->db, 'sql_mix_a', self::sql_mix, array($this->id_selection, $pas, $srid));
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief Liste les agrégations créés pour cette sélection
	 */
	public function liste_mix() {
		$q = bobs_qm()->query($this->db, 'sql_l_mix_a', self::sql_l_mix, array($this->id_selection));
		return self::fetch_all($q);
	}

	const csv_opt_toponymes = 'toponyme';
	const csv_opt_xy = 'xy';
	const csv_opt_enquete = 'enq';

	/**
	 * Extrait la sélection dans un CSV
	 * @param $handler Descripteur déjà ouvert
	 * @param $opts un tableau d'options
	 *  - toponymes => 1 inclure toponyme si l'espace est un point
	 * @return int le nombre de ligne exportées
	 */
	public function extract_csv($handler, $opts=false) {
		if (!is_resource($handler))
			throw new Exception('$handler est pas une ressource');

		fputcsv($handler, bobs_citation::get_ligne_csv_titre($opts), ';', '"');

		$n = 0;
		foreach ($this->get_citations() as $c) {
			$n++;
			$ligne = $c->get_ligne_csv($opts);
			fwrite($handler, $ligne);
		}
		return $n;
	}

	/**
	 * Nécessite les paquets PEAR :
	 *   - Spreadsheet_Excel_Writer
	 *   - OLE
	 *
	 * Le fichier est directement envoyé au navigateur
	 */
	public function extract_xls() {
			// TODO
	    require_once('Spreadsheet/Excel/Writer.php');
	    $n = 0;
	    $i = 0;
	    $workbook = new Spreadsheet_Excel_Writer();
	    $workbook->send("selection-{$this->id_selection}.xls");
	    $sheet =& $workbook->addWorksheet($this->nom_selection);

	    foreach (bobs_citation::get_ligne_array_titre() as $v) {
				$sheet->write(0, $i, $v);
				$i++;
	    }

	    $sql = 'select citations.*
				from selection_data,citations
				where citations.id_citation=selection_data.id_citation
				and selection_data.id_selection=$1';
	    $q = bobs_qm()->query($this->db, 'sel_ext_cit_xls', $sql, array($this->id_selection));
	    while ($cit = self::fetch($q)) {
			$c = get_citation($this->db, $cit);
			$d = $c->get_ligne_array();
			$n += 1;
			for ($i=0; $i<count($d); $i++) {
			    $sheet->write($n, $i, iconv('utf8', 'latin1', $d[$i]));
			}
	    }
	    $workbook->close();
	}

	/**
	 * @brief rend la sélection visible dans le QG
	 */
	public function set_qg() {
	    return $this->update_field('partage_qg', 'true');
	}

	public function change_proprietaire($id_utilisateur) {
	    self::cli($id_utilisateur, self::except_si_inf_1);
	    return $this->update_field('id_utilisateur', $id_utilisateur);
	}

	const sql_id_citations_id_espece = 'select sd.id_citation from selection_data sd, citations c
		where sd.id_citation=c.id_citation and sd.id_selection=$1 and c.id_espece=$2';

	public function id_citations_avec_espece($espece) {
		$q = bobs_qm()->query($this->db, 'id_citation_id_espece_sel', self::sql_id_citations_id_espece, array($this->id_selection, $espece->id_espece));
		$r = self::fetch_all($q);
		return array_column($r, 'id_citation');
	}

	const sql_id_citations_id_classe = 'select sd.id_citation
		from selection_data sd, citations c, especes e
		where sd.id_citation=c.id_citation and sd.id_selection=$1
		and c.id_espece=e.id_espece and e.classe=$2';

	public function id_citations_avec_classe($classe) {
		$q = bobs_qm()->query($this->db, 'id_citation_id_classe', self::sql_id_citations_id_classe, array($this->id_selection, $classe));
		$r = self::fetch_all($q);
		return array_column($r, 'id_citation');
	}

	const sql_cherche_tag = 'select sd.id_citation from selection_data sd, citations_tags ct
		where ct.id_citation=sd.id_citation and sd.id_selection=$1 and ct.id_tag=$2';

	const sql_n_tag = 'select count(sd.id_citation) as n from selection_data sd, citations_tags ct
		where ct.id_citation=sd.id_citation and sd.id_selection=$1 and ct.id_tag=$2';


	/**
	 * @brief retourne un tableau des id_citations portant un tag particulier
	 * @param $id_tag integer l'identifiant du tag recherché
	 * @return array un tableau d'id_tag
	 */
	public function id_citations_avec_tag($id_tag) {
		$t = array();

		self::cli($id_tag, self::except_si_inf_1);
		self::cli($this->id_selection, self::except_si_inf_1);

		$q = bobs_qm()->query($this->db, 'sel_w_id_tag', self::sql_cherche_tag, array($this->id_selection, $id_tag));
		$r = self::fetch_all($q);
		return array_column($r, 'id_citation');
	}

	public function citations_avec_tag($id_tag) {
		return new clicnat_iterateur_citations($this->db, $this->id_citations_avec_tag($id_tag));
	}

	/**
	 * @brief retourne le nombre de citations avec un tag particulier
	 * @param $id_tag integer l'identifiant du tag recherché
	 * @return array un tableau d'id_tag
	 */
	public function n_citations_avec_tag($id_tag) {
		self::cli($id_tag, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'sel_n_id_tag', self::sql_n_tag, array($this->id_selection, $id_tag));
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief test si la sélection contient des données invalidées
	 * @return boolean
	 */
	public function a_des_citations_invalides() {
		$tag = bobs_tags::by_ref($this->db, TAG_INVALIDE);
		return $this->n_citations_avec_tag($tag->id_tag);
	}

	/**
	 * @brief test si la sélection contient des citations de nouveaux observateurs
	 * @return boolean
	 */
	public function a_des_citations_de_nouveaux_observateurs() {
		$tag = bobs_tags::by_ref($this->db, TAG_NOUVEL_OBSERVATEUR);
		return $this->n_citations_avec_tag($tag->id_tag);
	}


	const sql_get_ids = 'select distinct id_observation
			from citations,selection_data
			where id_selection=$1
			and citations.id_citation=selection_data.id_citation';

	/**
	 * @brief liste les observations
	 * @return clicnat_iterateur_observations
	 */
	public function get_observations() {
		$ids = array();
		$q = bobs_qm()->query($this->db, 'cso_ids', self::sql_get_ids, array($this->id_selection));
		$t = bobs_element::fetch_all($q);
		$ids = array_column($t, 'id_observation');
		return new clicnat_iterateur_observations($this->db, $ids);
	}

	const sql_get_tags = 'select tags.id_tag,tags.lib,count(citations_tags.id_citation) as usage,
				a_chaine,a_entier
				from tags,citations_tags,selection_data
				where selection_data.id_selection=$1
				and citations_tags.id_citation=selection_data.id_citation
				and citations_tags.id_tag=tags.id_tag
				group by tags.id_tag,tags.lib,tags.a_chaine,tags.a_entier
				order by count(citations_tags.id_citation) desc';

	/**
	 * @brief liste les tags utilisé par les citations sélectionnées
	 * @return get_tags
	 */
	public function get_tags() {
		$q = bobs_qm()->query($this->db, 'sel_get_tags', self::sql_get_tags, array($this->id_selection));
		return self::fetch_all($q);
	}

	const sql_get_ids_citations = 'select id_citation from selection_data where id_selection=$1';

	public function get_citations() {
		$q = bobs_qm()->query($this->db, 'sel_get_ids_citations', self::sql_get_ids_citations, array($this->id_selection));
		$r = self::fetch_all($q);
		$ids = array_column($r, 'id_citation');
		return new clicnat_iterateur_citations($this->db, $ids);
	}

	public function citations() {
		return $this->get_citations();
	}

	const sql_get_ids_communes = "select ei.id_espace_ref as id_espace
			from espace_intersect ei,observations o,citations c,selection_data sd
			where ei.table_espace_ref='espace_commune'
			and o.id_espace=ei.id_espace_obs
			and o.id_observation=c.id_observation
			and sd.id_citation=c.id_citation
			and sd.id_selection=$1";

	public function get_communes() {
		$q = bobs_qm()->query($this->db, 'sel_get_ids_espacecom', self::sql_get_ids_commune, array($this->id_selection));
		$r = self::fetch_all($q);
		$ids = array_column($r, 'id_espace');
		return new clicnat_iterateur_espaces($this->db, $ids);
	}


	const colonnes_liste_especes_csv = 'id_espece,classe,ordre,famille,cd_nom_mnhn,nom_scientifique,nom_vernaculaire,rarete,menace,n_citations';

	public function liste_especes_csv($fh) {
		$n = 0;
		fputcsv($fh, explode(',',self::colonnes_liste_especes_csv));
		foreach ($this->get_especes() as $tab_e) {
			$e = get_espece($this->db, $tab_e['id_espece']);
			$n++;
			$ref = $e->get_referentiel_regional();
			fputcsv($fh, array(
				$e->id_espece,
				$e->classe,
				$e->ordre,
				$e->famille,
				$e->taxref_inpn_especes,
				$e->nom_s,
				$e->nom_f,
				isset($ref['indice_rar'])?$ref['indice_rar']:'',
				isset($ref['categorie'])?$ref['categorie']:'',
				$tab_e['n_citations']
			));
		}
		return $n;
	}

	const colonnes_liste_personnes_csv = 'id_utilisateur,nom,prenom,diffusion_restreinte,n_citations';

	public function liste_observateurs_csv($fh) {
		return $this->liste_utl_csv($fh, "observateurs");
	}

	public function liste_auteurs_csv($fh) {
		return $this->liste_utl_csv($fh, "auteurs");
	}

	private function liste_utl_csv($fh, $liste) {
		$n = 0;
		$cols = explode(',', self::colonnes_liste_personnes_csv);
		fputcsv($fh, $cols);
		switch ($liste) {
			case 'observateurs':
				$personnes = $this->get_observateurs();
				break;
			case 'auteurs':
				$personnes = $this->get_auteurs();
				break;
			default:
				throw new InvalidArgumentException("\$liste invalide : $liste");
		}
		foreach ($personnes as $o) {
			$row = [];
			foreach ($cols as $c)
				$row[$c] = $o[$c];
			fputcsv($fh, $row);
			$n++;
		}
		return $n;
	}

	public function creer_tache_fichier_csv($utilisateur, $date_prevue=null, $opts=null) {
		if (is_null($date_prevue)) $date_prevue = strftime("%Y-%m-%d %H:%M:%S");
		if (is_null($opts)) $opts = [];

		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Création CSV pour selection {$this->id_selection}",
			"clicnat_selection_tr_csv",
			[
				"id_selection" => $this->id_selection,
				"id_utilisateur" => $utilisateur->id_utilisateur,
				"opts" => $opts
			]
		);
	}

	public function creer_tache_fichier_csv_full($utilisateur, $date_prevue=null) {
		if (is_null($date_prevue)) $date_prevue = strftime("%Y-%m-%d %H:%M:%S");

		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Création CSV séparés pour selection {$this->id_selection}",
			"clicnat_selection_tr_full",
			array(
				"id_selection" => $this->id_selection,
				"id_utilisateur" => $utilisateur->id_utilisateur
			)
		);
	}

	public function creer_tache_fichier_shp($utilisateur, $date_prevue=null, $epsg=2154, $type=BOBS_EXTRACTION_SHP_NORMAL) {
		if (is_null($date_prevue)) $date_prevue = strftime("%Y-%m-%d %H:%M:%S");
		if (empty($epsg))
			throw new InvalidArgumentException("\$epsg vide");
		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Création SHP pour selection {$this->id_selection}",
			"clicnat_selection_tr_shp",
			array(
				"id_selection" => $this->id_selection,
				"id_utilisateur" => $utilisateur->id_utilisateur,
				"epsg" => $epsg,
				"type" => $type
			)
		);
	}

	public function creer_tache_extraction_nicheur($utilisateur, $date_prevue=null) {
		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Extraction des nicheurs pour la sélection {$this->id_selection}",
			"clicnat_selection_tr_nicheur",
			[
				"id_selection" => $this->id_selection
			]
		);
	}

	public function creer_tache_validation($utilisateur, $date_prevue=null) {
		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Application des filtres de validation pour la sélection {$this->id_selection}",
			"clicnat_selection_tr_validation",
			[
				"id_selection" => $this->id_selection
			]
		);
	}

	public function creer_tache_mix_a($utilisateur, $date_prevue=null, $srid=2154, $pas=1000) {
		return clicnat_tache::ajouter(
			$this->db,
			$date_prevue,
			$utilisateur->id_utilisateur,
			"Agrégation par mailles et par année de la sélection {$this->id_selection}",
			"clicnat_selection_tr_mix_a",
			[
				"id_selection" => $this->id_selection,
				"projection" => (int)$srid,
				"pas" => (int)$pas
			]
		);
	}

	const sql_liste_enquetes = "select distinct
			substring(xpath('/enquete_resultat/@id_enquete', enquete_resultat)::text from '{(\d+)}') as id_enquete,
			substring(xpath('/enquete_resultat/@version', enquete_resultat)::text from '{(\d+)}') as enquete_version
			from citations,selection_data
			where citations.id_citation=selection_data.id_citation and id_selection=$1";

	public function enquetes_versions() {
		$t = [];
		$q = bobs_qm()->query($this->db, 'sel_l_enq', self::sql_liste_enquetes, [$this->id_selection]);
		while ($r = self::fetch($q)) {
			if (!empty($r['id_enquete']))
				$t[] = new clicnat_enquete_version($this->db, $r['id_enquete'], $r['enquete_version']);
		}
		return $t;
	}

	public function ajouter_ids_citation_extraction_agent($id_extraction) {
		$mc = new \MongoClient(MONGO_DB_STR);
		$mdb = $mc->clicnat_instructeur;
		$extr = $mdb->selections->findOne(["_id" => new MongoId($id_extraction)]);
		$this->ajouter_ids($extr['ids']);
	}

}
