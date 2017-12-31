<?php
namespace Picnat\Clicnat;

/**
 * @brief Observation en cours d'import
 */
class bobs_import_observations extends bobs_element {
	public $id_import;
	public $num_ligne;
	public $id_observation;
	public $date_observation;
	public $precision_date;
	public $id_espace;
	public $date_modif;
	public $espace_table;
	public $brouillard;
	public $heure_observation;
	public $duree_observation;
	public $n_ligne;

	public $date_deb;
	public $date_fin;

	private $ligne_original;
	private $import;

	function __construct($db, $id) {
		parent::__construct($db, 'imports_observations', 'id_observation', $id);
		if (empty($this->n_ligne)) {
			$this->n_ligne = $this->set_n_ligne();
		}
	}

	/**
	 * @brief retourne les observations en cours d'import pour un import
	 * @param ressource $db
	 * @param int $id_import
	 * @return bobs_import_observations[]
	 */
	public static function get_obs_import($db, $id_import) {
		$t = array();
		$sql = 'select * from imports_observations where id_import=$1 order by num_ligne';
		$q = bobs_qm()->query($db, 'imp_obs_get_obsall', $sql, array($id_import));
		while ($r = self::fetch($q)) {
			$t[] = new bobs_import_observations($db, $r);
		}
		return $t;
	}

	/**
	 * @brief retourne le numéro de ligne de la dernière citation de l'observation
	 * @return int
	 */
	public function derniere_ligne() {
	    return $this->num_ligne + $this->n_ligne - 1;
	}

	/**
	 * @brief retourne le numéro de ligne de la dernière citation de l'observation si rien a été validé
	 * @return int
	 */
	private function __derniere_ligne() {
	    $sql = 'select min(num_ligne) as n from imports_observations where id_import=$1 and num_ligne>$2';
	    $q = bobs_qm()->query($this->db, 'imp_max_l', $sql, array($this->id_import, $this->num_ligne));
	    $r = self::fetch($q);
	    if (!empty($r['n'])) {
		return $r['n']-1;
	    }
	    // on doit se trouver sur la dernière observation donc voir la dernière ligne de l'import
	    return $this->get_import()->dernier_numero_de_ligne_db();
	}

	public function set_n_ligne() {
	    $n = $this->__derniere_ligne() - $this->num_ligne + 1;

	    self::cli($n);
	    if (empty($n))
		throw new Exception('$n est vide');

	    $sql = 'update imports_observations set n_ligne = $2 where id_observation=$1';
	    if (!bobs_qm()->query($this->db, 'imp_set_n_ligne', $sql, array($this->id_observation, $n))) {
		throw new Exception('échec mise à jour');
	    }

	    $this->n_ligne = $n;
	    return $this->n_ligne;
	}

	/**
	 * @brief retourne le nombre de lignes (citations)
	 * @return int
	 */
	public function nombre_lignes() {
	    return $this->n_ligne;
	}

	/**
	 * @brief retourne l'objet import associé
	 * @return bobs_import
	 */
	public function get_import() {
		if (!isset($this->import))
			$this->import = new bobs_import($this->db, $this->id_import);
		return $this->import;
	}

	public function set_espace_wkt($wkt) {
		if (preg_match('/^(\w+)\s?\(/', $wkt, $m)) {
			switch (strtoupper($m[1])) {
				case 'POLYGON':
				case 'MULTIPOLYGON':
					$table = 'espace_polygon';
					break;
				case 'POINT':
					$table = 'espace_point';
					break;
				case 'LINESTRING':
					$table = 'espace_line';
					break;
				default:
					throw new Exception("type de géométrie WKT non géré :{$m[1]}");
			}
		} else {
			throw new Exception('Est-ce du WKT ? '.$wkt);
		}
		$data = [
			'id_utilisateur' => $this->get_import()->id_utilisateur,
			'reference' => sprintf("import %d ligne %d", $import->id_import, $import->num_ligne),
			'nom' => '-',
			'wkt' => $wkt
		];

		$id_espace = bobs_espace::insert_wkt($this->db, $data, $table);
		return $this->set_espace($table, $id_espace);
	}

	public function set_espace_dms($latitude, $longitude) {
		if (!preg_match('/N?([0-9]+)°?([0-9]+)\'?([0-9\.]+)"?/', $latitude, $t_latitude)) {
			throw new Exception('échec conversion dms latitude : '.$latitude);
		}

		if (!preg_match('/E?([0-9]+)°?([0-9]+)\'?([0-9\.]+)"?/', $longitude, $t_longitude)) {
			throw new Exception('échec conversion dms longitude : '.$longitude);
		}

		$n_latitude = $t_latitude[1]  + ($t_latitude[2])/60 + $t_latitude[3]/3600;
		$n_longitude = $t_longitude[1] + ($t_longitude[2])/60 + $t_longitude[3]/3600;

		unset($t_latitude);
		unset($t_longitude);

		return $this->set_espace_d($n_latitude, $n_longitude);
	}

	public function set_espace_d($latitude, $longitude) {
		$import = $this->get_import();
		$id_point = bobs_espace_point::insert($this->db, [
			'id_utilisateur' => $import->id_utilisateur,
			'reference' => sprintf("import %d ligne %d", $import->id_import, $import->num_ligne),
			'nom' => '',
			'x' => $longitude,
			'y' => $latitude
		]);
		$this->set_espace('espace_point', $id_point, false);
	}

	const sql_set_espace = 'update imports_observations set espace_table=$3, id_espace=$4 where id_import=$1 and id_observation=$2';

	public function set_espace($table, $id, $propager=true) {
		self::cli($id);
		self::cls($table);

		if (empty($table))
		    throw new Exception('Ne peut etre vide');

		$q = bobs_qm()->query($this->db, 'imp-obs-set-esp', self::sql_set_espace, [$this->id_import, $this->id_observation, $table, $id]);

		if ($q) {
			$this->id_espace = $id;
			$this->espace_table = $table;
			if ($propager) {
				$imp = $this->get_import();
				$ligne = $imp->ligne($this->num_ligne);
				$this_md5 = $imp->extract_location_md5($ligne);
				foreach ($imp->get_observations() as $obs) {
					if ($this->num_ligne == $obs->num_ligne)
						continue;
					$ligne = $imp->ligne($obs->num_ligne);
					$l_md5 = $imp->extract_location_md5($ligne);
					if ($this_md5 == $l_md5) {
						$obs->set_espace($this->espace_table, $this->id_espace, false);
					}
				}
    			}
		}
	}

	public function get_ligne() {
	    if (!isset($this->ligne_original)) {
		$imp = $this->get_import();
		$this->ligne_original = $imp->ligne($this->num_ligne);
	    }
	    return $this->ligne_original;
	}

	/**
	 * @brief compte le nombre de citations associée à une ligne
	 * @return int le nombre de citations associée
	 *
	 * normalement celà doit retourner 0 ou 1.
	 */
	public function citation_ligne_existe($num_ligne) {
		$sql = 'select count(*) as n from imports_citations where id_import=$1 and num_ligne=$2';
		$q = bobs_qm()->query($this->db, 'get_citations_n_for_l', $sql, array($this->id_import, $num_ligne));
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief liste les objets bobs_import_citation associées à une ligne
	 */
	public function citation_import_objs($num_ligne) {
		$sql = 'select * from imports_citations where id_import=$1 and num_ligne=$2';
		$q = bobs_qm()->query($this->db, 'get_citations_for_ll', $sql, array($this->id_import, $num_ligne));
		$t = array();
		while (($r = self::fetch($q))) {
			$t[] = new bobs_import_citations($this->db, $r);
		}
		return $t;
	}

	const sql_insert_imp_cit = 'insert into imports_citations (id_import,num_ligne,id_citation,id_observation,id_espece) values ($1,$2,$3,$4,$5)';

	/**
	 * @brief création d'une citation temporaire
	 * @param $num_ligne le numéro de la ligne de l'import
	 * @param $id_espece le numéro de l'espèce
	 * @return int le numéro de la citation
	 */
	public function citation_ligne_creation($num_ligne, $id_espece) {
	    if (empty($this->id_import))
		throw new Exception('chargé ?');
	    if (empty($id_espece))
		throw new Exception('$id_espece vide');

	    $id_citation = self::nextval($this->db, 'citations_id_citation_seq');
	    $q = bobs_qm()->query($this->db, 'import_cre_citation', self::sql_insert_imp_cit, array($this->id_import, $num_ligne, $id_citation, $this->id_observation, $id_espece));
	    if ($q)
		return $id_citation;
	}

	public function get_espece_str($num_ligne) {
	    $imp = $this->get_import();
	    return $imp->extract_espece_str($imp->ligne($num_ligne));
	}

	/*public function set_espece_str($num_ligne, $str) {
	    $imp = $this->get_import();
	}*/

	public function get_especes_recherche($num_ligne) {
		$nom = $this->get_espece_str($num_ligne);
		$t1 = array();
		$t2 = array();
		$t3 = array();
		$t4 = array();
		if (!empty($nom)) {
			$t1 = bobs_espece::recherche_par_nom($this->db, $nom);
			$t2 = bobs_espece::recherche_par_code($this->db, $nom);
			$t_obj = bobs_espece::index_recherche($this->db, $nom);
			foreach ($t_obj['especes'] as $obj) {
				$t3[] = array('id_espece' => $obj->id_espece,'nom_f' => $obj->nom_f,'nom_s' => $obj->nom_s);
			}
		}
		foreach ($this->import->extract_especes_taxref($this->get_import()->ligne($num_ligne)) as $obj) {
			$t4[] = array('id_espece' => $obj->id_espece,'nom_f' => $obj->nom_f,'nom_s' => $obj->nom_s);
		}
		return array_merge($t1,$t2,$t3,$t4);
	}

	public function get_date_import() {
		$imp = $this->get_import();
		$d_imp = $imp->extract_date($this->get_ligne());
		if ($d_imp != $this->date_observation)
			$this->set_date($d_imp);
		return $d_imp;
	}

	public function get_heure_import() {
		$heure_imp = $this->get_import()->extract_heure($this->get_ligne());
		if (!empty($heure_imp)) {
			if ($heure_imp != $this->heure_observation) {
				$this->set_heure($heure_imp);
			}
		}
		return $this->heure_observation;
	}

	public function get_duree_import() {
		$duree_imp = $this->get_import()->extract_duree($this->get_ligne());
		if (!empty($duree_imp)) {
			if ($duree_imp != $this->duree_observation) {
				$this->set_duree($duree_imp);
			}
		}
		return $this->duree_observation;
	}

	const sql_upd_date = 'update imports_observations set date_observation=$2 where id_observation=$1';
	const sql_upd_periode = 'update imports_observations set date_deb=$2, date_fin=$3 where id_observation=$1';

	public function set_date($date_str) {
		$q = bobs_qm()->query($this->db, 'import_maj_date', self::sql_upd_date, array($this->id_observation, $date_str));
		if ($q)
			$this->date_observation = $date_str;
	}

	public function set_periode($date_deb, $date_fin) {
		$q = bobs_qm()->query($this->db, 'import_maj_per', self::sql_upd_periode, [$this->id_observation, $date_deb, $date_fin]);
		if ($q) {
			$this->date_deb = $date_deb;
			$this->date_fin = $date_fin;
		}
	}

	const sql_upd_heure = 'update imports_observations set heure_observation=$2 where id_observation=$1';

	public function set_heure($heure_str) {
		$q = bobs_qm()->query($this->db, 'import_maj_heure', self::sql_upd_heure, array($this->id_observation, $heure_str));
		if ($q)
			$this->heure_observation = $heure_str;
	}

	const sql_upd_duree = 'update imports_observations set duree_observation=$2 where id_observation=$1';

	public function set_duree($duree_str) {
		$q = bobs_qm()->query($this->db, 'import_maj_duree', self::sql_upd_duree, array($this->id_observation, $duree_str));
		if ($q)
			$this->duree_observation = $duree_str;
	}

	public function get_observateurs_import() {
		$imp = $this->get_import();
		return $imp->extract_observateurs_str($imp->ligne($this->num_ligne));
	}

	const sql_select_uimp = 'select utilisateur.* from utilisateur,imports_observations_observateurs
		where id_observation=$1 and id_import=$2
		and utilisateur.id_utilisateur=imports_observations_observateurs.id_utilisateur';
	public function get_observateurs() {
		$q = bobs_qm()->query($this->db, 'imp_g_observ', self::sql_select_uimp, array($this->id_observation, $this->id_import));
		return bobs_element::fetch_all($q);
	}

	public function get_effectifs($num_ligne) {
		$imp = $this->get_import();
		return $imp->extract_effectifs($imp->ligne($num_ligne));
	}

	public function get_indice_fia($num_ligne) {
		return $this->get_import()->extract_indice_fia($this->get_import()->ligne($num_ligne));
	}

	public function get_tags($num_ligne) {
		return $this->get_import()->extract_tags($this->get_import()->ligne($num_ligne));
	}

	public function get_commentaire($num_ligne) {
		return $this->get_import()->extract_commentaire($this->get_import()->ligne($num_ligne));
	}


	public function pret_a_valider() {
		// id_espace ok ?
		if (empty($this->id_espace))
			return false;

		// espace_table ok ?
		if (empty($this->espace_table))
			return false;

		// reste des citations a créer ?
		for ($num_ligne=$this->num_ligne; $num_ligne<=$this->derniere_ligne(); $num_ligne++) {
			if (!$this->citation_ligne_existe($num_ligne))
				return false;
		}

		// observateurs > 1
		$observateurs = $this->get_observateurs();
		if (!is_array($observateurs))
			return false;

		if (count($observateurs) < 0)
			return false;

		return true;
	}
	const sql_imp_valider_1 = 'insert into observations (id_observation, id_utilisateur, date_observation, id_espace, espace_table, brouillard, heure_observation, duree_observation, date_deb, date_fin)
		select id_observation, id_utilisateur, date_observation, id_espace, espace_table, false, heure_observation, duree_observation,date_deb,date_fin from imports_observations where id_observation=$1';
	const sql_imp_valider_2 = 'insert into observations_observateurs (id_observation, id_utilisateur)
		select id_observation, id_utilisateur from imports_observations_observateurs where id_observation=$1';
	const sql_imp_valider_3 = 'insert into citations (id_citation, id_observation, id_espece, indice_qualite, sexe, age, nb, commentaire, ref_import)
		select id_citation, id_observation, id_espece, indice_qualite, sexe, age, nb, commentaire, $2 from imports_citations where id_observation=$1';
	const sql_imp_valider_4 = 'insert into citations_tags (id_citation, id_tag, v_text, v_int)
		select ict.id_citation, ict.id_tag, ict.v_text, ict.v_int
		from imports_citations_tags ict, imports_citations ic
		where ic.id_observation=$1
		and ic.id_citation=ict.id_citation';
	const sql_imp_valider_5 = 'delete from imports_lignes as l using imports_citations as c
		where c.id_import=l.id_import
		and l.id_import=$1
		and c.id_observation=$2
		and c.num_ligne=l.num_ligne';
	const sql_imp_valider_6 = 'delete from imports_citations_tags ict using imports_citations ic
		where ic.id_observation=$1
		and ic.id_citation=ict.id_citation';
	const sql_imp_valider_7 = 'delete from imports_citations where id_observation=$1';
	const sql_imp_valider_8 = 'delete from imports_observations_observateurs where id_observation=$1';
	const sql_imp_valider_9 = 'delete from imports_observations where id_observation=$1';

	public function valider() {
	    self::query($this->db, 'begin');
	    try {
		// Copie observations
		bobs_qm()->query($this->db, 'imp-valid-1', self::sql_imp_valider_1, array($this->id_observation));

		// Copie observateurs
		bobs_qm()->query($this->db, 'imp-valid-2', self::sql_imp_valider_2, array($this->id_observation));

		// Copie citations
		bobs_qm()->query($this->db, 'imp-valid-3', self::sql_imp_valider_3, array($this->id_observation, $this->id_import));

		// Copie tags
		bobs_qm()->query($this->db, 'imp-valid-4', self::sql_imp_valider_4, array($this->id_observation));

		// Suppr Lignes
		bobs_qm()->query($this->db, 'imp-valid-5', self::sql_imp_valider_5, array($this->id_import, $this->id_observation));

		// Suppr Tags
		bobs_qm()->query($this->db, 'imp-valid-6', self::sql_imp_valider_6, array($this->id_observation));

		// Suppr Citations
		bobs_qm()->query($this->db, 'imp-valid-7', self::sql_imp_valider_7, array($this->id_observation));

		// Suppr Observateurs
		bobs_qm()->query($this->db, 'imp-valid-8', self::sql_imp_valider_8, array($this->id_observation));

		// Suppr Observation
		bobs_qm()->query($this->db, 'imp-valid-9', self::sql_imp_valider_9, array($this->id_observation));
	    } catch (Exception $e) {
		bobs_log("import valider id_obs={$this->id_observation} ".__LINE__);
		self::query($this->db, 'rollback');
		throw $e;
	    }
	    self::query($this->db, 'commit');
	}

	const sql_supp_observ = 'delete from imports_observations_observateurs where id_import=$1 and id_observation=$2';
	private function supprime_liste_observateurs() {
		bobs_qm()->query($this->db, 'imports_drop_observ', self::sql_supp_observ, array($this->id_import, $this->id_observation));
	}

	private function creation_liste_observateurs() {
		$l_obs = $this->get_import()->extract_observateurs($this->get_import()->ligne($this->num_ligne));
		foreach ($l_obs as $o) {
			$this->ajoute_observateur($o->id_utilisateur);
		}
	}

	const sql_ajout_observ = 'insert into imports_observations_observateurs (id_import, id_observation, id_utilisateur) values ($1, $2, $3)';
	public function ajoute_observateur($id_utilisateur) {
		self::cli($id_utilisateur, self::except_si_inf_1);
		return bobs_qm()->query($this->db, 'imports-ins-obs', self::sql_ajout_observ, array($this->id_import, $this->id_observation, $id_utilisateur));
	}

	const sql_suppr_observateur = 'delete from imports_observations_observateurs where id_import=$1 and id_observation=$2 and id_utilisateur=$3';
	public function supprime_observateur($id_utilisateur) {
		self::cli($id_utilisateur, self::except_si_inf_1);
		return bobs_qm()->query($this->db, 'sql_suppr_observ', self::sql_suppr_observateur, array($this->id_import, $this->id_observation, $id_utilisateur));
	}

	public function refait_liste_observateurs() {
		$this->supprime_liste_observateurs();
		$this->creation_liste_observateurs();
	}
}
