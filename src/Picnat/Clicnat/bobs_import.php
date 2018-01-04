<?php
namespace Picnat\Clicnat;

/**
 * @brief Import de données depuis un CSV
 */
class bobs_import extends bobs_element {
	public $id_import;
	public $id_utilisateur;
	public $id_auteur;
	public $libelle;
	public $date_import;

	protected $prep_import_ligne_insert;
	protected $prep_import_ligne_get;

	private $liste_des_colonnes_inc;

	function __construct($db, $id) {
		parent::__construct($db, 'imports', 'id_import', $id);
		$this->prep_import_ligne_insert = false;
		$this->prep_import_ligne_get = false;
		if (!array_key_exists(IMPORT_SESSION_N, $_SESSION)) {
			$_SESSION[IMPORT_SESSION_N] = [];
		}
		if (!array_key_exists('cols', $_SESSION[IMPORT_SESSION_N])) {
			$_SESSION[IMPORT_SESSION_N]['cols'] = [];
		}
		$this->restaure_colonnes();
	}

	/**
	 * @brief liste les imports en cours
	 * @param ressource $db
	 * @return array
	 */
	public static function get_list($db) 	{
		$sql = "select i.id_import,i.libelle,
			u.nom,u.prenom,u.id_utilisateur,
			count(l.*) as n
		    from imports i,imports_lignes l,utilisateur u
		    where i.id_import=l.id_import
		    and i.id_utilisateur=u.id_utilisateur
		    group by i.id_import,i.libelle,u.nom,u.prenom,u.id_utilisateur
		    order by i.id_import desc";
		return self::fetch_all(bobs_qm()->query($db, 'list_import', $sql, array()));
	}

	/**
	 * @brief création d'un nouvel import
	 * @param $db une connection vers la base de données
	 * @param $args un tableau associatifs des valeurs de création
	 * @return le numéro d'import
	 */
	public static function nouveau($db, $args) {
		$args['id_utilisateur'] = sprintf('%d', $args['id_utilisateur']);
		$args['id_auteur'] = sprintf('%d', $args['id_auteur']);
		$args['libelle'] = trim($args['libelle']);

		if (empty($args['id_utilisateur']) || empty($args['id_auteur']) || empty($args['libelle']))
		throw new \InvalidArgumentException('au moins un des arguments manquant');

		$args['id_import'] = self::nextval($db, 'imports_id_import_seq');

		$keys = ['id_utilisateur', 'id_auteur', 'id_import', 'libelle'];
		$vals = [];
		foreach ($keys as $k) {
			$vals[$k] = $args[$k];
		}

		self::insert($db, 'imports', $vals);

		return $vals['id_import'];
	}

	/**
	 * @brief retourne le dernier numéro de ligne de l'import
	 * @return 0 si aucun sinon le dernier numéro
	 */
	public function dernier_numero_de_ligne_db() {
		$r = $this->query_assoc($this->db, sprintf('
			select coalesce(max(num_ligne), 0) as n
			from imports_lignes where id_import=%d',
		$this->id_import));
		return $r['n'];
	}

	/**
	 * @brief ajoute une ligne a l'import
	 * @param $cols un tableau (simple) des colonnes
	 */
	public function ajoute_ligne($cols) {
		if (!is_array($cols))
		throw new \InvalidArgumentException('$cols doit être un tableau');

		$sql = "
			insert into imports_lignes (
				id_import,
				num_ligne,
				colonne_a, colonne_b, colonne_c, colonne_d, colonne_e,
				colonne_f, colonne_g, colonne_h, colonne_i, colonne_j,
				colonne_k, colonne_l, colonne_m, colonne_n, colonne_o,
				colonne_p, colonne_q, colonne_r, colonne_s, colonne_t,
				colonne_u, colonne_v, colonne_w, colonne_x, colonne_y,
				colonne_z, colonne_aa, colonne_ab, colonne_ac, colonne_ad,
				colonne_ae, colonne_af, colonne_ag, colonne_ah, colonne_ai,
				colonne_aj, colonne_ak
			) values (
				 $1,  $2,
				 $3,  $4,  $5,  $6,  $7,
				 $8,  $9, $10, $11, $12,
				$13, $14, $15, $16, $17,
				$18, $19, $20, $21, $22,
				$23, $24, $25, $26, $27,
				$28, $29, $30, $31, $32,
				$33, $34, $35, $36, $37,
				$38, $39
			)
			";
		$values = [];
		$values[] = $this->id_import;
		$dernier_numero = $this->dernier_numero_de_ligne_db() + 1;
		$values[] = $dernier_numero;

		for ($i=0; $i<IMPORT_MAX_COL; $i++) {
			$values[] = self::cls($cols[$i]);
		}

		if (!$this->prep_import_ligne_insert) {
			$this->prep_import_ligne_insert = pg_prepare($this->db, 'import_ligne_insert', $sql);
		}
		try {
			pg_execute($this->db, 'import_ligne_insert', $values);
		} catch (\Exception $e) {
			error_log("can't import values : ".join("/", $values));
			throw $e;
		}

		return $dernier_numero;
	}

	/**
	 * @brief extrait une ligne de la base de données
	 */
	public function ligne($numero) {
		$sql = 	'select * from imports_lignes '.
			'where id_import = $1 '.
			'and num_ligne = $2';
		$q = bobs_qm()->query($this->db, 'import_ligne_get', $sql, array($this->id_import, (int)$numero));
		return pg_fetch_assoc($q);
	}

	public function charge_fichier($fichier, $length=0, $delimiteur=',', $enclosure='"') {
		$n = 0;
		$f = fopen($fichier, 'r');
		while ($r = fgetcsv($f, $length, $delimiteur, $enclosure)) {
			$this->ajoute_ligne($r);
			$n += 1;
		}
		fclose($f);
		return $n;
	}

	const sql_s_lignes_exp = 'select * from imports_lignes where id_import=$1 order by num_ligne';

	/**
	 * @brief export les lignes pas encore importées en CSV
	 *
	 * la sortie est faite sur stdout
	 */
	public function export_fichier() {
		$f = fopen('php://output','w');
		if (!$f)
			throw new Exception('ne peut ouvrir la sortie');
		$q = bobs_qm()->query($this->db, 'imp_s_lignes_exp', self::sql_s_lignes_exp, array($this->id_import));
		while ($r = pg_fetch_row($q)) {
			fputcsv($f, $r, ';', '"');
		}
		fclose($f);
	}

	const sql_liste_colonne = "select column_name
			from information_schema.columns
			where table_name='imports_lignes'
			and column_name like 'colonne%'
			order by length(column_name),column_name";

	public static function liste_colonnes($db, $complet=true) {
		$t = array();
		$r = self::query_fetch_all($db, self::sql_liste_colonne);

		if ($complet) {
			foreach($r as $l)
				$t[] = $l['column_name'];
		} else {
			if (isset($this->liste_des_colonnes_inc))
			return $this->liste_des_colonnes_inc;
			foreach ($r as $l)
			$t[] = strtoupper(str_replace('colonne_', '', $l['column_name']));
			$this->liste_des_colonnes_inc = $t;
		}
		return $t;
	}

	public static function titre_type_colonne($type) {
		switch ($type) {
			case IMPORT_COL_OBS_DATE:
				return 'date d\'observation';
			case IMPORT_COL_OBS_OBSERV:
				return 'observateur';
			case IMPORT_COL_OBS_LIEU:
				return 'lieu d\'observation';
			case IMPORT_COL_CIT_EFFECTIF:
				return 'effectif';
			//case IMPORT_COL_CIT_EFFECTIF2:
			//	return 'effectif + genre';
			case IMPORT_COL_CIT_ESPECE:
				return 'espèce';
			case IMPORT_COL_CIT_ORDRE:
				return 'ordre';
			case IMPORT_COL_IGNORER:
				return 'ignorer cette colonne';
			case IMPORT_COL_TEMPERATURE:
				return 'température';
			case IMPORT_COL_CODE_FNAT:
				return 'code comportement (fnat)';
			case IMPORT_COL_GENRE:
				return 'genre';
			case IMPORT_COL_AGE:
				return 'age';
			case IMPORT_COL_COMMENTAIRE:
				return 'commentaire';
			case IMPORT_COL_DUREE:
				return 'durée';
			case IMPORT_COL_HEURE:
				return 'heure';
			case IMPORT_COL_LATITUDE_DMS:
				return 'latitude DMS';
			case IMPORT_COL_LONGITUDE_DMS:
				return 'longitude DMS';
			case IMPORT_COL_LATITUDE_D:
				return 'latitude degrés';
			case IMPORT_COL_LONGITUDE_D:
				return 'longitude degrés';
			case IMPORT_COL_CD_NOM:
				return 'identifiant taxref CD_NOM';
			case IMPORT_COL_INDICE_FIA:
				return 'indice fiabilité identification 1 a 4';
			case IMPORT_COL_PERIODE_DATE_A:
				return 'date période : date deb';
			case IMPORT_COL_PERIODE_DATE_B:
				return 'date période : date fin';
			case IMPORT_COL_WKT:
				return 'localisation WKT';
			default:
				return '';
		}
	}

	public function reset_colonne_type() {
		$_SESSION[IMPORT_SESSION_N]['cols'] = array();
	}

	public function set_colonne_type($colonne, $type) {
		$_SESSION[IMPORT_SESSION_N]['cols'][$colonne] = $type;
	}

	public function get_colonne_type($colonne) {
		return $_SESSION[IMPORT_SESSION_N]['cols'][$colonne];
	}

	public function sauve_colonnes() {
		$data = json_encode($_SESSION[IMPORT_SESSION_N]['cols']);
		file_put_contents("/tmp/import-{$this->id_import}.json", $data);
	}

	public function restaure_colonnes() {
		$f = "/tmp/import-{$this->id_import}.json";
		if (file_exists($f)) {
			$data = file_get_contents($f);
			$_SESSION[IMPORT_SESSION_N]['cols'] = json_decode($data, true);
		}
	}

	/**
	 * @brief définition pour un import standard
	 */
	public function set_colonnes_imports() {
		$this->set_colonne_type('colonne_a', IMPORT_COL_OBS_DATE);
		$this->set_colonne_type('colonne_e', IMPORT_COL_OBS_OBSERV);
		$this->set_colonne_type('colonne_f', IMPORT_COL_OBS_OBSERV);
		$this->set_colonne_type('colonne_g', IMPORT_COL_IGNORER);
		$this->set_colonne_type('colonne_h', IMPORT_COL_OBS_LIEU);
		$this->set_colonne_type('colonne_i', IMPORT_COL_OBS_LIEU);
		$this->set_colonne_type('colonne_m', IMPORT_COL_IGNORER); // classe
		$this->set_colonne_type('colonne_n', IMPORT_COL_CIT_ESPECE);
		$this->set_colonne_type('colonne_o', IMPORT_COL_GENRE);
		$this->set_colonne_type('colonne_p', IMPORT_COL_AGE);
		$this->set_colonne_type('colonne_q', IMPORT_COL_CIT_EFFECTIF);
	//	$this->set_colonne_type('colonne_s', IMPORT_COL_CIT_EFFECTIF2);
		$this->set_colonne_type('colonne_t', IMPORT_COL_CODE_FNAT);
		$this->set_colonne_type('colonne_u', IMPORT_COL_CODE_FNAT);
		$this->set_colonne_type('colonne_ad', IMPORT_COL_COMMENTAIRE);
	}

	private function get_q_lignes() {
		$sql = 'select * from imports_lignes where id_import=$1 order by num_ligne';
		return bobs_qm()->query($this->db, 'imports-lignes', $sql, array($this->id_import));
	}

	/**
	 * @brief teste que toutes les colonnes non vides ont un 'programme'
	 *
	 * @return array
	 */
	public function test_attribution_colonne() {
		$lignes_non_vide = array();
		$colonnes = $this->liste_colonnes($this->db);
		foreach ($_SESSION[IMPORT_SESSION_N]['cols'] as $k => $v) {
			self::cli($v);
			if ($v > 0) {
				$k2 = array_search($k, $colonnes);
				unset($colonnes[$k2]);
			}
		}
		$colonnes = array_values($colonnes);
		$q = $this->get_q_lignes();

		$t = self::fetch_all($q);
		foreach ($t as $l) {
			foreach ($colonnes as $col) {
				self::cls($l[$col]);
				if (!empty($l[$col])) {
					$lignes_non_vide[] = $l['num_ligne'];
				}
				continue;
			}
		}
		return $lignes_non_vide;
	}

	public function extract_date($ligne) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_OBS_DATE) {
				if (preg_match('/(\d+).(\d+).(\d+).*/', $ligne[$c], $resultat)) {
					return self::__date_from_preg_match_array($resultat);
				}
			}
		}
		return false;
	}

	private static function __date_from_preg_match_array($resultat) {
		if (intval($resultat[1]) > 1000) {
			return sprintf("%04d-%02d-%02d", $resultat[1], $resultat[2], $resultat[3]);
		}
		if (intval($resultat[3]) > 50 && intval($resultat[3]) < 100)
			$resultat[3] += 1900;
		elseif (intval($resultat[3]) < 100)
			$resultat[3] += 2000;
		return sprintf("%04d-%02d-%02d", $resultat[3], $resultat[2], $resultat[1]);
	}

	public function extract_periode($ligne) {
		$t = [false,false];
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_PERIODE_DATE_A) {
				if (preg_match('/(\d+).(\d+).(\d+).*/', $ligne[$c], $resultat)) {
					$t[0] = self::__date_from_preg_match_array($resultat);
				}
			}
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_PERIODE_DATE_B) {
				if (preg_match('/(\d+).(\d+).(\d+).*/', $ligne[$c], $resultat)) {
					$t[1] = self::__date_from_preg_match_array($resultat);
				}
			}
		}
		if ($t[0]==false || $t[1]==false) {
			return false;
		}
		return $t;
	}

	public function extract_location_md5($ligne) {
		$cols = [
			IMPORT_COL_LATITUDE_DMS,
			IMPORT_COL_LONGITUDE_DMS,
			IMPORT_COL_LATITUDE_D,
			IMPORT_COL_LONGITUDE_D,
			IMPORT_COL_OBS_LIEU,
			IMPORT_COL_WKT
		];
		$str = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if (array_key_exists($c, $_SESSION[IMPORT_SESSION_N]['cols'])) {
				if (array_search($_SESSION[IMPORT_SESSION_N]['cols'][$c], $cols) !== false) {
				//if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_OBS_LIEU) {
					self::cls($ligne[$c]);
					if (!empty($ligne[$c])) {
						$str .= strtolower($ligne[$c]);
					}
				}
			}
		}
		return md5($str);
	}

	public function extract_location($ligne) {
		$str = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if (isset($_SESSION[IMPORT_SESSION_N]['cols'][$c]))
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_OBS_LIEU) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c]))
				$str .= $ligne[$c].' ';
			}
		}
		return self::cls($str);
	}

	public function extract_observateurs_str($ligne) {
		$str = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if (array_key_exists($c, $_SESSION[IMPORT_SESSION_N]['cols'])) {
				if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_OBS_OBSERV) {
					self::cls($ligne[$c]);
					if (!empty($ligne[$c])) {
						$str .= strtolower($ligne[$c]);
					}
				}
			}
		}
		return $str;
	}

	public function extract_observateurs_md5($ligne) {
		return md5($this->extract_observateurs_str($ligne));
	}

	public function extract_observateurs($ligne) {
		$r = array();
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_OBS_OBSERV) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c])) {
					echo "<font color=green>Recherche : {$ligne[$c]}</font>\n";
					$t = bobs_utilisateur::rechercher_import($this->db, $ligne[$c]);
					if (count($t) > 0)
					foreach ($t as $o)
					$r[] = $o;
				}
			}
		}
		return $r;
	}

	public function extract_effectifs($ligne) {
		$nb = 0;
		$genre = '';
		$age = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			switch ($_SESSION[IMPORT_SESSION_N]['cols'][$c]) {
				case IMPORT_COL_CIT_EFFECTIF:
					$nb = self::cli($ligne[$c]);
					break;
				case IMPORT_COL_AGE:
					$age = self::cls($ligne[$c]);
					break;
				case IMPORT_COL_GENRE:
					$genre = self::cls($ligne[$c]);
					break;

			}
		}
		return [
			[
				'effectif' => $nb,
				'genre'    => $genre,
				'age'      => $age
			]
		];
	}

	/**
	 * @brief Extrait la chaine pour le nom de l'espece
	 * @param string $ligne
	 * @return string
	 */
	public function extract_espece_str($ligne) {
		$r = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_CIT_ESPECE) {
				$r .= ' '.$ligne[$c];
			}
		}
		self::cls($r);
		return $r;
	}

	public function extract_especes_taxref($ligne) {
		$cd_nom = false;
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_CD_NOM) {
				$cd_nom = $ligne[$c];
				break;
			}
		}
		if ($cd_nom) {
			$taxref = new bobs_espece_inpn($this->db, $cd_nom);
			$esps = $taxref->get_especes();
			return $esps;
		} else {
			echo "pas de cd_nom";
			return array();
		}
	}

	public function extract_indice_fia($ligne) {
		$indice_fia = null;
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_INDICE_FIA) {
				$indice_fia = intval($ligne[$c]);
				break;
			}
		}
		return $indice_fia;
	}

	/**
	 * @brief Mise à jour du texte décrivant l'espèce
	 * @param int $ligne
	 * @param string $str
	 */
	public function update_espece_str($ligne, $str) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_CIT_ESPECE) {
				$sql = sprintf('update imports_lignes set %s=$1 where id_import=$2 and num_ligne=$3', $c);
				return bobs_qm()->query($this->db, 'update_imp_esp'.$c, $sql, array($str, $this->id_import, $ligne));
			}
		}
	}

	public function extract_tags($ligne) {
		$t = [];
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_CODE_FNAT) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c])) {
					$t[] = bobs_tags::by_ref($this->db, $ligne[$c]);
				}
			}
		}
		return $t;
	}

	public function extract_commentaire($ligne) {
		$commentaire = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_COMMENTAIRE) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c])) {
					$commentaire .= $ligne[$c]."\n";
				}
			}
		}
		return trim($commentaire);
	}

	public function extract_heure($ligne) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_HEURE) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c])) {
					$h = strtolower($ligne[$c]);
					$h = str_replace(' ', '', $h);
					if (preg_match('/([0-9].*)h([0-9].*)/', $h, $matches)) {
						return sprintf('%02d:%02d:00', $matches[1], $matches[2]);
					} else if (preg_match('/([0-9].*):([0-9].*):([0-9].*)/', $h, $matches)) {
						return sprintf('%02d:%02d:%02d', $matches[1], $matches[2], $matches[3]);
					} else if (preg_match('/([0-9].*)h$/', $h, $matches)) {
						return sprintf('%02d:00:00', $matches[1]);
					}
					return $ligne[$c];
				}
			}
		}
		return '';
	}

	public function extract_duree($ligne) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_DUREE) {
				self::cls($ligne[$c]);
				if (!empty($ligne[$c])) {
					return $ligne[$c];
				}
			}
		}
		return '';
	}

	public function extract_wkt($ligne) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_WKT) {
				return $ligne[$c];
			}
		}
		return false;
	}

	public function extract_lat_lon($ligne) {
		foreach ($this->liste_colonnes($this->db) as $c) {
			if (array_key_exists($c, $_SESSION[IMPORT_SESSION_N]['cols'])) {
				if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_LATITUDE_D) {
					$latitude_d = $ligne[$c];
				} elseif ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_LONGITUDE_D) {
					$longitude_d = $ligne[$c];
				}
			}
		}
		if (empty($latitude_d) or empty($longitude_d)) {
			return false;
		}
		$latitude_d = str_replace(",",".",$latitude_d);
		$longitude_d = str_replace(",",".",$longitude_d);
		echo "$longitude_d $latitude_d";
		return array('latitude' => $latitude_d, 'longitude' => $longitude_d);
	}

	public function extract_lat_lon_dms($ligne) {
		$latitude_dms = $longitude_dms = '';
		foreach ($this->liste_colonnes($this->db) as $c) {
			if (array_key_exists($c, $_SESSION[IMPORT_SESSION_N]['cols'])) {
				if ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_LATITUDE_DMS) {
					$latitude_dms = $ligne[$c];
				} elseif ($_SESSION[IMPORT_SESSION_N]['cols'][$c] == IMPORT_COL_LONGITUDE_DMS) {
					$longitude_dms = $ligne[$c];
				}
			}
			if (!empty($longitude_dms) && !empty($latitude_dms)) {
				break;
			}
		}

		if (empty($latitude_dms) || empty($longitude_dms)) {
			return false;
		}
		$latitude_dms = str_replace(",",".",$latitude_dms);
		$longitude_dms = str_replace(",",".",$longitude_dms);
		return [
			'latitude_dms' => $latitude_dms,
			'longitude_dms' => $longitude_dms
		];
	}

	const sql_del_observations = 'delete from imports_observations where id_import=$1';
	const sql_del_observations_observateurs = 'delete from imports_observations_observateurs where id_import=$1';
	const sql_del_citations = 'delete from imports_citations where id_import=$1';
	const sql_del_citations_tags = 'delete from imports_citations_tags where id_import=$1';

	/**
	 * @brief supprime tout ce qui est en cours d'import
	 */
	private function nettoyage() {
		bobs_qm()->query($this->db, 'imp_del_obsobs', self::sql_del_observations_observateurs, array($this->id_import));
		bobs_qm()->query($this->db, 'imp_del_cittags', self::sql_del_citations_tags, array($this->id_import));
		bobs_qm()->query($this->db, 'imp_del_cit', self::sql_del_citations, array($this->id_import));
		bobs_qm()->query($this->db, 'imp_del_obs', self::sql_del_observations, array($this->id_import));
	}

	const sql_del_lignes = 'delete from imports_lignes where id_import=$1';
	const sql_del_import = 'delete from imports where id_import=$1';

	/**
	 * @brief purge
	 */
	public function purge() {
		$this->nettoyage();
		bobs_qm()->query($this->db, 'imp_del_lignes', self::sql_del_lignes, array($this->id_import));
		bobs_qm()->query($this->db, 'imp_del_imp', self::sql_del_import, array($this->id_import));
	}

	/**
	 * @brief Première étape qui va créer les observations
	 */
	public function get_imp_observations() {
		echo "<pre>";
		$t_observateurs = array();
		$gras = false;
		$q = $this->get_q_lignes();
		$obs_date = null;
		$obs_heure = null;
		$obs_lieu_md5 = null;
		$observ_md5 = null;

		$prec_obs_date = null;
		$prec_obs_heure = null;
		$prec_obs_lieu_md5 = null;
		$prec_observ_md5 = null;

		$this->nettoyage();

		$sql_ins_obs = 'insert into imports_observations (date_observation, id_utilisateur, id_import, num_ligne) values ($1, $2, $3, $4)';
		$sql_ins_obs2 = 'insert into imports_observations (id_utilisateur, id_import, num_ligne, date_deb, date_fin) values ($1, $2, $3, $4, $5)';

		$sql_sel_obs = 'select * from imports_observations where id_import=$1 and num_ligne=$2';

		$sql_ins_observ = 'insert into imports_observations_observateurs (id_import,id_observation,id_utilisateur) values ($1, $2, $3)';

		$this->query($this->db, "begin");
		echo "begin\n";
		while ($ligne = self::fetch($q)) {
			$obs_dates = false;
			$obs_date = $this->extract_date($ligne);
			if (!$obs_date) {
				$obs_dates = $this->extract_periode($ligne);
				if (!$obs_dates) {
					echo "pas de date\n";
					continue;
				}
				$compare_date = "{$obs_dates[0]}{$obs_dates[1]}";
			} else {
				$compare_date = $obs_date;
			}

			$obs_lieu_md5 = $this->extract_location_md5($ligne);
			$observ_md5 = $this->extract_observateurs_md5($ligne);

			// la liste des observateurs est stockée ici au cas où on la retrouverai
			if (!isset($t_observateurs[$observ_md5]))
			    $t_observateurs[$observ_md5] = $this->extract_observateurs($ligne);

			if ($prec_obs_date != $compare_date || $obs_lieu_md5 != $prec_obs_lieu_md5 || $observ_md5 != $prec_observ_md5) {
				$gras = !$gras;

				if ($obs_date) {
					bobs_qm()->query($this->db, 'imp_obs_insert', $sql_ins_obs, [$obs_date, $this->id_utilisateur, $this->id_import, $ligne['num_ligne']]);
				} else {
					bobs_qm()->query($this->db, 'imp_obs_insert2', $sql_ins_obs2, [$this->id_utilisateur, $this->id_import, $ligne['num_ligne'], $obs_dates[0], $obs_dates[1]]);
				}

				/*if (empty($prec_obs_date)) { // premiere ligne
					bobs_qm()->query($this->db, 'imp_obs_insert', $sql_ins_obs,
					array(
						$obs_date,
						$this->id_utilisateur,
						$this->id_import,
						$ligne['num_ligne']
					));
				} else {
					bobs_qm()->query($this->db, 'imp_obs_insert', $sql_ins_obs,
					array(
						$obs_date,
						$this->id_utilisateur,
						$this->id_import,
						$ligne['num_ligne']
					));
				}*/

				$q_obs = bobs_qm()->query($this->db, 'imp_get_obs', $sql_sel_obs, array($this->id_import, $ligne['num_ligne']));
				$r_obs = self::fetch($q_obs);
				$id_observation = $r_obs['id_observation'];
				unset($r_obs);
				unset($q_obs);
				foreach ($t_observateurs[$observ_md5] as $observateurs) {
					bobs_qm()->query($this->db, 'imp_ins_observ.', $sql_ins_observ,
					array($this->id_import, $id_observation,
					$observateurs->id_utilisateur));
				}
				$prec_ligne = $ligne['num_ligne'];
				$prec_obs_date = $compare_date;
				$prec_obs_heure = $obs_heure;
				$prec_obs_lieu_md5 = $obs_lieu_md5;
				$prec_observ_md5 = $observ_md5;
			}

			if ($gras) echo "<b>";
			echo "LIGNE=<i>{$ligne['num_ligne']}</i> DATE=<i>".($obs_date?$obs_date:"{$obs_dates[0]}:{$obs_dates[1]}")."</i>";
			echo "LIEU=<i>$obs_lieu_md5</i> OBSERV=<i>$observ_md5</i>\n";
			if ($gras) echo "</b>";
		}
		echo "</pre>";
		$this->query($this->db, "commit");
	}

	/**
	 * @brief nombre d'observations enregistrées
	 * @return int
	 */
	public function get_imp_observation_n() {
		$sql = 'select count(*) as n from imports_observations where id_import=$1';
		$q = bobs_qm()->query($this->db, 'imp_obs_n', $sql, array($this->id_import));
		$r = self::fetch($q);
		return $r['n'];
	}

	/**
	 * @brief retourne les observations en cours d'import
	 * @return bobs_import_observations[]
	 */
	public function get_observations() {
		return bobs_import_observations::get_obs_import($this->db, $this->id_import);
	}

	public function get_observation($id_observation) {
		$sql = 'select * from imports_observations where id_import=$1 and id_observation=$2';
		$q = bobs_qm()->query($this->db, 'imp_obs_get_obs_1', $sql, array($this->id_import, $id_observation));
		$r = self::fetch($q);
		return new bobs_import_observations($this->db, $r);
	}
}
